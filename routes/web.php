<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminUserController;
use Illuminate\Support\Facades\Auth;

// 1. Halaman Depan (Welcome) dengan proteksi deteksi Session dan Role
Route::get('/', function () {
    if (Auth::check()) {
        // Navigasi cerdas saat user membuka halaman utama dalam kondisi sudah login
        return Auth::user()->role === 'admin'
            ? redirect()->route('dashboard')
            : redirect()->route('user.dashboard');
    }
    return view('welcome');
})->name('home');

// 2. Route Utama SSO & Callback (Menggunakan GET untuk kemudahan integrasi link)
Route::get('/login/sso', [LoginController::class, 'redirectToProvider'])->name('login.sso');
Route::get('/login', [LoginController::class, 'redirectToProvider'])->name('login');
Route::get('/login/callback', [LoginController::class, 'handleProviderCallback'])->name('login.callback');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// 3. JALUR KHUSUS ADMIN (Hanya untuk group dash_admin)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

    // Halaman Monitor Khusus Nextcloud Storage & Global Logs (Untuk internal system)
    Route::get('/admin/nextcloud', [DashboardController::class, 'nextcloudMonitor'])->name('admin.nextcloud');

    // 🔥 JALUR BARU: Endpoint Handler untuk mengubah kuota user Nextcloud via Dropdown Form
    Route::post('/admin/nextcloud/update-quota', [DashboardController::class, 'updateUserQuota'])->name('admin.nextcloud.update-quota');

    // API CRUD FreeIPA Router
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{username}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::patch('/admin/users/{username}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::delete('/admin/users/{username}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
});

// 4. JALUR KHUSUS USER BIASA (Untuk group ipausers biasa)
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/user-dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
});
