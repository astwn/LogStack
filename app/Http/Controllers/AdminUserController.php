<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FreeIPAService;
use Illuminate\Support\Facades\Log;

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

        $result = $this->freeIpa->deleteUser($username);

        if ($result['success']) {
            return redirect()->route('dashboard')->with('success_user', "User {$username} berhasil dihapus!");
        }

        return redirect()->route('dashboard')->with('error_user', "Gagal menghapus user: " . $result['message']);
    }

    public function update(Request $request, $username)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email',
            'group'      => 'required|in:dash_admin,dash_user',
        ]);

        $this->freeIpa->updateUser($username, $request->first_name, $request->last_name, $request->email);

        if ($request->group === 'dash_admin') {
            $this->freeIpa->removeUserFromGroup($username, 'dash_user');
            $this->freeIpa->addUserToGroup($username, 'dash_admin');
        } else {
            $this->freeIpa->removeUserFromGroup($username, 'dash_admin');
            $this->freeIpa->addUserToGroup($username, 'dash_user');
        }

        return redirect()->route('dashboard')->with('success_user', "Data dan Role user {$username} berhasil diperbarui!");
    }

    public function toggleStatus($username, Request $request)
    {
        $shouldLock = $request->input('lock') == 1;
        $result = $this->freeIpa->toggleUserStatus($username, $shouldLock);

        if ($result['success']) {
            $statusMsg = $shouldLock ? 'dinonaktifkan (Disabled)' : 'diaktifkan kembali (Active)';
            return redirect()->route('dashboard')->with('success_user', "User {$username} berhasil {$statusMsg}!");
        }

        return redirect()->route('dashboard')->with('error_user', "Gagal mengubah status user: " . $result['message']);
    }
}
