<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FreeIPAService;
use App\Services\NextcloudService;
use App\Services\OdooService;
use Illuminate\Support\Facades\Log;
use App\Services\KeycloakAdminService;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    protected $freeIpa;

    public function __construct(FreeIPAService $freeIpa)
    {
        $this->freeIpa = $freeIpa;
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'   => 'required|alpha_num|min:3',
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email',
            'password'   => 'required|min:6',
            'group'      => 'required|in:dash_admin,dash_user',
        ]);

        // 1. Daftarkan User ke FreeIPA
        $createResult = $this->freeIpa->createUser(
            $request->username,
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->password
        );

        if (!$createResult['success']) {
            // 🔥 Amankan log jika FreeIPA menolak (Misal karena masalah password policy/domain redirect)
            Log::error("Gagal createUser di FreeIPA. Respons API: ", ['response' => $createResult]);
            return redirect()->route('dashboard')->with('error_user', "Gagal menambah user: " . $createResult['message']);
        }

        // 2. Suntikkan ke grup pilihan (dash_admin atau dash_user)
        $groupResult = $this->freeIpa->addUserToGroup($request->username, $request->group);

        if (!$groupResult['success']) {
            Log::error("User terbuat, tapi GAGAL masuk grup di FreeIPA. Respons API: ", ['response' => $groupResult]);
            return redirect()->route('dashboard')->with('success_user', "User dibuat, tetapi GAGAL dimasukkan ke grup otorisasi: " . $groupResult['message']);
        }

        return redirect()->route('dashboard')->with('success_user', "User {$request->username} sukses terdaftar di grup {$request->group}!");
    }

    public function destroy($username)
    {
        if ($username === 'arif' || $username === 'admin') {
            return redirect()->route('dashboard')->with('error_user', 'Proteksi Sistem: Akun root administrator tidak boleh dihapus!');
        }

        $errors    = [];
        $successes = [];

        // Cari local user sekali, reuse di semua step
        $localUser = \App\Models\User::where('username', $username)->first()
            ?? \App\Models\User::where('email', 'like', $username . '@%')->first();

        // 1. Hapus dari FreeIPA
        $ipaResult = $this->freeIpa->deleteUser($username);
        if ($ipaResult['success']) {
            $successes[] = 'FreeIPA';
        } else {
            $errors[] = "FreeIPA: " . $ipaResult['message'];
            Log::error("Cascade delete: gagal hapus {$username} dari FreeIPA: " . $ipaResult['message']);
        }

        // 2. Hapus dari Nextcloud
        try {
            $nextcloud = app(NextcloudService::class);
            $ncResult  = $nextcloud->deleteUser($username);
            if ($ncResult['success']) {
                $successes[] = 'Nextcloud';
            } else {
                $errors[] = "Nextcloud: " . $ncResult['message'];
                Log::error("Cascade delete: gagal hapus {$username} dari Nextcloud: " . $ncResult['message']);
            }
        } catch (\Exception $e) {
            $errors[] = "Nextcloud: " . $e->getMessage();
            Log::error("Cascade delete: Nextcloud exception untuk {$username}: " . $e->getMessage());
        }

        // 3. Archive di Odoo
        try {
            if ($localUser) {
                $odoo       = app(OdooService::class);
                $odooResult = $odoo->archiveUser($localUser->email);
                if ($odooResult['success']) {
                    $successes[] = 'Odoo';
                } else {
                    $errors[] = "Odoo: " . $odooResult['message'];
                    Log::error("Cascade delete: gagal archive {$username} di Odoo: " . $odooResult['message']);
                }
            } else {
                Log::info("Cascade delete: user {$username} tidak ditemukan di local DB, skip Odoo archive.");
                $successes[] = 'Odoo (skip - user tidak ada di DB)';
            }
        } catch (\Exception $e) {
            $errors[] = "Odoo: " . $e->getMessage();
            Log::error("Cascade delete: Odoo exception untuk {$username}: " . $e->getMessage());
        }

        // 4. Hapus dari Laravel DB + sessions
        try {
            if ($localUser) {
                DB::table('sessions')->where('user_id', $localUser->id)->delete();
                $localUser->delete();
                $successes[] = 'Laravel DB';
                Log::info("Cascade delete: user {$username} dihapus dari Laravel DB.");
            } else {
                $successes[] = 'Laravel DB (skip - user tidak ada)';
            }
        } catch (\Exception $e) {
            $errors[] = "Laravel DB: " . $e->getMessage();
            Log::error("Cascade delete: DB exception untuk {$username}: " . $e->getMessage());
        }

        // Return hasil
        if (empty($errors)) {
            return redirect()->route('dashboard')->with('success_user',
                "User {$username} berhasil dihapus dari: " . implode(', ', $successes) . ".");
        }

        $successMsg = !empty($successes) ? " Berhasil di: " . implode(', ', $successes) . "." : "";
        return redirect()->route('dashboard')->with('error_user',
            "Sebagian penghapusan gagal — " . implode('; ', $errors) . $successMsg);
    }

    public function update(Request $request, $username)
    {
        $request->validate([
            'first_name'      => 'required|string',
            'last_name'       => 'required|string',
            'email'           => 'required|email',
            'group'           => 'required|in:dash_admin,dash_user',
            'new_password'    => 'nullable|string|min:8',
            'confirm_password'=> 'nullable|string',
        ]);

        // Validasi manual confirm_password hanya jika new_password diisi
        if ($request->filled('new_password') && $request->new_password !== $request->confirm_password) {
            return redirect()->route('dashboard')->with('error_user', 'Konfirmasi password tidak cocok.');
        }

        $this->freeIpa->updateUser($username, $request->first_name, $request->last_name, $request->email);

        if ($request->group === 'dash_admin') {
            $this->freeIpa->removeUserFromGroup($username, 'dash_user');
            $this->freeIpa->addUserToGroup($username, 'dash_admin');
        } else {
            $this->freeIpa->removeUserFromGroup($username, 'dash_admin');
            $this->freeIpa->addUserToGroup($username, 'dash_user');
        }

        // Reset password jika diisi
        if ($request->filled('new_password')) {
            $passResult = $this->freeIpa->resetPassword($username, $request->new_password);
            if (!$passResult['success']) {
                Log::warning("Gagal reset password user {$username}: " . $passResult['message']);
            } else {
                Log::info("Password user {$username} berhasil direset oleh admin.");
            }
        }

        // Update role di local DB + force logout
        $newRole = $request->group === 'dash_admin' ? 'admin' : 'user';
        // Prioritas: cari by username dulu, baru by email
        $localUser = \App\Models\User::where('username', $username)->first();
        if (!$localUser) {
            $localUser = \App\Models\User::where('email', $username . '@logstack.web.id')->first();
        }
        if (!$localUser) {
            $localUser = \App\Models\User::where('email', 'like', $username . '@%')->first();
        }
        if ($localUser) {
            $localUser->update(['role' => $newRole]);
            $deleted = DB::table('sessions')->where('user_id', $localUser->id)->delete();
            Log::info("Force logout user {$username}: {$deleted} session(s) deleted, new role: {$newRole}");
        } else {
            Log::warning("Force logout: user {$username} not found in local DB");
        }

        // Sync Keycloak — hapus user dari Keycloak cache biar re-import dari FreeIPA
        $keycloak = app(KeycloakAdminService::class);
        $keycloak->syncUser($username);

        return redirect()->route('dashboard')->with('success_user', "Data dan Role user {$username} berhasil diperbarui! User akan diminta login ulang.");
    }

    public function toggleStatus($username, Request $request)
    {
        $shouldLock = $request->input('lock') == 1;
        $result = $this->freeIpa->toggleUserStatus($username, $shouldLock);

        if ($result['success']) {
            $statusMsg = $shouldLock ? 'dinonaktifkan (Disabled)' : 'diaktifkan kembali (Active)';
            // Force logout saat user di-lock
            if ($shouldLock) {
                $localUser = \App\Models\User::where('username', $username)
                    ->orWhere('email', 'like', $username . '@%')
                    ->first();
                if ($localUser) {
                    DB::table('sessions')->where('user_id', $localUser->id)->delete();
                }
            }
            return redirect()->route('dashboard')->with('success_user', "User {$username} berhasil {$statusMsg}!");
        }

        return redirect()->route('dashboard')->with('error_user', "Gagal mengubah status user: " . $result['message']);
    }
}
