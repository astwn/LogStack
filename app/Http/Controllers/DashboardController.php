<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\FreeIPAService;
use App\Services\NextcloudService;
use App\Services\BrandingService;
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
     * Helper: Service URLs dari .env
     */
    private function getServiceUrls(): array
    {
        return [
            'nextcloud'   => env('SERVICE_URL_NEXTCLOUD', 'https://drive.logstack.web.id'),
            'odoo'        => env('SERVICE_URL_ODOO', 'https://erp.logstack.web.id'),
            'sogo'        => env('SERVICE_URL_SOGO', 'https://mbox.logstack.web.id'),
            'freeipa'     => env('SERVICE_URL_FREEIPA', 'https://ipa.logstack.web.id'),
            'grafana'     => env('SERVICE_URL_GRAFANA', 'https://monit.logstack.web.id'),
            'keycloak'    => env('SERVICE_URL_KEYCLOAK', 'https://sso.logstack.web.id'),
            'mail_domain' => env('SERVICE_MAIL_DOMAIN', 'logstack.web.id'),
        ];
    }

    /**
     * Local memory stats with Linux and macOS fallback.
     */
    private function getLocalMemoryStats(): array
    {
        $default = [
            'total_mb' => 0,
            'used_mb' => 0,
            'usage_percentage' => 0,
        ];

        try {
            $freeMem = shell_exec('free -m 2>/dev/null');

            if (is_string($freeMem) && trim($freeMem) !== '') {
                $freeMemLines = preg_split('/\r\n|\r|\n/', trim($freeMem));
                $memoryLine = collect($freeMemLines)->first(fn ($line) => str_starts_with(trim($line), 'Mem:'));

                if ($memoryLine) {
                    $memDetails = preg_split('/\s+/', trim($memoryLine));
                    $ramTotal = (int) ($memDetails[1] ?? 0);
                    $ramUsed = (int) ($memDetails[2] ?? 0);

                    if ($ramTotal > 0) {
                        return [
                            'total_mb' => $ramTotal,
                            'used_mb' => $ramUsed,
                            'usage_percentage' => round(($ramUsed / $ramTotal) * 100, 2),
                        ];
                    }
                }
            }

            $totalBytes = (int) trim((string) shell_exec('sysctl -n hw.memsize 2>/dev/null'));
            $vmStat = shell_exec('vm_stat 2>/dev/null');

            if ($totalBytes > 0 && is_string($vmStat) && trim($vmStat) !== '') {
                preg_match('/page size of (\d+) bytes/i', $vmStat, $pageSizeMatch);
                $pageSize = (int) ($pageSizeMatch[1] ?? 4096);

                $extractPages = function (string $label) use ($vmStat): int {
                    if (preg_match('/' . preg_quote($label, '/') . ':\s+([\d.]+)/i', $vmStat, $match)) {
                        return (int) str_replace('.', '', $match[1]);
                    }

                    return 0;
                };

                $freePages = $extractPages('Pages free') + $extractPages('Pages inactive') + $extractPages('Pages speculative');
                $freeBytes = $freePages * $pageSize;
                $usedBytes = max(0, $totalBytes - $freeBytes);
                $ramTotal = round($totalBytes / 1024 / 1024);
                $ramUsed = round($usedBytes / 1024 / 1024);

                return [
                    'total_mb' => $ramTotal,
                    'used_mb' => $ramUsed,
                    'usage_percentage' => $ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 2) : 0,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Unable to read local memory stats: ' . $e->getMessage());
        }

        return $default;
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

        $memoryStats = $this->getLocalMemoryStats();
        $ramTotal = $memoryStats['total_mb'];
        $ramUsed = $memoryStats['used_mb'];
        $ramUsagePercentage = $memoryStats['usage_percentage'];

        $cpuLoad = sys_getloadavg();
        $cpuLoadValue = isset($cpuLoad[0]) ? round($cpuLoad[0], 2) : 0.00;

        // 2. LIVE HEALTH CHECK
        $appsStatus = [
            'nextcloud' => $this->checkAppStatus(env('SERVICE_IP_NEXTCLOUD', '172.18.4.105'), env('SERVICE_PORT_NEXTCLOUD', 80)),
            'odoo'      => $this->checkAppStatus(env('SERVICE_IP_ODOO', '172.18.4.106'), env('SERVICE_PORT_ODOO', 8069)),
            'sogo'      => $this->checkAppStatus(env('SERVICE_IP_SOGO', '172.18.4.107'), env('SERVICE_PORT_SOGO', 80)),
            'freeipa'   => $this->checkAppStatus(env('SERVICE_IP_FREEIPA', '172.18.4.103'), env('SERVICE_PORT_FREEIPA', 443)),
            'grafana'   => $this->checkAppStatus(env('SERVICE_IP_GRAFANA', '172.18.4.108'), env('SERVICE_PORT_GRAFANA', 3000)),
            'nginx'     => $this->checkAppStatus(env('SERVICE_IP_NGINX', '172.18.4.101'), env('SERVICE_PORT_NGINX', 80)),
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
            $sshCommand = "ssh -i " . env('NEXTCLOUD_SSH_KEY', '/var/www/.ssh/id_rsa') . " -o StrictHostKeyChecking=no -p " . env('NEXTCLOUD_SSH_PORT', 2227) . " " . env('NEXTCLOUD_SSH_USER', 'root') . "@" . env('SERVICE_IP_NEXTCLOUD', '172.18.4.105') . " 'df -Th / | tail -n 1' 2>&1";
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
                    'username'   => $username,
                    'fullname'   => trim(($userRaw['givenname'][0] ?? '') . ' ' . ($userRaw['sn'][0] ?? '')) ?: ($userRaw['cn'][0] ?? 'No Name'),
                    'first_name' => $userRaw['givenname'][0] ?? '',
                    'last_name'  => $userRaw['sn'][0] ?? '',
                    'email'      => $userRaw['mail'][0] ?? '-',
                    'groups'     => $userRaw['memberof_group'] ?? ['ipausers'],
                    'last_login' => $lastLogin,
                    'status' => $status
                ];
            }
        }

        $serviceUrls = $this->getServiceUrls();

        return view('dashboard', compact(
            'diskTotalGb', 'diskUsedGb', 'diskUsagePercentage', 'ramTotal', 'ramUsed', 'ramUsagePercentage', 'cpuLoadValue',
            'freeIpaUsers', 'appsStatus', 'nextcloudQuota', 'recentActivities', 'ncStorage', 'ncUserStorageList', 'serviceUrls'
        ) + ['branding' => BrandingService::get()]);
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
            'nextcloud' => $this->checkAppStatus(env('SERVICE_IP_NEXTCLOUD', '172.18.4.105'), env('SERVICE_PORT_NEXTCLOUD', 80)),
            'odoo'      => $this->checkAppStatus(env('SERVICE_IP_ODOO', '172.18.4.106'), env('SERVICE_PORT_ODOO', 8069)),
            'sogo'      => $this->checkAppStatus(env('SERVICE_IP_SOGO', '172.18.4.107'), env('SERVICE_PORT_SOGO', 80)),
        ];

        return view('user_dashboard', [
            'user'             => Auth::user(),
            'appsStatus'       => $appsStatus,
            'nextcloudQuota'   => $nextcloudQuota,
            'recentActivities' => [],
            'branding'         => BrandingService::get(),
            'serviceUrls'      => $this->getServiceUrls(),
        ]);
    }

    /**
     * ==========================================
     * CENTRALIZED STORAGE - pakai NextcloudService
     * ==========================================
     */

    private function getNcCredentials(): array
    {
        $user       = Auth::user();
        $username   = $user->username ?: explode('@', $user->email)[0];
        $appPassword = $user->nc_app_password ?? null;
        return compact('username', 'appPassword');
    }

    public function getFiles(Request $request)
    {
        extract($this->getNcCredentials());

        if (!$appPassword) {
            return response()->json(['error' => 'Nextcloud app password belum tersedia. Silakan logout dan login ulang.'], 403);
        }

        $path = $request->query('path', '/');
        $nextcloud = app(NextcloudService::class);
        $files = $nextcloud->listFiles($username, $appPassword, $path);
        return response()->json($files);
    }

    public function uploadFile(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['success' => false, 'message' => 'File tidak ditemukan.']);
        }

        extract($this->getNcCredentials());

        if (!$appPassword) {
            return response()->json(['success' => false, 'message' => 'Nextcloud app password belum tersedia. Silakan logout dan login ulang.']);
        }

        $path = $request->input('path', '/');
        $nextcloud = app(NextcloudService::class);
        $success = $nextcloud->uploadFile($username, $appPassword, $request->file('file'), $path);
        return response()->json([
            'success' => $success,
            'message' => $success ? 'File berhasil diunggah.' : 'Gagal mengunggah file.'
        ]);
    }

    public function deleteFile(Request $request)
    {
        $fileName = $request->input('file');
        if (!$fileName) {
            return response()->json(['success' => false, 'message' => 'Nama file tidak valid.']);
        }

        extract($this->getNcCredentials());

        if (!$appPassword) {
            return response()->json(['success' => false, 'message' => 'Nextcloud app password belum tersedia. Silakan logout dan login ulang.']);
        }

        $path = $request->input('path', '/');
        $nextcloud = app(NextcloudService::class);
        $success = $nextcloud->deleteFile($username, $appPassword, $fileName, $path);
        return response()->json([
            'success' => $success,
            'message' => $success ? 'File berhasil dihapus.' : 'Gagal menghapus file.'
        ]);
    }

    public function previewFile(Request $request)
    {
        return $this->streamFile($request, 'inline');
    }

    public function downloadFile(Request $request)
    {
        return $this->streamFile($request, 'attachment');
    }

    private function streamFile(Request $request, string $disposition)
    {
        $fileName = $request->query('file');
        $path     = $request->query('path', '/');

        if (!$fileName) {
            abort(400, 'Nama file tidak valid.');
        }

        extract($this->getNcCredentials());

        if (!$appPassword) {
            abort(403, 'Nextcloud app password belum tersedia. Silakan logout dan login ulang.');
        }

        $nextcloud = app(NextcloudService::class);
        $response  = $nextcloud->streamFile($username, $appPassword, $fileName, $path);

        if (!$response || !$response->successful()) {
            abort(404, 'File tidak ditemukan.');
        }

        // Proteksi file besar — max 100MB via stream Laravel
        $fileSize = strlen($response->body());
        if ($fileSize > 100 * 1024 * 1024) {
            abort(413, 'File terlalu besar untuk di-stream (maks 100MB). Gunakan Nextcloud langsung.');
        }

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'mp4'  => 'video/mp4',
            'webm' => 'video/webm',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt'  => 'text/plain',
            'md'   => 'text/plain',
        ];
        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

        return response($response->body(), 200, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => $disposition . '; filename="' . $fileName . '"',
            'Content-Length'      => strlen($response->body()),
        ]);
    }

}
