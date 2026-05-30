<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KeycloakAdminService
{
    protected $baseUrl;
    protected $realm;
    protected $adminUser;
    protected $adminPassword;

    public function __construct()
    {
        $this->baseUrl       = rtrim(config('services.keycloak.base_url'), '/');
        $this->realm         = config('services.keycloak.realms');
        $this->adminUser     = config('services.keycloak.admin_user');
        $this->adminPassword = config('services.keycloak.admin_password');
    }

    private function getAdminToken()
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->asForm()
                ->post("{$this->baseUrl}/realms/master/protocol/openid-connect/token", [
                    'grant_type' => 'password',
                    'client_id'  => 'admin-cli',
                    'username'   => $this->adminUser,
                    'password'   => $this->adminPassword,
                ]);

            if ($response->successful()) {
                return $response->json()['access_token'] ?? null;
            }
            Log::error('Keycloak admin token failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Keycloak admin token error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Trigger LDAP sync dari FreeIPA ke Keycloak untuk user tertentu
     * Menggunakan triggerChangedUsersSync - lebih aman dari delete user
     */
    public function syncUser(string $username)
    {
        return $this->syncAllUsers();
    }

    /**
     * Trigger sync semua user dari FreeIPA federation
     */
    public function syncAllUsers()
    {
        $token = $this->getAdminToken();
        if (!$token) {
            Log::error('Keycloak syncAllUsers: gagal mendapatkan admin token.');
            return ['success' => false, 'message' => 'Gagal mendapatkan admin token Keycloak.'];
        }

        try {
            $fedResponse = Http::withOptions(['verify' => false])
                ->withToken($token)
                ->get("{$this->baseUrl}/admin/realms/{$this->realm}/components", [
                    'type' => 'org.keycloak.storage.UserStorageProvider',
                ]);

            if (!$fedResponse->successful() || empty($fedResponse->json())) {
                Log::error('Keycloak syncAllUsers: federation provider tidak ditemukan.');
                return ['success' => false, 'message' => 'Gagal menemukan User Federation provider.'];
            }

            $providerId = $fedResponse->json()[0]['id'];

            $syncResponse = Http::withOptions(['verify' => false])
                ->withToken($token)
                ->post("{$this->baseUrl}/admin/realms/{$this->realm}/user-storage/{$providerId}/sync?action=triggerChangedUsersSync");

            if ($syncResponse->successful()) {
                $result = $syncResponse->json();
                Log::info('Keycloak LDAP sync triggered: ' . json_encode($result));
                return ['success' => true, 'message' => 'Sync FreeIPA ke Keycloak berhasil.', 'result' => $result];
            }

            Log::error('Keycloak sync failed: ' . $syncResponse->body());
            return ['success' => false, 'message' => 'Gagal trigger sync federation.'];

        } catch (\Exception $e) {
            Log::error('Keycloak syncAllUsers error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat sync.'];
        }
    }
}
