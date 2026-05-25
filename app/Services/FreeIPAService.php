<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FreeIPAService
{
    protected $baseUrl;
    protected $adminUser;
    protected $adminPassword;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.freeipa.url'), '/');
        $this->adminUser = config('services.freeipa.user');
        $this->adminPassword = config('services.freeipa.password');
    }

    private function getSessionCookie()
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'text/plain',
                'Referer' => $this->baseUrl . '/ipa',
            ])
            ->withOptions(['verify' => false])
            ->asForm()
            ->post($this->baseUrl . '/ipa/session/login_password', [
                'user' => $this->adminUser,
                'password' => $this->adminPassword,
            ]);

            if ($response->successful()) {
                $cookies = $response->header('Set-Cookie');
                if (preg_match('/ipa_session=([^;]+)/', $cookies, $matches)) {
                    return 'ipa_session=' . $matches[1];
                }
            }
            return null;
        } catch (\Exception $e) {
            Log::error('FreeIPA Connection Error: ' . $e->getMessage());
            return null;
        }
    }

    public function callRpc(string $method, array $params = [])
    {
        $cookie = $this->getSessionCookie();
        if (!$cookie) {
            return ['success' => false, 'message' => 'Gagal otentikasi ke FreeIPA Server.'];
        }

        $payload = [
            'id' => 0,
            'method' => $method,
            'params' => [
                [], 
                array_merge(['version' => '2.213'], $params)
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Cookie' => $cookie,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Referer' => $this->baseUrl . '/ipa',
            ])
            ->withOptions(['verify' => false])
            ->post($this->baseUrl . '/ipa/session/json', $payload);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['error']) && !is_null($result['error'])) {
                    return ['success' => false, 'message' => $result['error']['message']];
                }
                return ['success' => true, 'data' => $result['result']];
            }
            return ['success' => false, 'message' => 'FreeIPA API HTTP Error ' . $response->status()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Mengambil seluruh daftar user dari FreeIPA Directory
     */
    public function listUsers()
    {
        return $this->callRpc('user_find', [
            'sizelimit' => 100,
            'all' => true // Minta FreeIPA memuntahkan seluruh atribut metadata (termasuk create timestamp)
        ]);
    }

    /**
     * Membuat user baru di FreeIPA
     */
    public function createUser(string $username, string $firstName, string $lastName, string $email, string $password)
    {
        return $this->callRpc('user_add', [
            'uid' => $username,
            'givenname' => $firstName,
            'sn' => $lastName,
            'cn' => $firstName . ' ' . $lastName,
            'mail' => $email,
            'userpassword' => $password,
        ]);
    }

    /**
     * Memasukkan user ke Group spesifik (dash_admin atau ipausers)
     */
    public function addUserToGroup(string $username, string $groupName)
    {
        return $this->callRpc('group_add_member', [
            'cn' => $groupName,
            'user' => $username
        ]);
    }

    public function deleteUser(string $username)
    {
        return $this->callRpc('user_del', [
            'uid' => $username,
        ]);
    }

    public function updateUser(string $username, string $firstName, string $lastName, string $email)
    {
        return $this->callRpc('user_mod', [
            'uid' => $username,
            'givenname' => $firstName,
            'sn' => $lastName,
            'cn' => $firstName . ' ' . $lastName,
            'mail' => $email,
        ]);
    }

   public function removeUserFromGroup(string $username, string $groupName)
    {
        return $this->callRpc('group_remove_member', [
            'cn' => $groupName,
            'user' => $username
        ]);
    }

    public function toggleUserStatus(string $username, bool $lock)
    {
        return $this->callRpc('user_mod', [
            'uid' => $username,
            'nsaccountlock' => $lock ? 'TRUE' : 'FALSE'
        ]);
    }
}
