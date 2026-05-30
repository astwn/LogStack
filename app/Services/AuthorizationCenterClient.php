<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthorizationCenterClient
{
    protected string $baseUrl;
    protected string $appCode;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.authorization_center.url'), '/');
        $this->appCode = config('services.authorization_center.app_code', 'logstack-app');
    }

    public function redirectUrl(string $callbackUrl): string
    {
        return $this->baseUrl . '/api/v1/auth/keycloak/redirect?' . http_build_query([
            'callback_url' => $callbackUrl,
        ]);
    }

    public function exchangeCode(string $code): array
    {
        $response = Http::timeout(15)
            ->acceptJson()
            ->asJson()
            ->post($this->baseUrl . '/api/v1/auth/keycloak/exchange', [
                'code' => $code,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Authorization Center exchange failed: ' . $response->body());
        }

        $payload = $response->json();
        if (!($payload['success'] ?? false) || empty($payload['data']['access_token'])) {
            throw new \RuntimeException('Authorization Center exchange response is invalid.');
        }

        return $payload['data'];
    }

    public function refreshToken(string $refreshToken): array
    {
        $response = Http::timeout(15)
            ->acceptJson()
            ->asJson()
            ->post($this->baseUrl . '/api/v1/auth/refresh', [
                'refresh_token' => $refreshToken,
                'set_cookie' => false,
            ]);

        return $this->expectData($response, 'Authorization Center refresh failed.');
    }

    public function currentUser(string $accessToken): array
    {
        $response = $this->authorized($accessToken)
            ->get($this->baseUrl . '/api/v1/auth/me');

        return $this->expectData($response, 'Authorization Center current user failed.');
    }

    public function accessSummary(string $accessToken, ?string $appCode = null): array
    {
        $response = $this->authorized($accessToken)
            ->get($this->accessUrl($appCode));

        return $this->expectData($response, 'Authorization Center access summary failed.');
    }

    public function accessMenus(string $accessToken, ?string $appCode = null): array
    {
        $response = $this->authorized($accessToken)
            ->get($this->accessUrl($appCode) . '/menus');

        return $this->expectData($response, 'Authorization Center access menus failed.');
    }

    public function accessPermissions(string $accessToken, ?string $appCode = null): array
    {
        $response = $this->authorized($accessToken)
            ->get($this->accessUrl($appCode) . '/permissions');

        return $this->expectData($response, 'Authorization Center access permissions failed.');
    }

    public function checkAccess(string $accessToken, string $permission, ?string $appCode = null): array
    {
        $response = $this->authorized($accessToken)
            ->get($this->accessUrl($appCode) . '/check', [
                'permission' => $permission,
            ]);

        return $this->expectData($response, 'Authorization Center access check failed.');
    }

    public function appAccessToken(string $accessToken, ?string $appCode = null): array
    {
        $response = $this->authorized($accessToken)
            ->get($this->accessUrl($appCode) . '/token');

        return $this->expectData($response, 'Authorization Center app access token failed.');
    }

    public function refreshAccessSnapshot(?string $appCode = null, bool $force = false): array
    {
        $this->ensureFreshSessionAccessToken();

        $accessToken = session('access_token');
        if (!$accessToken) {
            throw new \RuntimeException('Access token tidak tersedia di session Laravel.');
        }

        $appCode = $appCode ?: self::appCode();
        $cacheKey = self::accessCacheKey($appCode);

        if ($force) {
            Cache::forget($cacheKey);
        }

        try {
            $snapshot = Cache::remember(
                $cacheKey,
                now()->addSeconds((int) env('AUTHORIZATION_CENTER_ACCESS_CACHE_TTL', 150)),
                fn() => $this->accessSummary($accessToken, $appCode)
            );
        } catch (\Exception $e) {
            Log::warning('Authorization access cache gagal, fallback direct request: ' . $e->getMessage());
            $snapshot = $this->accessSummary($accessToken, $appCode);
        }

        return self::storeAccessSnapshot($snapshot, $appCode);
    }

    public function logout(?string $refreshToken): void
    {
        if (!$refreshToken) {
            return;
        }

        Http::timeout(10)
            ->acceptJson()
            ->asJson()
            ->post($this->baseUrl . '/api/v1/auth/logout', [
                'refresh_token' => $refreshToken,
            ]);
    }

    public static function accessSnapshot(): array
    {
        return session('authz_access', self::emptyAccessSnapshot());
    }

    public static function appCode(): string
    {
        return session('authz_app_code', config('services.authorization_center.app_code', 'logstack-app'));
    }

    public static function menus(): array
    {
        return session('authz_menus', []);
    }

    public static function permissions(): array
    {
        return session('authz_permissions', []);
    }

    public static function can(string $permission): bool
    {
        return in_array($permission, self::permissions(), true);
    }

    public static function any(array $permissions): bool
    {
        $permissions = self::flattenPermissions($permissions);

        foreach ($permissions as $permission) {
            if (self::can($permission)) {
                return true;
            }
        }

        return false;
    }

    public static function all(array $permissions): bool
    {
        $permissions = self::flattenPermissions($permissions);

        foreach ($permissions as $permission) {
            if (!self::can($permission)) {
                return false;
            }
        }

        return true;
    }

    public static function storeAccessSnapshot(array $snapshot, ?string $appCode = null): array
    {
        $appCode = $appCode ?: ($snapshot['app'] ?? config('services.authorization_center.app_code', 'logstack-app'));
        $menus = self::normalizeMenus($snapshot['menus'] ?? ($snapshot['items'] ?? []));
        $permissions = self::normalizePermissions($snapshot['permissions'] ?? []);

        $stored = [
            'app' => $appCode,
            'menus' => $menus,
            'permissions' => $permissions,
            'loaded_at' => now()->toIso8601String(),
        ];

        session([
            'authz_app_code' => $appCode,
            'authz_menus' => $menus,
            'authz_permissions' => $permissions,
            'authz_access' => $stored,
            'authz_access_loaded_at' => $stored['loaded_at'],
        ]);

        return $stored;
    }

    public static function forgetAccessSnapshot(): void
    {
        Cache::forget(self::accessCacheKey(self::appCode()));
        session()->forget([
            'authz_app_code',
            'authz_menus',
            'authz_permissions',
            'authz_access',
            'authz_access_loaded_at',
        ]);
    }

    public static function accessCacheKey(?string $appCode = null): string
    {
        $userKey = Auth::id() ? 'user:' . Auth::id() : 'session:' . session()->getId();
        return 'authz:access:' . $userKey . ':app:' . ($appCode ?: self::appCode());
    }

    protected function ensureFreshSessionAccessToken(): void
    {
        $expiresAt = (int) session('access_token_expires_at', 0);
        if (!$expiresAt || $expiresAt > now()->addMinute()->timestamp) {
            return;
        }

        $refreshToken = session('refresh_token');
        if (!$refreshToken) {
            return;
        }

        try {
            $tokens = $this->refreshToken($refreshToken);
            if (!empty($tokens['access_token'])) {
                session(['access_token' => $tokens['access_token']]);
            }
            if (!empty($tokens['refresh_token'])) {
                session(['refresh_token' => $tokens['refresh_token']]);
            }
            if (!empty($tokens['expires_in'])) {
                session(['access_token_expires_at' => now()->addSeconds((int) $tokens['expires_in'])->timestamp]);
            }
        } catch (\Exception $e) {
            Log::warning('Authorization Center token refresh gagal: ' . $e->getMessage());
        }
    }

    protected function authorized(string $accessToken)
    {
        return Http::timeout(15)
            ->acceptJson()
            ->withToken($accessToken);
    }

    protected function accessUrl(?string $appCode = null): string
    {
        $appCode = $appCode ?: $this->appCode;

        return $this->baseUrl . '/api/v1/auth/me/apps/' . rawurlencode($appCode) . '/access';
    }

    protected function expectData($response, string $message): array
    {
        if (!$response->successful()) {
            throw new \RuntimeException($message . ' ' . $response->body());
        }

        $payload = $response->json();
        if (!($payload['success'] ?? false)) {
            throw new \RuntimeException($message);
        }

        return $payload['data'] ?? [];
    }

    protected static function normalizeMenus(array $menus): array
    {
        return array_values(array_map(function ($menu) {
            if (!is_array($menu)) {
                return ['code' => (string) $menu, 'path' => null, 'required_permission' => null];
            }

            return [
                'code' => $menu['code'] ?? null,
                'path' => $menu['path'] ?? ($menu['route_path'] ?? null),
                'required_permission' => $menu['required_permission'] ?? null,
            ];
        }, $menus));
    }

    protected static function normalizePermissions(array $permissions): array
    {
        return array_values(array_unique(array_filter(array_map(function ($permission) {
            if (is_array($permission)) {
                return $permission['code'] ?? null;
            }

            return is_string($permission) ? $permission : null;
        }, $permissions))));
    }

    protected static function flattenPermissions(array $permissions): array
    {
        if (count($permissions) === 1 && is_array($permissions[0] ?? null)) {
            return $permissions[0];
        }

        return $permissions;
    }

    protected static function emptyAccessSnapshot(): array
    {
        return [
            'app' => config('services.authorization_center.app_code', 'logstack-app'),
            'menus' => [],
            'permissions' => [],
            'loaded_at' => null,
        ];
    }
}
