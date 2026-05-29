<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogService;

class OnlyOfficeViewController extends Controller
{
    /**
     * Helper: ambil credentials Nextcloud user yang sedang login
     */
    private function getNcCredentials(): array
    {
        $user        = Auth::user();
        $username    = $user->username ?? explode('@', $user->email)[0];
        $appPassword = $user->nc_app_password ?? null;
        $baseUrl     = rtrim(env('NEXTCLOUD_BASE_URL', 'http://172.18.4.105'), '/');
        $docsDir     = "{$baseUrl}/remote.php/dav/files/{$username}/Documents";

        return compact('username', 'appPassword', 'baseUrl', 'docsDir');
    }

    /**
     * Auto-create folder Documents jika belum ada
     * Pakai user credentials untuk MKCOL — admin tidak bisa MKCOL di space user lain
     */
    private function ensureDocumentsFolder(string $username, string $appPassword, string $docsDir): void
    {
        try {
            $check = Http::withBasicAuth($username, $appPassword)
                ->withHeaders(['Depth' => '0'])
                ->send('PROPFIND', $docsDir);

            if ($check->status() == 404) {
                $result = Http::withBasicAuth($username, $appPassword)
                    ->send('MKCOL', $docsDir);
                Log::info("OnlyOffice: ensureDocumentsFolder untuk {$username} - MKCOL status: " . $result->status());
            }
        } catch (\Exception $e) {
            Log::error("OnlyOffice: ensureDocumentsFolder exception untuk {$username}: " . $e->getMessage());
        }
    }

    /**
     * Pastikan folder Documents ada — dipanggil dari callback & downloadRaw
     * Pakai admin credentials untuk check, user credentials untuk create
     */
    private function ensureDocumentsFolderAdmin(string $username): void
    {
        $ncBaseUrl  = rtrim(env('NEXTCLOUD_BASE_URL', 'http://172.18.4.105'), '/');
        $adminUser  = env('NEXTCLOUD_API_USER');
        $adminToken = env('NEXTCLOUD_API_TOKEN');
        $docsDir    = "{$ncBaseUrl}/remote.php/dav/files/{$username}/Documents";

        try {
            $check = Http::withBasicAuth($adminUser, $adminToken)
                ->withHeaders(['Depth' => '0'])
                ->send('PROPFIND', $docsDir);

            if ($check->status() == 404) {
                // Coba buat via user app password dari DB
                $localUser = \App\Models\User::where('username', $username)->first();
                if ($localUser && $localUser->nc_app_password) {
                    $result = Http::withBasicAuth($username, $localUser->nc_app_password)
                        ->send('MKCOL', $docsDir);
                    Log::info("OnlyOffice: ensureDocumentsFolderAdmin untuk {$username} - MKCOL status: " . $result->status());
                }
            }
        } catch (\Exception $e) {
            Log::error("OnlyOffice: ensureDocumentsFolderAdmin exception untuk {$username}: " . $e->getMessage());
        }
    }

