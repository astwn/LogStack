<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\NextcloudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
class LoginController extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::driver('keycloak')->redirect();
    }
    public function handleProviderCallback(Request $request)
    {
        try {
            $keycloakUser = Socialite::driver('keycloak')->user();
            $rawData = $keycloakUser->getRaw();
            Log::info($rawData);
            // GROUP ROLE
            $groups = $rawData['groups'] ?? [];
            $role = str_contains(json_encode($groups), 'dash_admin') ? 'admin' : 'user';
            // DATA USER
            $email = $keycloakUser->getEmail();
            $username =
                $rawData['preferred_username']
                ?? ($rawData['uid'][0] ?? null)
                ?? $keycloakUser->getNickname()
                ?? explode('@', $email)[0];
            Log::info([
                'sync_username' => $username,
                'sync_email' => $email
            ]);
            // Bangun nama dari given_name + family_name (dari FreeIPA givenname + sn)
            $givenName  = trim($rawData['given_name'] ?? '');
            $familyName = trim($rawData['family_name'] ?? '');
            $fullName   = trim($givenName . ' ' . $familyName);
            if (empty($fullName)) {
                $fullName = $keycloakUser->getName() ?? $username;
            }

            // UPSERT USER
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'       => $fullName,
                    'username'   => $username,
                    'role'       => $role,
                    'password'   => bcrypt(Str::random(24)),
                    'last_login' => now(),
                ]
            );

            // AUTO-GENERATE NEXTCLOUD APP PASSWORD jika belum ada atau tidak valid
            try {
                $nextcloud = app(NextcloudService::class);

                // AUTO-PROVISION user baru ke Nextcloud jika belum ada
                $email       = $user->email ?? ($username . '@logstack.web.id');
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
            $idToken = $keycloakUser->tokenResponse['id_token']
                ?? $keycloakUser->accessTokenResponseBody['id_token']
                ?? null;
            if ($idToken) {
                $request->session()->put('id_token_hint', $idToken);
            }
            if (isset($keycloakUser->token)) {
                $request->session()->put('access_token', $keycloakUser->token);
            }
            // LOG ACTIVITY
            ActivityLogService::login($username, $user->id);
            return redirect($user->role === 'admin' ? '/dashboard' : '/user-dashboard');
        } catch (\Exception $e) {
            Log::error('SSO ERROR: ' . $e->getMessage());
            return "Login Error: " . $e->getMessage();
        }
    }
    public function logout(Request $request)
    {
        $baseUrl  = rtrim(env('KEYCLOAK_BASE_URL'), '/');
        $realm    = env('KEYCLOAK_REALM');
        $clientId = env('KEYCLOAK_CLIENT_ID');
        $idTokenHint = $request->session()->get('id_token_hint');

        // LOG ACTIVITY sebelum session dihapus
        $user = Auth::user();
        if ($user) {
            ActivityLogService::logout($user->username ?? $user->email, $user->id);

            // Force logout Nextcloud — hapus semua browser session token via SSH
            try {
                $username = $user->username ?? explode('@', $user->email)[0];
                $sshKey   = env('NEXTCLOUD_SSH_KEY', '/var/www/.ssh/id_rsa');
                $sshPort  = env('NEXTCLOUD_SSH_PORT', '2227');
                $sshHost  = env('SERVICE_IP_NEXTCLOUD', '172.18.4.105');
                $occPath  = '/var/www/nextcloud/occ';

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

        // 1. URL Keycloak Logout
        $logoutUrl = "{$baseUrl}/realms/{$realm}/protocol/openid-connect/logout";
        $params = [
            'client_id'                => $clientId,
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect($oauth2SignOutUrl);
    }
}
