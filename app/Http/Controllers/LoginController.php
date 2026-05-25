<?php

namespace App\Http\Controllers;

use App\Models\User;
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

            // 🔥 PENTING: ambil username dari FreeIPA / Keycloak
            $username =
                $rawData['preferred_username']
                ?? ($rawData['uid'][0] ?? null)
                ?? $keycloakUser->getNickname()
                ?? explode('@', $email)[0];

            Log::info([
                'sync_username' => $username,
                'sync_email' => $email
            ]);

            // UPSERT USER (ANTI NULL)
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $keycloakUser->getName() ?? $username,
                    'username' => $username,
                    'role' => $role,
                    'password' => bcrypt(Str::random(24)),
                    'last_login' => now(),
                ]
            );

            Auth::login($user);
            $request->session()->regenerate();

            // ID TOKEN
            $idToken = $keycloakUser->tokenResponse['id_token']
                ?? $keycloakUser->accessTokenResponseBody['id_token']
                ?? null;

            if ($idToken) {
                $request->session()->put('id_token_hint', $idToken);
            }

            return redirect($user->role === 'admin' ? '/dashboard' : '/user-dashboard');

        } catch (\Exception $e) {
            Log::error('SSO ERROR: ' . $e->getMessage());
            return "Login Error: " . $e->getMessage();
        }
    }

    public function logout(Request $request)
    {
    $baseUrl = rtrim(env('KEYCLOAK_BASE_URL'), '/');
    $realm = env('KEYCLOAK_REALM');
    $clientId = env('KEYCLOAK_CLIENT_ID');
    $idTokenHint = $request->session()->get('id_token_hint');

    // Buat URL untuk Keycloak Logout
    $logoutUrl = "{$baseUrl}/realms/{$realm}/protocol/openid-connect/logout";
    $params = [
        'client_id' => $clientId,
        'post_logout_redirect_uri' => config('app.url'),
    ];
    if ($idTokenHint) {
        $params['id_token_hint'] = $idTokenHint;
    }
    $fullLogoutUrl = $logoutUrl . '?' . http_build_query($params);

    // Hapus sesi Laravel
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Redirect ke Odoo terlebih dahulu (Force Logout)
    // Odoo akan menghapus cookie, lalu otomatis melempar ke $fullLogoutUrl
    return redirect("https://erp.logstack.web.id/auth_oauth/force_logout?redirect=" . urlencode($fullLogoutUrl));
    }

}
