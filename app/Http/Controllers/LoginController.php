<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\AuthorizationCenterClient;
use App\Services\NextcloudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class LoginController extends Controller
{
    public function redirectToProvider(AuthorizationCenterClient $authorizationCenter)
    {
        $redirectUrl = $authorizationCenter->redirectUrl(route('login.callback'));
        Log::info('START REDIRECT TO PROVIDER', [
            'callback_url' => route('login.callback'),
            'redirect_url' => $redirectUrl,
        ]);

        return redirect($redirectUrl);
    }

    public function handleProviderCallback(Request $request, AuthorizationCenterClient $authorizationCenter)
    {
        try {
            Log::info('START SSO CALLBACK', [
                'has_code' => $request->filled('code'),
                'error' => $request->query('error'),
                'session_id' => $request->session()->getId(),
            ]);

            if ($request->filled('error')) {
                throw new \RuntimeException('SSO Error: ' . $request->query('error'));
            }
            if (!$request->filled('code')) {
                throw new \RuntimeException('SSO callback code is missing.');
            }

            $tokens = $authorizationCenter->exchangeCode($request->query('code'));
            $rawData = array_replace_recursive(
                $this->decodeJwt($tokens['access_token'] ?? ''),
                $this->decodeJwt($tokens['id_token'] ?? '')
            );
            Log::info($rawData);
            // GROUP ROLE
            $groups = $rawData['groups'] ?? ($rawData['realm_access']['roles'] ?? []);
            $role = str_contains(json_encode($groups), 'dash_admin') ? 'admin' : 'user';
            // DATA USER
            $email = $rawData['email'] ?? null;
            if (!$email) {
                throw new \RuntimeException('Email tidak ditemukan dari token SSO.');
            }
            $username =
                $rawData['preferred_username']
                ?? ($rawData['uid'][0] ?? null)
                ?? ($rawData['nickname'] ?? null)
                ?? explode('@', $email)[0];
            Log::info([
                'sync_username' => $username,
                'sync_email' => $email
            ]);
            // Bangun nama dari given_name + family_name (dari FreeIPA givenname + sn)
            $givenName = trim($rawData['given_name'] ?? '');
            $familyName = trim($rawData['family_name'] ?? '');
            $fullName = trim($givenName . ' ' . $familyName);
            if (empty($fullName)) {
                $fullName = $rawData['name'] ?? $username;
            }

            // UPSERT USER
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $fullName,
                    'username' => $username,
                    'role' => $role,
                    'password' => bcrypt(Str::random(24)),
                    'last_login' => now(),
                ]
            );

            // AUTO-GENERATE NEXTCLOUD APP PASSWORD jika belum ada atau tidak valid
            try {
                $nextcloud = app(NextcloudService::class);

                // AUTO-PROVISION user baru ke Nextcloud jika belum ada
                $email = $user->email ?? ($username . '@logstack.web.id');
                $displayName = $user->name ?? $username;
                $nextcloud->provisionUser($username, $email, $displayName);

                $needGenerate = false;

                if (empty($user->nc_app_password)) {
                    $needGenerate = true;
                    Log::info("Nextcloud: user {$username} belum punya app password, generate baru.");
                } else {
                    // Validasi apakah app password masih valid
                    $isValid = $nextcloud->isAppPasswordValid($username, $user->nc_app_password);
                    if (!$isValid) {
                        $needGenerate = true;
                        Log::info("Nextcloud: app password {$username} tidak valid, regenerate.");
                        // Revoke token lama dulu
                        $nextcloud->revokeAppPassword($username);
                    }
                }

                if ($needGenerate) {
                    $appPassword = $nextcloud->generateAppPassword($username);
                    if ($appPassword) {
                        $user->update(['nc_app_password' => $appPassword]);
                        Log::info("Nextcloud: app password berhasil di-generate untuk {$username}.");
                    } else {
                        Log::warning("Nextcloud: gagal generate app password untuk {$username}.");
                    }
                }
            } catch (\Exception $ncEx) {
                // Jangan sampai error Nextcloud menggagalkan login
                Log::error("Nextcloud app password error: " . $ncEx->getMessage());
            }

            Auth::login($user);
            $request->session()->regenerate();
            // ID TOKEN
            if (!empty($tokens['id_token'])) {
                $request->session()->put('id_token_hint', $tokens['id_token']);
                $request->session()->put('id_token', $tokens['id_token']);
            }
            if (!empty($tokens['access_token'])) {
                $request->session()->put('access_token', $tokens['access_token']);
            }
            if (!empty($tokens['refresh_token'])) {
                $request->session()->put('refresh_token', $tokens['refresh_token']);
            }
            if (!empty($tokens['session_state'])) {
                $request->session()->put('keycloak_session_state', $tokens['session_state']);
            }
            if (!empty($tokens['expires_in'])) {
                $request->session()->put('access_token_expires_at', now()->addSeconds((int) $tokens['expires_in'])->timestamp);
            }
            try {
                $authorizationCenter->refreshAccessSnapshot();
            } catch (\Exception $accessEx) {
                Log::warning('Authorization access sync gagal: ' . $accessEx->getMessage());
                AuthorizationCenterClient::storeAccessSnapshot([
                    'app' => config('services.authorization_center.app_code', 'logstack-app'),
                    'menus' => [],
                    'permissions' => [],
                ]);
            }
            // LOG ACTIVITY
            ActivityLogService::login($username, $user->id);
            Log::info('SSO CALLBACK SUCCESS', [
                'user_id' => $user->id,
                'role' => $user->role,
                'session_id' => $request->session()->getId(),
            ]);

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            Log::error('SSO ERROR: ' . $e->getMessage());
            return "Login Error: " . $e->getMessage();
        }
    }

    public function logout(Request $request, AuthorizationCenterClient $authorizationCenter)
    {
        $baseUrl = rtrim(env('KEYCLOAK_BASE_URL'), '/');
        $realm = env('KEYCLOAK_REALM');
        $clientId = env('KEYCLOAK_CLIENT_ID');
        $idTokenHint = $request->session()->get('id_token_hint');
        $refreshToken = $request->session()->get('refresh_token');

        // LOG ACTIVITY sebelum session dihapus
        $user = Auth::user();
        if ($user) {
            ActivityLogService::logout($user->username ?? $user->email, $user->id);

            // Force logout Nextcloud — hapus semua browser session token via SSH
            if ($this->isLocalHttpApp()) {
                Log::info('Nextcloud browser session revoke dilewati untuk local HTTP.');
            } else {
                $username = $user->username ?? explode('@', $user->email)[0];

                try {
                    $sshKey = env('NEXTCLOUD_SSH_KEY', '/var/www/.ssh/id_rsa');
                    $sshPort = env('NEXTCLOUD_SSH_PORT', '2227');
                    $sshHost = env('SERVICE_IP_NEXTCLOUD', '172.18.4.105');
                    $occPath = '/var/www/nextcloud/occ';

                    // List semua token user
                    $listCmd = "ssh -i {$sshKey} -o StrictHostKeyChecking=no -p {$sshPort} root@{$sshHost} "
                        . "'php {$occPath} user:auth-tokens:list {$username} 2>&1'";
                    $listOutput = shell_exec($listCmd);

                    // Parse token IDs yang bukan logstack-portal (browser sessions)
                    if (preg_match_all('/\|\s*(\d+)\s*\|(?!.*logstack-portal).*temporary/', $listOutput, $matches)) {
                        foreach ($matches[1] as $tokenId) {
                            $delCmd = "ssh -i {$sshKey} -o StrictHostKeyChecking=no -p {$sshPort} root@{$sshHost} "
                                . "'php {$occPath} user:auth-tokens:delete {$username} {$tokenId} 2>&1'";
                            shell_exec($delCmd);
                            Log::info("Nextcloud: browser session token {$tokenId} dihapus untuk user {$username}");
                        }
                    } else {
                        Log::info("Nextcloud: tidak ada browser session token untuk dihapus untuk user {$username}");
                    }
                } catch (\Exception $e) {
                    Log::warning("Nextcloud logout gagal untuk {$username}: " . $e->getMessage());
                }
            }
        }

        // 1. URL Keycloak Logout
        try {
            $authorizationCenter->logout($refreshToken);
        } catch (\Exception $e) {
            Log::warning('Authorization Center logout gagal: ' . $e->getMessage());
        }

        $logoutUrl = "{$baseUrl}/realms/{$realm}/protocol/openid-connect/logout";
        $params = [
            'client_id' => $clientId,
            'post_logout_redirect_uri' => config('app.url'),
        ];
        if ($idTokenHint) {
            $params['id_token_hint'] = $idTokenHint;
        }
        $keycloakLogoutUrl = $logoutUrl . '?' . http_build_query($params);
        // 2. Odoo force logout → Keycloak logout
        $odooLogoutUrl = env('SERVICE_URL_ODOO', 'https://erp.logstack.web.id') . '/auth_oauth/force_logout?redirect=' . urlencode($keycloakLogoutUrl);
        // 3. oauth2-proxy sign_out → Odoo → Keycloak
        $oauth2SignOutUrl = env('SERVICE_URL_SOGO', 'https://mbox.logstack.web.id') . '/oauth2/sign_out?rd=' . urlencode($odooLogoutUrl);
        // Hapus sesi Laravel
        $this->clearLaravelSession($request);

        if ($this->isLocalHttpApp()) {
            Log::info('LOCAL LOGOUT COMPLETE', [
                'redirect_url' => route('home'),
            ]);

            return redirect()
                ->route('home')
                ->withCookie(Cookie::forget(config('session.cookie')));
        }

        return redirect($oauth2SignOutUrl)
            ->withCookie(Cookie::forget(config('session.cookie')));
    }

    private function clearLaravelSession(Request $request): void
    {
        AuthorizationCenterClient::forgetAccessSnapshot();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function isLocalHttpApp(): bool
    {
        $appUrl = (string) config('app.url');

        return app()->environment('local')
            || str_starts_with($appUrl, 'http://localhost')
            || str_starts_with($appUrl, 'http://127.0.0.1');
    }

    private function decodeJwt(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) < 2) {
            return [];
        }

        $payload = strtr($parts[1], '-_', '+/');
        $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);
        $decoded = base64_decode($payload, true);
        if ($decoded === false) {
            return [];
        }

        $claims = json_decode($decoded, true);
        return is_array($claims) ? $claims : [];
    }
}
