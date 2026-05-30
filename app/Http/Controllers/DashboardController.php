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
            'nextcloud'   => config('services.infrastructure.url_nextcloud', 'https://drive.logstack.web.id'),
            'odoo'        => config('services.infrastructure.url_odoo', 'https://erp.logstack.web.id'),
            'sogo'        => config('services.infrastructure.url_sogo', 'https://mbox.logstack.web.id'),
            'freeipa'     => config('services.infrastructure.url_freeipa', 'https://ipa.logstack.web.id'),
            'grafana'     => config('services.infrastructure.url_grafana', 'https://monit.logstack.web.id'),
            'keycloak'    => config('services.infrastructure.url_keycloak', 'https://sso.logstack.web.id'),
            'mail_domain' => config('services.infrastructure.mail_domain', 'logstack.web.id'),
        ];
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

        $baseUrl = rtrim(config('services.nextcloud.base_url'), '/');
        $apiUser = config('services.nextcloud.api_user');
        $apiToken = config('services.nextcloud.api_token');

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
            'nextcloud' => $this->checkAppStatus(config('services.infrastructure.ip_nextcloud', '172.18.4.105'), config('services.infrastructure.port_nextcloud', 80)),
            'odoo'      => $this->checkAppStatus(config('services.infrastructure.ip_odoo', '172.18.4.106'), config('services.infrastructure.port_odoo', 8069)),
            'sogo'      => $this->checkAppStatus(config('services.infrastructure.ip_sogo', '172.18.4.107'), config('services.infrastructure.port_sogo', 80)),
            'freeipa'   => $this->checkAppStatus(config('services.infrastructure.ip_freeipa', '172.18.4.103'), config('services.infrastructure.port_freeipa', 443)),
            'grafana'   => $this->checkAppStatus(config('services.infrastructure.ip_grafana', '172.18.4.108'), config('services.infrastructure.port_grafana', 3000)),
            'nginx'     => $this->checkAppStatus(config('services.infrastructure.ip_nginx', '172.18.4.101'), config('services.infrastructure.port_nginx', 80)),
        ];

        $baseUrl = rtrim(config('services.nextcloud.base_url'), '/');
        $apiUser = config('services.nextcloud.api_user');
        $apiToken = config('services.nextcloud.api_token');

        $appsStatus = [
            'nextcloud' => $this->checkAppStatus(config('services.infrastructure.ip_nextcloud', '172.18.4.105'), config('services.infrastructure.port_nextcloud', 80)),
            'odoo'      => $this->checkAppStatus(config('services.infrastructure.ip_odoo', '172.18.4.106'), config('services.infrastructure.port_odoo', 8069)),
            'sogo'      => $this->checkAppStatus(config('services.infrastructure.ip_sogo', '172.18.4.107'), config('services.infrastructure.port_sogo', 80)),
            'freeipa'   => $this->checkAppStatus(config('services.infrastructure.ip_freeipa', '172.18.4.103'), config('services.infrastructure.port_freeipa', 443)),
            'grafana'   => $this->checkAppStatus(config('services.infrastructure.ip_grafana', '172.18.4.108'), config('services.infrastructure.port_grafana', 3000)),
            'nginx'     => $this->checkAppStatus(config('services.infrastructure.ip_nginx', '172.18.4.101'), config('services.infrastructure.port_nginx', 80)),
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
            $sshCommand = "ssh -i " . config('services.nextcloud.ssh_key', '/var/www/.ssh/id_rsa') . " -o StrictHostKeyChecking=no -p " . config('services.nextcloud.ssh_port', 2227) . " " . config('services.nextcloud.ssh_user', 'root') . "@" . config('services.infrastructure.ip_nextcloud', '172.18.4.105') . " 'df -Th / | tail -n 1' 2>&1";
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
        $baseUrl = rtrim(config('services.nextcloud.base_url'), '/');
        $apiUser = config('services.nextcloud.api_user');
        $apiToken = config('services.nextcloud.api_token');

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
            'nextcloud' => $this->checkAppStatus(config('services.infrastructure.ip_nextcloud', '172.18.4.105'), config('services.infrastructure.port_nextcloud', 80)),
            'odoo'      => $this->checkAppStatus(config('services.infrastructure.ip_odoo', '172.18.4.106'), config('services.infrastructure.port_odoo', 8069)),
            'sogo'      => $this->checkAppStatus(config('services.infrastructure.ip_sogo', '172.18.4.107'), config('services.infrastructure.port_sogo', 80)),
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

    /**
     * Share dokumen ke user lain via Nextcloud OCS Share API
     */
    public function shareDocument(Request $request)
    {
        $request->validate([
            'file'       => 'required|string',
            'share_with' => 'required|string',
        ]);

        $fileName  = $request->input('file');
        $shareWith = $request->input('share_with');

        extract($this->getNcCredentials());

        if (!$appPassword) {
            return response()->json(['success' => false, 'message' => 'Nextcloud app password belum tersedia.'], 403);
        }

        $ncBaseUrl = rtrim(config('services.nextcloud.base_url', 'http://172.18.4.105'), '/');

        try {
            // Cek apakah sudah di-share sebelumnya
            $existingShares = \Illuminate\Support\Facades\Http::withBasicAuth($username, $appPassword)
                ->withHeaders(['OCS-APIRequest' => 'true'])
                ->get("{$ncBaseUrl}/ocs/v2.php/apps/files_sharing/api/v1/shares", [
                    'path'   => "/Documents/{$fileName}",
                    'format' => 'json',
                ]);

            if ($existingShares->successful()) {
                $shares = $existingShares->json()['ocs']['data'] ?? [];
                foreach ($shares as $share) {
                    if (($share['share_with'] ?? '') === $shareWith) {
                        return response()->json(['success' => false, 'message' => "Dokumen sudah di-share ke user {$shareWith}."]);
                    }
                }
            }

            // Buat share baru
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($username, $appPassword)
                ->withHeaders(['OCS-APIRequest' => 'true'])
                ->post("{$ncBaseUrl}/ocs/v2.php/apps/files_sharing/api/v1/shares", [
                    'path'        => "/Documents/{$fileName}",
                    'shareType'   => 0, // user share
                    'shareWith'   => $shareWith,
                    'permissions' => 17, // read + update
                    'format'      => 'json',
                ]);

            $body = $response->json();
            $status = $body['ocs']['meta']['statuscode'] ?? 0;

            if ($response->successful() && in_array($status, [100, 200])) {
                Log::info("Document {$fileName} shared from {$username} to {$shareWith}");
                return response()->json(['success' => true, 'message' => "Dokumen berhasil di-share ke {$shareWith}."]);
            }

            $errorMsg = $body['ocs']['meta']['message'] ?? 'Gagal share dokumen.';
            return response()->json(['success' => false, 'message' => $errorMsg]);

        } catch (\Exception $e) {
            Log::error("shareDocument exception: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }

    /**
     * Ambil daftar dokumen yang di-share ke user ini
     */
    public function getSharedDocuments()
    {
        extract($this->getNcCredentials());

        if (!$appPassword) {
            return response()->json([]);
        }

        $ncBaseUrl = rtrim(config('services.nextcloud.base_url', 'http://172.18.4.105'), '/');

        try {
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($username, $appPassword)
                ->withHeaders(['OCS-APIRequest' => 'true'])
                ->get("{$ncBaseUrl}/ocs/v2.php/apps/files_sharing/api/v1/shares", [
                    'shared_with_me' => 'true',
                    'format'         => 'json',
                ]);

            if (!$response->successful()) return response()->json([]);

            $shares = $response->json()['ocs']['data'] ?? [];
            $files  = [];

            foreach ($shares as $share) {
                $name = basename($share['path'] ?? '');
                $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                // Hanya tampilkan dokumen Office
                if (!in_array($ext, ['docx', 'xlsx', 'pptx'])) continue;

                $files[] = [
                    'name'       => $name,
                    'shared_by'  => $share['displayname_owner'] ?? $share['uid_owner'] ?? '-',
                    'updated_at' => isset($share['stime']) ? date('d M Y, H:i', $share['stime']) : '-',
                    'ext'        => $ext,
                    'share_id'   => $share['id'] ?? null,
                    'file_owner' => $share['uid_owner'] ?? null,
                ];
            }

            return response()->json($files);

        } catch (\Exception $e) {
            Log::error("getSharedDocuments exception: " . $e->getMessage());
            return response()->json([]);
        }
    }

}
