<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class OnlyOfficeCallbackController extends Controller
{
    /**
     * Pastikan folder Documents ada untuk user — pakai app password dari DB
     */
    private function ensureDocumentsFolder(string $username): void
    {
        $ncBaseUrl  = rtrim(config('services.nextcloud.base_url', 'http://172.18.4.105'), '/');
        $adminUser  = config('services.nextcloud.api_user');
        $adminToken = config('services.nextcloud.api_token');
        $docsDir    = "{$ncBaseUrl}/remote.php/dav/files/{$username}/Documents";

        try {
            $check = Http::withBasicAuth($adminUser, $adminToken)
                ->withHeaders(['Depth' => '0'])
                ->send('PROPFIND', $docsDir);

            if ($check->status() == 404) {
                $localUser = \App\Models\User::where('username', $username)->first();
                if ($localUser && $localUser->nc_app_password) {
                    $result = Http::withBasicAuth($username, $localUser->nc_app_password)
                        ->send('MKCOL', $docsDir);
                    Log::info("OnlyOffice callback: ensureDocumentsFolder untuk {$username} - status: " . $result->status());
                }
            }
        } catch (\Exception $e) {
            Log::error("OnlyOffice callback: ensureDocumentsFolder exception untuk {$username}: " . $e->getMessage());
        }
    }

    public function handle(Request $request)
    {
        $body = $request->json()->all();
        if (isset($body['token'])) {
            $body = (array) JWT::decode($body['token'], new Key(config('services.onlyoffice.secret'), 'HS256'));
        }

        if (isset($body['status']) && ($body['status'] == 2 || $body['status'] == 6)) {
            $targetUser = $request->query('user');
            $fileName   = $request->query('file');
            $ncBaseUrl  = rtrim(config('services.nextcloud.base_url', 'http://172.18.4.105'), '/');

            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $mimeTypes = [
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ];
            $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

            // Pastikan folder Documents ada
            $this->ensureDocumentsFolder($targetUser);

            // Ambil app password user dari DB
            $localUser = \App\Models\User::where('username', $targetUser)->first();
            if (!$localUser || !$localUser->nc_app_password) {
                Log::error("OnlyOffice callback: app password tidak ditemukan untuk user {$targetUser}");
                return response()->json(['error' => 0]);
            }

            $ncUser  = $targetUser;
            $ncToken = $localUser->nc_app_password;

            $destUrl = $ncBaseUrl . "/remote.php/dav/files/" . $targetUser . "/Documents/" . $fileName;

            $fileContent = Http::get($body['url'])->body();

            // Direct PUT ke destination pakai user credentials
            $upload = Http::withBasicAuth($ncUser, $ncToken)
                ->withBody($fileContent, $contentType)
                ->put($destUrl);

            if ($upload->successful()) {
                Log::info("File {$fileName} sukses disimpan di folder Documents user {$targetUser}");
            } else {
                Log::error("File {$fileName} gagal disimpan untuk user {$targetUser} - status: " . $upload->status() . " body: " . substr($upload->body(), 0, 200));
            }
        }
        return response()->json(['error' => 0]);
    }
}
