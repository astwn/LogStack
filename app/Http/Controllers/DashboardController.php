<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\FreeIPAService;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $freeIpa;

    public function __construct(FreeIPAService $freeIpa)
    {
        $this->freeIpa = $freeIpa;
    }

    /**
     * Helper Live Health Check TCP Port
     */
    private function checkAppStatus($ip, $port, $timeout = 1)
    {
        $connection = @fsockopen($ip, $port, $errno, $errstr, $timeout);
        if (is_resource($connection)) {
            fclose($connection);
            return 'ONLINE';
        }
        return 'OFFLINE';
    }

    /**
     * UTILITY API: MODUL GANTI QUOTA USER NEXTCLOUD
     */
    public function updateUserQuota(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'quota' => 'required|string'
        ]);

        $baseUrl = rtrim(env('NEXTCLOUD_BASE_URL'), '/');
        $apiUser = env('NEXTCLOUD_API_USER');
        $apiToken = env('NEXTCLOUD_API_TOKEN');

        try {
            $response = Http::withBasicAuth($apiUser, $apiToken)
                ->withHeaders(['OCS-APIRequest' => 'true'])
                ->put("{$baseUrl}/ocs/v2.php/cloud/users/{$request->username}", [
                    'key' => 'quota',
                    'value' => $request->quota
                ]);

            if ($response->successful()) {
                return redirect()->back()->with('success', "Kuota user {$request->username} berhasil diubah menjadi {$request->quota}!");
            }

            return redirect()->back()->with('error', "Gagal mengubah kuota di server Nextcloud.");

        } catch (\Exception $e) {
            Log::error("Gagal update quota Nextcloud: " . $e->getMessage());
            return redirect()->back()->with('error', "Terjadi kesalahan sistem.");
        }
    }

    public function adminDashboard()
    {
        // 1. Logika Monitoring local resource server App Portal
        $diskTotal = disk_total_space(base_path());
        $diskFree = disk_free_space(base_path());
        $diskUsed = $diskTotal - $diskFree;
        $diskUsagePercentage = round(($diskUsed / $diskTotal) * 100, 2);
        $diskTotalGb = round($diskTotal / (1024 * 1024 * 1024), 2);
        $diskUsedGb = round($diskUsed / (1024 * 1024 * 1024), 2);

        $freeMem = shell_exec('free -m');
        $freeMemLines = explode("\n", trim($freeMem));
        $memDetails = preg_split('/ +/', trim($freeMemLines[1]));
        $ramTotal = $memDetails[1];
        $ramUsed = $memDetails[2];
        $ramUsagePercentage = round(($ramUsed / $ramTotal) * 100, 2);

        $cpuLoad = sys_getloadavg();
        $cpuLoadValue = isset($cpuLoad[0]) ? round($cpuLoad[0], 2) : 0.00;

        // 2. LIVE HEALTH CHECK
        $appsStatus = [
            'nextcloud' => $this->checkAppStatus('172.18.4.105', 80),
            'odoo'      => $this->checkAppStatus('172.18.4.106', 8069),
            'sogo'      => $this->checkAppStatus('172.18.4.107', 80),
            'freeipa'   => $this->checkAppStatus('172.18.4.103', 443),
            'grafana'   => $this->checkAppStatus('172.18.4.108', 3000),
            'nginx'     => $this->checkAppStatus('172.18.4.101', 80),
        ];

        $baseUrl = rtrim(env('NEXTCLOUD_BASE_URL'), '/');
        $apiUser = env('NEXTCLOUD_API_USER');
        $apiToken = env('NEXTCLOUD_API_TOKEN');

        $ncStorage = [
            'total_gb' => '0',
            'used_gb' => '0',
            'free_gb' => '0',
            'percentage' => 0,
            'display_total' => '0 GB'
        ];
        $nextcloudQuota = [
            'free_gb' => 0,
            'used_gb' => 0,
            'total_gb' => 0,
            'relative' => 0,
            'display_total' => 'Unlimited'
        ];
        
        $recentActivities = [];
        $ncUserStorageList = []; 

        // 3. AMBIL DATA STORAGE REALTIME OS VIA SSH PORT 2227
        try {
            $sshCommand = "ssh -i /var/www/.ssh/id_rsa -o StrictHostKeyChecking=no -p 2227 root@172.18.4.105 'df -Th / | tail -n 1' 2>&1";
            $output = shell_exec($sshCommand);

            if (!empty($output) && !str_contains($output, 'Permission denied') && !str_contains($output, 'Could not open')) {
                $cleanedOutput = preg_replace('/ +/', ' ', trim($output));
                $details = explode(' ', $cleanedOutput);

                $percentIndex = -1;
                foreach ($details as $index => $value) {
                    if (str_contains($value, '%')) {
                        $percentIndex = $index;
                        break;
                    }
                }

                if ($percentIndex !== -1 && $percentIndex >= 3) {
                    $percentUsed = (int) str_replace('%', '', $details[$percentIndex]);
                    $freeSpace   = $details[$percentIndex - 1]; 
                    $usedSpace   = $details[$percentIndex - 2]; 
                    $totalSpace  = $details[$percentIndex - 3]; 

                    $totalClean = str_replace(['G', 'M', 'T', 'K', 'g', 'm', 't'], '', $totalSpace);
                    $usedClean  = str_replace(['G', 'M', 'T', 'K', 'g', 'm', 't'], '', $usedSpace);
                    $freeClean  = str_replace(['G', 'M', 'T', 'K', 'g', 'm', 't'], '', $freeSpace);

                    $ncStorage = [
                        'total_gb'      => $totalClean,
                        'used_gb'       => $usedClean,
                        'free_gb'       => $freeClean,
                        'percentage'    => $percentUsed,
                        'display_total' => $totalSpace . 'B'
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Gagal SSH df -Th: " . $e->getMessage());
        }

        // 4. SINKRONISASI KUOTA PERSONAL ADMIN
        try {
            $currentUser = Auth::user();
            $ncUsername = $currentUser->username ?: explode('@', $currentUser->email)[0];

            $response = Http::withBasicAuth($apiUser, $apiToken)
                ->withHeaders(['OCS-APIRequest' => 'true'])
                ->timeout(3)
                ->get("{$baseUrl}/ocs/v2.php/cloud/users/{$ncUsername}", ['format' => 'json']);

            if ($response->successful()) {
                $quota = $response->json()['ocs']['data']['quota'] ?? null;
                if ($quota) {
                    $tBytes = $quota['total'] ?? -3;
                    $uBytes = $quota['used'] ?? 0;
                    $fBytes = $quota['free'] ?? 0;

                    $nextcloudQuota = [
                        'free_gb'       => round($fBytes / 1024 / 1024 / 1024, 2),
                        'used_gb'       => round($uBytes / 1024 / 1024 / 1024, 2),
                        'total_gb'      => $tBytes > 0 ? round($tBytes / 1024 / 1024 / 1024, 2) : 0,
                        'relative'      => round($quota['relative'] ?? 0, 1),
                        'display_total' => $tBytes > 0 ? round($tBytes / 1024 / 1024 / 1024, 2) . ' GB' : 'Unlimited'
                    ];
                }
            }
        } catch (\Exception $ex) {
            Log::error("Gagal sinkron kuota personal admin: " . $ex->getMessage());
        }

        // 5. DATA LIVE USER UNTUK MANAJEMEN BAWAH
        $ipaResponse = $this->freeIpa->listUsers();
        if ($ipaResponse['success'] && isset($ipaResponse['data']['result'])) {
            foreach ($ipaResponse['data']['result'] as $userRaw) {
                $username = $userRaw['uid'][0] ?? 'unknown';
                if ($username === 'unknown' || $username === 'super-admin') { continue; }

                try {
                    $userResponse = Http::withBasicAuth($apiUser, $apiToken)
                        ->withHeaders(['OCS-APIRequest' => 'true'])
                        ->timeout(2)
                        ->get("{$baseUrl}/ocs/v2.php/cloud/users/{$username}", ['format' => 'json']);

                    if ($userResponse->successful()) {
                        $uQuota = $userResponse->json()['ocs']['data']['quota'] ?? null;
                        if ($uQuota) {
                            $uTotal = $uQuota['total'] ?? -3;
                            $ncUserStorageList[] = [
                                'username'   => $username,
                                'fullname'   => $userRaw['cn'][0] ?? $username,
                                'used'       => round(($uQuota['used'] ?? 0) / 1024 / 1024 / 1024, 2) . ' GB',
                                'free'       => $uTotal > 0 ? round(($uQuota['free'] ?? 0) / 1024 / 1024 / 1024, 2) . ' GB' : 'Unlimited',
                                'total'      => $uTotal > 0 ? round($uTotal / 1024 / 1024 / 1024, 2) . ' GB' : 'Unlimited'
                            ];
                        }
                    }
                } catch (\Exception $err) {
                    $ncUserStorageList[] = [
                        'username' => $username,
                        'fullname' => $userRaw['cn'][0] ?? $username,
                        'used' => '0 GB', 'free' => 'Default', 'total' => 'Default'
                    ];
                }
            }
        }

        $freeIpaUsers = [];
        if ($ipaResponse['success'] && isset($ipaResponse['data']['result'])) {
            foreach ($ipaResponse['data']['result'] as $userRaw) {
                $username = $userRaw['uid'][0] ?? 'unknown';
                $localUser = User::where('email', $userRaw['mail'][0] ?? null)->first();
                if (!$localUser && $username !== 'unknown') { $localUser = User::where('name', $username)->first(); }
                $lastLogin = $localUser && $localUser->last_login ? Carbon::parse($localUser->last_login)->setTimezone('Asia/Jakarta')->format('d M Y - H:i') . ' WIB' : 'Never / Offline';
                $status = isset($userRaw['nsaccountlock']) && ($userRaw['nsaccountlock'] === true || $userRaw['nsaccountlock'] === 'TRUE') ? 'Locked' : 'Active';

                $freeIpaUsers[] = [
                    'username' => $username,
                    'fullname' => $userRaw['cn'][0] ?? 'No Name',
                    'email' => $userRaw['mail'][0] ?? '-',
                    'groups' => $userRaw['memberof_group'] ?? ['ipausers'],
                    'last_login' => $lastLogin,
                    'status' => $status
                ];
            }
        }

        return view('dashboard', compact(
            'diskTotalGb', 'diskUsedGb', 'diskUsagePercentage', 'ramTotal', 'ramUsed', 'ramUsagePercentage', 'cpuLoadValue',
            'freeIpaUsers', 'appsStatus', 'nextcloudQuota', 'recentActivities', 'ncStorage', 'ncUserStorageList'
        ));
    }

    /**
     * 🔥 FIX SINKRONISASI UNTUK DASHBOARD USER BIASA (ANTI EROR UNDEFINED KEY)
     */
    public function userDashboard()
    {
        $baseUrl = rtrim(env('NEXTCLOUD_BASE_URL'), '/');
        $apiUser = env('NEXTCLOUD_API_USER');
        $apiToken = env('NEXTCLOUD_API_TOKEN');

        // Sediakan array default penyelamat agar tidak memicu error blade
        $nextcloudQuota = [
            'free_gb' => 0,
            'used_gb' => 0,
            'total_gb' => 0,
            'relative' => 0,
            'display_total' => 'Default'
        ];

        try {
            $currentUser = Auth::user();
            // Ambil identifier username Nextcloud dari data session login user biasa
            $ncUsername = $currentUser->username ?: explode('@', $currentUser->email)[0];

            // Tembak kuota personal milik si user biasa secara real-time
            $response = Http::withBasicAuth($apiUser, $apiToken)
                ->withHeaders(['OCS-APIRequest' => 'true'])
                ->timeout(3)
                ->get("{$baseUrl}/ocs/v2.php/cloud/users/{$ncUsername}", ['format' => 'json']);

            if ($response->successful()) {
                $quota = $response->json()['ocs']['data']['quota'] ?? null;
                if ($quota) {
                    $tBytes = $quota['total'] ?? -3;
                    $uBytes = $quota['used'] ?? 0;
                    $fBytes = $quota['free'] ?? 0;

                    $nextcloudQuota = [
                        'free_gb'       => round($fBytes / 1024 / 1024 / 1024, 2),
                        'used_gb'       => round($uBytes / 1024 / 1024 / 1024, 2),
                        'total_gb'      => $tBytes > 0 ? round($tBytes / 1024 / 1024 / 1024, 2) : 0,
                        'relative'      => round($quota['relative'] ?? 0, 1),
                        'display_total' => $tBytes > 0 ? round($tBytes / 1024 / 1024 / 1024, 2) . ' GB' : 'Unlimited'
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Gagal sinkronisasi kuota user biasa: " . $e->getMessage());
        }

        // Live App Status Ringkas untuk Dashboard User Biasa
        $appsStatus = [
            'nextcloud' => $this->checkAppStatus('172.18.4.105', 80),
            'odoo'      => $this->checkAppStatus('172.18.4.106', 8069),
            'sogo'      => $this->checkAppStatus('172.18.4.107', 80),
        ];

        return view('user_dashboard', [
            'user'             => Auth::user(), 
            'appsStatus'       => $appsStatus, 
            'nextcloudQuota'   => $nextcloudQuota, // 🔥 Sekarang Key used_gb terisi aman sentosa!
            'recentActivities' => []
        ]);
    }
}