    public function openDocument(Request $request)
    {
        $fileName        = $request->query('file', 'dokumen.docx');
        $currentUsername = Auth::user()->username ?? 'guest';

        ActivityLogService::log('onlyoffice', 'open_document', "Membuka dokumen: " . $fileName, $currentUsername, Auth::id());

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $documentType = match ($ext) {
            'doc', 'docx', 'odt'      => 'word',
            'xls', 'xlsx', 'ods', 'csv' => 'cell',
            'ppt', 'pptx', 'odp'      => 'slide',
            default                    => 'word',
        };
        $mimeTypes = [
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

        extract($this->getNcCredentials());

        if (!$appPassword) {
            return response()->json(['error' => 'Nextcloud app password belum tersedia. Silakan logout dan login ulang.'], 403);
        }

        $webdavUrl = "{$docsDir}/{$fileName}";

        // Auto-create folder Documents
        $this->ensureDocumentsFolder($username, $appPassword, $docsDir);

        // Auto-create blank file jika belum ada
        $checkFile = Http::withBasicAuth($username, $appPassword)->head($webdavUrl);
        if ($checkFile->status() == 404) {
            $templateMap = [
                'docx' => storage_path('app/templates/blank.docx'),
                'xlsx' => storage_path('app/templates/blank.xlsx'),
                'pptx' => storage_path('app/templates/blank.pptx'),
            ];
            $templateFile = $templateMap[$ext] ?? storage_path('app/templates/blank.docx');
            if (file_exists($templateFile)) {
                ActivityLogService::log('onlyoffice', 'create_document', "Membuat dokumen baru: " . $fileName, $currentUsername, Auth::id());
                Http::withBasicAuth($username, $appPassword)
                    ->withBody(file_get_contents($templateFile), $contentType)
                    ->put($webdavUrl);
            } else {
                return response()->json(['error' => 'Template file not found: ' . $templateFile], 500);
            }
        }

        // Ambil last modified time file untuk generate unique key
        $ncBaseUrl   = rtrim(env('NEXTCLOUD_BASE_URL', 'http://172.18.4.105'), '/');
        $fileWebdav  = "{$ncBaseUrl}/remote.php/dav/files/{$username}/Documents/{$fileName}";
        $lastModified = '';
        try {
            $headResp = Http::withBasicAuth($username, $appPassword)
                ->withHeaders(['Depth' => '0'])
                ->send('PROPFIND', $fileWebdav);
            if ($headResp->successful()) {
                preg_match('/<d:getlastmodified>(.*?)<\/d:getlastmodified>/', $headResp->body(), $matches);
                $lastModified = $matches[1] ?? '';
            }
        } catch (\Exception $e) {}

        $fileId = md5($currentUsername . '_' . $fileName . '_' . $lastModified);
        $config = [
            "document" => [
                "fileType"    => $ext,
                "key"         => $fileId,
                "title"       => $fileName,
                "url"         => config('app.url') . '/document/download-raw?file='
                    . urlencode($fileName) . "&user=" . urlencode($currentUsername),
                "permissions" => ["edit" => true, "download" => true],
            ],
            "documentType" => $documentType,
            "editorConfig" => [
                "mode"        => "edit",
                "callbackUrl" => config('app.url') . '/onlyoffice/callback?file='
                    . urlencode($fileName) . "&user=" . urlencode($currentUsername),
                "user"        => [
                    "id"   => "user_" . Auth::id(),
                    "name" => Auth::user()->name ?? $currentUsername,
                ],
            ],
        ];

        return view('onlyoffice.editor', [
            'onlyofficeUrl' => env('ONLYOFFICE_URL', 'https://office.logstack.web.id'),
            'config'        => $config,
            'token'         => JWT::encode($config, env('ONLYOFFICE_SECRET'), 'HS256'),
        ]);
    }

    public function downloadRawFile(Request $request)
    {
        $fileName   = $request->query('file');
        $targetUser = $request->query('user');

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $mimeTypes = [
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

        $ncBaseUrl = rtrim(env('NEXTCLOUD_BASE_URL', 'http://172.18.4.105'), '/');

        // Pastikan folder Documents ada
        $this->ensureDocumentsFolderAdmin($targetUser);

        // Ambil app password user dari DB
        $localUser = \App\Models\User::where('username', $targetUser)->first();
        if (!$localUser || !$localUser->nc_app_password) {
            Log::error("OnlyOffice downloadRawFile: app password tidak ditemukan untuk user {$targetUser}");
            return response('App password not found', 403);
        }

        $webdavUrl = "{$ncBaseUrl}/remote.php/dav/files/{$targetUser}/Documents/{$fileName}";

        $response = Http::withBasicAuth($targetUser, $localUser->nc_app_password)->get($webdavUrl);

        if (!$response->successful()) {
            Log::error("OnlyOffice downloadRawFile: gagal fetch {$fileName} untuk {$targetUser} - status: " . $response->status());
            return response($response->body(), $response->status(), ['Content-Type' => 'application/xml']);
        }

        return response($response->body(), 200, ['Content-Type' => $contentType]);
    }

    public function getFilesList()
    {
        $currentUsername = Auth::user()->username ?? 'guest';

        extract($this->getNcCredentials());

        if (!$appPassword) {
            return response()->json([]);
        }

        // Auto-create folder Documents
        $this->ensureDocumentsFolder($username, $appPassword, $docsDir);

        $response = Http::withBasicAuth($username, $appPassword)
            ->withHeaders(['Depth' => '1'])
            ->send('PROPFIND', $docsDir);

        $files = [];
        if ($response->successful()) {
            $xml = simplexml_load_string($response->body());
            $xml->registerXPathNamespace('d', 'DAV:');
            $responses = $xml->xpath('//d:response');

            foreach ($responses as $res) {
                $href     = (string) $res->children('DAV:')->href;
                $basename = basename(urldecode($href));

                // Skip folder root Documents itu sendiri
                if (empty($basename) || $basename === 'Documents' || $basename === $currentUsername) {
                    continue;
                }

                $propstat    = $res->children('DAV:')->propstat;
                $prop        = $propstat->prop;
                $sizeInBytes = (int) $prop->getcontentlength;
                $lastMod     = (string) $prop->getlastmodified;

                $size = '0 KB';
                if ($sizeInBytes > 0) {
                    $size = $sizeInBytes >= 1048576
                        ? round($sizeInBytes / 1048576, 1) . ' MB'
                        : round($sizeInBytes / 1024, 1) . ' KB';
                }

                $ext     = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
                $files[] = [
                    'name'       => $basename,
                    'size'       => $size,
                    'updated_at' => $lastMod ? date('d M Y, H:i', strtotime($lastMod)) : '-',
                    'ext'        => $ext,
                ];
            }
        }

        return response()->json($files);
    }

    public function deleteDocument(Request $request)
    {
        $fileName        = $request->input('file');
        $currentUsername = Auth::user()->username ?? 'guest';

        extract($this->getNcCredentials());

        if (!$appPassword) {
            return response()->json(['success' => false, 'message' => 'Nextcloud app password belum tersedia.']);
        }

        $webdavUrl = "{$docsDir}/{$fileName}";
        $response  = Http::withBasicAuth($username, $appPassword)->send('DELETE', $webdavUrl);

        if ($response->successful()) {
            ActivityLogService::log('onlyoffice', 'delete_document', "Menghapus dokumen: " . $fileName, $currentUsername, Auth::id());
            return response()->json(['success' => true, 'message' => 'Dokumen dihapus']);
        }

        return response()->json(['success' => false, 'message' => 'Gagal menghapus dokumen'], 500);
    }
}
