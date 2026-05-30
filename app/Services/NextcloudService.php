<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NextcloudService
{
    protected string $baseUrl;
    protected string $adminUser;
    protected string $adminToken;
    protected string $ncHost;

    public function __construct()
    {
        $this->baseUrl    = rtrim(config('services.nextcloud.base_url', 'http://172.18.4.105'), '/');
        $this->adminUser  = config('services.nextcloud.api_user', 'super-admin');
        $this->adminToken = config('services.nextcloud.api_token', '');
        $this->ncHost     = config('services.nextcloud.host', '172.18.4.105');
    }

    /**
     * Cek apakah user sudah ada di Nextcloud via OCS API
     */
    public function userExists(string $username): bool
    {
        try {
            $response = Http::withBasicAuth($this->adminUser, $this->adminToken)
                ->withHeaders(['OCS-APIRequest' => 'true'])
                ->get("{$this->baseUrl}/ocs/v1.php/cloud/users/{$username}");
            // Nextcloud OCS selalu return HTTP 200, cek statuscode di body XML
            return str_contains($response->body(), '<statuscode>100</statuscode>');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Provision user baru di Nextcloud via OCS API
     * Dipanggil otomatis saat user baru pertama kali akses Nextcloud dari dashboard
     */
    public function provisionUser(string $username, string $email, string $displayName): bool
    {
        try {
            // Cek dulu apakah sudah ada
            if ($this->userExists($username)) {
                Log::info("Nextcloud: user {$username} sudah ada, skip provision.");
                return true;
            }

            // Buat user baru via OCS API
            $response = Http::withBasicAuth($this->adminUser, $this->adminToken)
                ->withHeaders(['OCS-APIRequest' => 'true'])
                ->post("{$this->baseUrl}/ocs/v1.php/cloud/users", [
                    'userid'      => $username,
                    'password'    => bin2hex(random_bytes(16)), // random password, user pakai SSO
                    'email'       => $email,
                    'displayName' => $displayName,
                ]);

            $body = $response->body();

            if (str_contains($body, '<statuscode>100</statuscode>')) {
                Log::info("Nextcloud: user {$username} berhasil di-provision.");
                return true;
            }

            Log::error("Nextcloud: gagal provision user {$username}. Response: {$body}");
            return false;

        } catch (\Exception $e) {
            Log::error("Nextcloud: provisionUser exception untuk {$username}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate app password untuk user via occ command (SSH ke server Nextcloud)
     * Return plain text password atau null jika gagal
     */
    public function generateAppPassword(string $username): ?string
    {
        try {
            $sshKey  = config('services.nextcloud.ssh_key', '/var/www/.ssh/id_rsa');
            $sshPort = config('services.nextcloud.ssh_port', '2227');
            $occPath = '/var/www/nextcloud/occ';

            $cmd = "ssh -i {$sshKey} -o StrictHostKeyChecking=no -p {$sshPort} root@{$this->ncHost} "
                 . "'php {$occPath} user:auth-tokens:add {$username} --name=logstack-portal --no-interaction 2>&1'";

            $output = shell_exec($cmd);

            if (!$output) {
                Log::error("NextcloudService: generateAppPassword gagal untuk {$username} - output kosong");
                return null;
            }

            // Parse app password dari output
            if (preg_match('/app password:\s*\n([^\s]+)/i', $output, $matches)) {
                return trim($matches[1]);
            }

            Log::error("NextcloudService: gagal parse app password untuk {$username}. Output: {$output}");
            return null;

        } catch (\Exception $e) {
            Log::error("NextcloudService: generateAppPassword exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Hapus semua app password logstack-portal milik user
     */
    public function revokeAppPassword(string $username): void
    {
        try {
            $sshKey  = config('services.nextcloud.ssh_key', '/var/www/.ssh/id_rsa');
            $sshPort = config('services.nextcloud.ssh_port', '2227');
            $occPath = '/var/www/nextcloud/occ';

            // List tokens dulu
            $listCmd = "ssh -i {$sshKey} -o StrictHostKeyChecking=no -p {$sshPort} root@{$this->ncHost} "
                     . "'php {$occPath} user:auth-tokens:list {$username} 2>&1'";
            $listOutput = shell_exec($listCmd);

            // Parse token IDs dengan nama logstack-portal
            if (preg_match_all('/\|\s*(\d+)\s*\|\s*logstack-portal\s*\|/', $listOutput, $matches)) {
                foreach ($matches[1] as $tokenId) {
                    $delCmd = "ssh -i {$sshKey} -o StrictHostKeyChecking=no -p {$sshPort} root@{$this->ncHost} "
                            . "'php {$occPath} user:auth-tokens:delete {$username} {$tokenId} 2>&1'";
                    shell_exec($delCmd);
                    Log::info("NextcloudService: revoked token {$tokenId} untuk {$username}");
                }
            }
        } catch (\Exception $e) {
            Log::error("NextcloudService: revokeAppPassword exception: " . $e->getMessage());
        }
    }

    /**
     * List semua file di root folder user
     */
    public function listFiles(string $username, string $appPassword, string $path = '/'): array
    {
        $path      = '/' . trim($path, '/');
        $webdavUrl = "{$this->baseUrl}/remote.php/dav/files/{$username}{$path}";
        // Nama folder saat ini untuk di-skip dari listing
        $currentFolder = basename($path) ?: $username;

        try {
            $response = Http::withBasicAuth($username, $appPassword)
                ->withHeaders(['Depth' => '1'])
                ->send('PROPFIND', $webdavUrl);

            if (!$response->successful()) {
                Log::warning("NextcloudService: listFiles gagal untuk {$username} - status " . $response->status());
                return [];
            }

            $xml = simplexml_load_string($response->body());
            $xml->registerXPathNamespace('d', 'DAV:');
            $responses = $xml->xpath('//d:response');

            $files = [];
            foreach ($responses as $res) {
                $href     = (string)$res->xpath('.//d:href')[0];
                $basename = basename(urldecode($href));

                // Skip root folder (folder itu sendiri)
                if (empty($basename) || $basename === $username || $basename === $currentFolder) continue;

                $propstat    = $res->xpath('.//d:propstat')[0];
                $sizeInBytes = (int)($propstat->xpath('.//d:getcontentlength')[0] ?? 0);
                $lastMod     = (string)($propstat->xpath('.//d:getlastmodified')[0] ?? '');
                $isDir       = !empty($res->xpath('.//d:collection'));

                $files[] = [
                    'name'       => $basename,
                    'is_dir'     => $isDir,
                    'size'       => $isDir ? '--' : ($sizeInBytes >= 1048576
                        ? round($sizeInBytes / 1048576, 1) . ' MB'
                        : round($sizeInBytes / 1024, 1) . ' KB'),
                    'updated_at' => $lastMod ? date('d M Y, H:i', strtotime($lastMod)) : '-',
                ];
            }

            return $files;

        } catch (\Exception $e) {
            Log::error("NextcloudService: listFiles exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Upload file ke folder user (support subfolder via $path)
     */
    public function uploadFile(string $username, string $appPassword, $file, string $path = '/'): bool
    {
        $path      = rtrim($path, '/');
        $webdavUrl = "{$this->baseUrl}/remote.php/dav/files/{$username}{$path}/"
                   . rawurlencode($file->getClientOriginalName());

        try {
            // Pakai stream untuk file besar — tidak load ke memory sekaligus
            $stream = fopen($file->getRealPath(), 'r');

            $response = Http::withBasicAuth($username, $appPassword)
                ->withHeaders([
                    'Content-Type'   => $file->getClientMimeType(),
                    'Content-Length' => $file->getSize(),
                ])
                ->withBody($stream, $file->getClientMimeType())
                ->timeout(600)
                ->put($webdavUrl);

            if (is_resource($stream)) fclose($stream);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error("NextcloudService: uploadFile exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus file dari folder user (support subfolder via $path)
     */
    public function deleteFile(string $username, string $appPassword, string $fileName, string $path = '/'): bool
    {
        $path      = rtrim($path, '/');
        $webdavUrl = "{$this->baseUrl}/remote.php/dav/files/{$username}{$path}/"
                   . rawurlencode($fileName);

        try {
            $response = Http::withBasicAuth($username, $appPassword)
                ->delete($webdavUrl);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error("NextcloudService: deleteFile exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus user dari Nextcloud via OCS API
     */
    public function deleteUser(string $username): array
    {
        try {
            if (!$this->userExists($username)) {
                Log::info("Nextcloud: user {$username} tidak ditemukan, skip delete.");
                return ['success' => true, 'message' => 'User tidak ditemukan di Nextcloud (skip).'];
            }

            $response = Http::withBasicAuth($this->adminUser, $this->adminToken)
                ->withHeaders(['OCS-APIRequest' => 'true'])
                ->delete("{$this->baseUrl}/ocs/v1.php/cloud/users/{$username}");

            $body = $response->body();

            if (str_contains($body, '<statuscode>100</statuscode>')) {
                Log::info("Nextcloud: user {$username} berhasil dihapus.");
                return ['success' => true, 'message' => 'User berhasil dihapus dari Nextcloud.'];
            }

            Log::error("Nextcloud: gagal hapus user {$username}. Response: {$body}");
            return ['success' => false, 'message' => 'Gagal menghapus user dari Nextcloud.'];

        } catch (\Exception $e) {
            Log::error("Nextcloud: deleteUser exception untuk {$username}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }
    public function isAppPasswordValid(string $username, string $appPassword): bool
    {
        try {
            $response = Http::withBasicAuth($username, $appPassword)
                ->withHeaders(['Depth' => '0'])
                ->send('PROPFIND', "{$this->baseUrl}/remote.php/dav/files/{$username}/");

            return $response->successful();

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Stream file dari Nextcloud (untuk preview/download)
     */
    public function streamFile(string $username, string $appPassword, string $fileName, string $path = '/'): ?\Illuminate\Http\Client\Response
    {
        $path      = '/' . trim($path, '/');
        $webdavUrl = "{$this->baseUrl}/remote.php/dav/files/{$username}{$path}/"
                   . rawurlencode($fileName);

        try {
            $response = Http::withBasicAuth($username, $appPassword)->get($webdavUrl);
            return $response->successful() ? $response : null;
        } catch (\Exception $e) {
            Log::error("NextcloudService: streamFile exception: " . $e->getMessage());
            return null;
        }
    }
}
