<?php
namespace App\Services;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ActivityLogService
{
    public static function log(
        string $app,
        string $action,
        string $description = '',
        ?string $username = null,
        ?int $userId = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): void {
        try {
            // Auto-fill dari Auth jika tidak disuplai
            $user = Auth::user();
            ActivityLog::create([
                'user_id'    => $userId ?? $user?->id,
                'username'   => $username ?? $user?->username ?? $user?->email,
                'app'        => $app,
                'action'     => $action,
                'description'=> $description,
                'ip_address' => $ip ?? request()->ip(),
                'user_agent' => $userAgent ?? request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Jangan sampai log error merusak flow utama
            \Illuminate\Support\Facades\Log::error('ActivityLog failed: ' . $e->getMessage());
        }
    }
    public static function login(string $username, ?int $userId = null): void
    {
        self::log('laravel', 'login', "Login ke LogStack Portal", $username, $userId);
    }
    public static function logout(string $username, ?int $userId = null): void
    {
        self::log('laravel', 'logout', "Logout dari LogStack Portal", $username, $userId);
    }
    public static function openApp(string $app, string $username, ?int $userId = null): void
    {
        $appNames = [
            'sogo'      => 'SOGo Webmail',
            'nextcloud' => 'Nextcloud Drive',
            'odoo'      => 'Odoo ERP',
            'grafana'   => 'Grafana Monitor',
            'keycloak'  => 'Keycloak SSO',
        ];
        $appName = $appNames[$app] ?? $app;
        self::log($app, 'open_app', "Membuka {$appName}", $username, $userId);
    }
}
