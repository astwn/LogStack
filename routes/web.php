<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\OnlyOfficeViewController;
use App\Http\Controllers\OnlyOfficeCallbackController;
use App\Http\Controllers\BrandingController;
use App\Http\Controllers\AccessRequestController;
use App\Services\AuthorizationCenterClient;
use Illuminate\Support\Facades\Auth;

// 1. Halaman Depan (Welcome) dengan proteksi deteksi Session dan Role
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

// 2. Route Utama SSO & Callback
Route::get('/login/sso', [LoginController::class, 'redirectToProvider'])->name('login.sso');
Route::get('/login', [LoginController::class, 'redirectToProvider'])->name('login');
Route::get('/login/callback', [LoginController::class, 'handleProviderCallback'])->name('login.callback');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// 2b. Access Request (Public)
Route::post('/access-request', [AccessRequestController::class, 'store'])->name('access-request.store');
Route::get('/api/access-request/pending-count', [AccessRequestController::class, 'pendingCount'])
    ->middleware(['auth', 'authz:logstack.admin-access-requests.read'])
    ->name('access-request.pending-count');

// 3. DASHBOARD UTAMA (rendering menu dikontrol oleh Authorization Center)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
    Route::get('/user-dashboard', fn () => redirect()->route('dashboard'))->name('user.dashboard');
    Route::get('/admin/nextcloud', [DashboardController::class, 'nextcloudMonitor'])->name('admin.nextcloud');
    Route::post('/admin/nextcloud/update-quota', [DashboardController::class, 'updateUserQuota'])->name('admin.nextcloud.update-quota');

    // API CRUD FreeIPA Router
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{username}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::patch('/admin/users/{username}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::delete('/admin/users/{username}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');

    // Branding Routes
    Route::post('/admin/branding', [BrandingController::class, 'save'])->name('admin.branding.save');
    Route::post('/admin/branding/reset', [BrandingController::class, 'reset'])->name('admin.branding.reset');

    // Access Request Routes (Admin)
    Route::middleware('authz:logstack.admin-access-requests.read')->group(function () {
        Route::get('/admin/access-requests', [AccessRequestController::class, 'index'])->name('admin.access-requests.index');
        Route::post('/admin/access-requests/{id}/approve', [AccessRequestController::class, 'approve'])->name('admin.access-requests.approve');
        Route::post('/admin/access-requests/{id}/reject', [AccessRequestController::class, 'reject'])->name('admin.access-requests.reject');
    });
});

// 5. JALUR BERSAMA ONLYOFFICE DOCUMENT (Wajib Login)
Route::middleware(['auth'])->group(function () {
    Route::get('/api/authz/access', function () {
        return response()->json([
            'success' => true,
            'data' => AuthorizationCenterClient::accessSnapshot(),
        ]);
    })->name('api.authz.access');

    Route::post('/api/authz/access/refresh', function (AuthorizationCenterClient $authorizationCenter) {
        return response()->json([
            'success' => true,
            'data' => $authorizationCenter->refreshAccessSnapshot(force: true),
        ]);
    })->name('api.authz.access.refresh');

    Route::get('/document/edit', [OnlyOfficeViewController::class, 'openDocument'])->name('document.edit');
    Route::get('/api/documents/list', [OnlyOfficeViewController::class, 'getFilesList'])->name('api.documents.list');
    Route::delete('/api/documents/delete', [OnlyOfficeViewController::class, 'deleteDocument'])->name('api.documents.delete');
    Route::post('/api/documents/share', [DashboardController::class, 'shareDocument'])->name('api.documents.share');
    Route::get('/api/documents/shared', [DashboardController::class, 'getSharedDocuments'])->name('api.documents.shared');

    // Endpoint API Nextcloud File Manager
    Route::get('/api/nextcloud/files', [DashboardController::class, 'getFiles']);
    Route::post('/api/nextcloud/upload', [DashboardController::class, 'uploadFile']);
    Route::delete('/api/nextcloud/delete', [DashboardController::class, 'deleteFile']);
    Route::get('/api/nextcloud/preview', [DashboardController::class, 'previewFile']);
    Route::get('/api/nextcloud/download', [DashboardController::class, 'downloadFile']);
});

// 6. ≡ƒöÑ GERBANG CALLBACK ONLYOFFICE (Wajib di luar auth grup agar bisa di-hit dari luar)
Route::post('/onlyoffice/callback', [OnlyOfficeCallbackController::class, 'handle']);
Route::get('/document/download-raw', [App\Http\Controllers\OnlyOfficeViewController::class, 'downloadRawFile']);
// 7. PROXY REDIRECT + LOG untuk open app
Route::middleware(['auth'])->group(function () {
    Route::get('/open/sogo', function () {
        \App\Services\ActivityLogService::openApp('sogo', Auth::user()->username ?? Auth::user()->email, Auth::id());
        return redirect(env('SERVICE_URL_SOGO', 'https://mbox.logstack.web.id') . '/SOGo');
    })->name('open.sogo');
    Route::get('/open/nextcloud', function () {
        \App\Services\ActivityLogService::openApp('nextcloud', Auth::user()->username ?? Auth::user()->email, Auth::id());
        return redirect(env('SERVICE_URL_NEXTCLOUD', 'https://drive.logstack.web.id'));
    })->name('open.nextcloud');
    Route::get('/open/odoo', function () {
        \App\Services\ActivityLogService::openApp('odoo', Auth::user()->username ?? Auth::user()->email, Auth::id());
        return redirect(env('SERVICE_URL_ODOO', 'https://erp.logstack.web.id'));
    })->name('open.odoo');
    Route::get('/open/grafana', function () {
        \App\Services\ActivityLogService::openApp('grafana', Auth::user()->username ?? Auth::user()->email, Auth::id());
        return redirect(env('SERVICE_URL_GRAFANA', 'https://monit.logstack.web.id'));
    })->name('open.grafana');
    Route::get('/open/keycloak', function () {
        \App\Services\ActivityLogService::openApp('keycloak', Auth::user()->username ?? Auth::user()->email, Auth::id());
        return redirect(env('SERVICE_URL_KEYCLOAK', 'https://sso.logstack.web.id'));
    })->name('open.keycloak');
});
// 9. ACTIVITY LOG API (JSON untuk Alpine.js)
Route::middleware(['auth'])->get('/admin/activity-log/api', function () {
    $query = \App\Models\ActivityLog::latest();
    if (request('username')) $query->byUsername(request('username'));
    if (request('app')) $query->byApp(request('app'));
    if (request('action')) $query->where('action', request('action'));
    if (request('date_from') && request('date_to')) {
        $query->byDateRange(request('date_from') . ' 00:00:00', request('date_to') . ' 23:59:59');
    }
    $logs = $query->paginate(25);
    $logs->getCollection()->transform(function ($log) {
        return [
            'id'          => $log->id,
            'username'    => $log->username,
            'app'         => $log->app,
            'action'      => $log->action,
            'description' => $log->description,
            'ip_address'  => $log->ip_address,
            'date'        => $log->created_at->timezone('Asia/Jakarta')->format('d M Y'),
            'time'        => $log->created_at->timezone('Asia/Jakarta')->format('H:i:s'),
        ];
    });
    return response()->json($logs);
})->name('admin.activity-log.api');
// 10. RECENT ACTIVITY API (untuk widget main dashboard - filter by current user)
Route::middleware(['auth'])->get('/admin/recent-activity', function () {
    $username = Auth::user()->username ?? Auth::user()->email;
    $logs = \App\Models\ActivityLog::latest()
        ->where('username', $username)
        ->take(8)
        ->get()
        ->map(function ($log) {
            return [
                'id'          => $log->id,
                'username'    => $log->username,
                'app'         => $log->app,
                'action'      => $log->action,
                'description' => $log->description,
                'date'        => $log->created_at->timezone('Asia/Jakarta')->format('d M Y'),
                'time'        => $log->created_at->timezone('Asia/Jakarta')->format('H:i'),
            ];
        });
    return response()->json($logs);
})->name('admin.recent-activity');

// 12. PROFILE STATS API
Route::middleware(['auth'])->get('/api/profile/stats', function () {
    $user = Auth::user();
    $username = $user->username ?? explode('@', $user->email)[0];

    // Total aktivitas hari ini
    $todayCount = \App\Models\ActivityLog::where('username', $username)
        ->whereDate('created_at', today())
        ->count();

    // Total aktivitas minggu ini
    $weekCount = \App\Models\ActivityLog::where('username', $username)
        ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
        ->count();

    // Total aktivitas keseluruhan
    $totalCount = \App\Models\ActivityLog::where('username', $username)->count();

    // App paling sering dibuka
    $topApp = \App\Models\ActivityLog::where('username', $username)
        ->select('app', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
        ->groupBy('app')
        ->orderByDesc('total')
        ->first();

    // Last login
    $lastLogin = \App\Models\ActivityLog::where('username', $username)
        ->where('action', 'login')
        ->latest()
        ->first();

    // Sesi aktif sekarang
    $session = \Illuminate\Support\Facades\DB::table('sessions')
        ->where('user_id', $user->id)
        ->orderByDesc('last_activity')
        ->first();

    return response()->json([
        'today_count'    => $todayCount,
        'week_count'     => $weekCount,
        'total_count'    => $totalCount,
        'top_app'        => $topApp ? $topApp->app : '-',
        'top_app_count'  => $topApp ? $topApp->total : 0,
        'last_login'     => $lastLogin ? \Carbon\Carbon::parse($lastLogin->created_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-',
        'session_ip'     => $session ? $session->ip_address : request()->ip(),
        'session_agent'  => $session ? $session->user_agent : request()->userAgent(),
        'session_time'   => $session ? \Carbon\Carbon::createFromTimestamp($session->last_activity)->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-',
    ]);
})->name('api.profile.stats');


// 13. CHANGE PASSWORD VIA FREEIPA
Route::middleware(['auth'])->post('/api/profile/change-password', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'current_password' => 'required|string',
        'new_password'     => 'required|string|min:8',
        'confirm_password' => 'required|same:new_password',
    ]);

    $user = \Illuminate\Support\Facades\Auth::user();
    $username = $user->username ?? explode('@', $user->email)[0];

    $freeIpa = app(\App\Services\FreeIPAService::class);
    $result = $freeIpa->changePassword($username, $request->current_password, $request->new_password);

    return response()->json($result);
})->name('api.profile.change-password');

// 11. MAIL STATS API (untuk widget SOGo dashboard)
Route::middleware(['auth'])->get('/api/mail/stats', function () {
    $username = Auth::user()->username ?? explode('@', Auth::user()->email)[0];
    $imap = new \App\Services\DoveadmService();
    $stats = $imap->getMailStats($username);
    return response()->json($stats);
})->name('api.mail.stats');

// 14. ODOO DASHBOARD API
Route::middleware(['auth'])->get('/api/odoo/dashboard', function () {
    $user = Auth::user();
    $email = $user->email ?? ($user->username . '@logstack.web.id');
    $odoo = app(\App\Services\OdooService::class);
    $data = $odoo->getUserDashboard($email);
    return response()->json($data);
})->name('api.odoo.dashboard');

// 15. PROMETHEUS METRICS API
Route::middleware(['auth'])->get('/api/metrics', function () {
    $prometheusUrl = env('PROMETHEUS_URL', 'http://172.18.4.108:9090');

    $nodes = [
        '172.18.4.101' => 'Nginx Gateway',
        '172.18.4.102' => 'App Portal',
        '172.18.4.103' => 'FreeIPA',
        '172.18.4.104' => 'Keycloak',
        '172.18.4.105' => 'Nextcloud',
        '172.18.4.106' => 'Odoo ERP',
        '172.18.4.107' => 'SOGo Mail',
        '172.18.4.108' => 'Monitoring',
        '172.18.4.109' => 'Database',
        '172.18.4.112' => 'OnlyOffice',
    ];

    $queryPrometheus = function($url, $query) {
        $ch = curl_init($url . '/api/v1/query?query=' . urlencode($query));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        if (!$res) return [];
        $data = json_decode($res, true);
        $result = [];
        foreach ($data['data']['result'] ?? [] as $item) {
            $instance = explode(':', $item['metric']['instance'] ?? '')[0];
            $result[$instance] = round((float)$item['value'][1], 2);
        }
        return $result;
    };

    // CPU usage per node
    $cpuQuery = '100 - (avg by (instance) (rate(node_cpu_seconds_total{mode="idle",job="logstack-nodes"}[5m])) * 100)';
    $cpuData = $queryPrometheus($prometheusUrl, $cpuQuery);

    // RAM usage percent
    $ramQuery = '100 - ((node_memory_MemAvailable_bytes{job="logstack-nodes"} / node_memory_MemTotal_bytes{job="logstack-nodes"}) * 100)';
    $ramData = $queryPrometheus($prometheusUrl, $ramQuery);

    // RAM total bytes
    $ramTotalQuery = 'node_memory_MemTotal_bytes{job="logstack-nodes"}';
    $ramTotalData = $queryPrometheus($prometheusUrl, $ramTotalQuery);

    // RAM used bytes
    $ramUsedQuery = 'node_memory_MemTotal_bytes{job="logstack-nodes"} - node_memory_MemAvailable_bytes{job="logstack-nodes"}';
    $ramUsedData = $queryPrometheus($prometheusUrl, $ramUsedQuery);

    // Disk usage percent
    $diskQuery = '100 - ((node_filesystem_avail_bytes{job="logstack-nodes",mountpoint="/",fstype!="tmpfs"} / node_filesystem_size_bytes{job="logstack-nodes",mountpoint="/",fstype!="tmpfs"}) * 100)';
    $diskData = $queryPrometheus($prometheusUrl, $diskQuery);

    // Disk total bytes
    $diskTotalQuery = 'node_filesystem_size_bytes{job="logstack-nodes",mountpoint="/",fstype!="tmpfs"}';
    $diskTotalData = $queryPrometheus($prometheusUrl, $diskTotalQuery);

    // Disk used bytes
    $diskUsedQuery = 'node_filesystem_size_bytes{job="logstack-nodes",mountpoint="/",fstype!="tmpfs"} - node_filesystem_avail_bytes{job="logstack-nodes",mountpoint="/",fstype!="tmpfs"}';
    $diskUsedData = $queryPrometheus($prometheusUrl, $diskUsedQuery);

    // Helper: format bytes
    $fmtBytes = function($bytes) {
        if ($bytes === null) return '-';
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 0) . ' MB';
        return round($bytes / 1024, 0) . ' KB';
    };

    // Build response
    $metrics = [];
    foreach ($nodes as $ip => $name) {
        $metrics[] = [
            'ip'         => $ip,
            'name'       => $name,
            'cpu'        => $cpuData[$ip] ?? null,
            'ram'        => $ramData[$ip] ?? null,
            'ram_used'   => isset($ramUsedData[$ip]) ? $fmtBytes($ramUsedData[$ip]) : '-',
            'ram_total'  => isset($ramTotalData[$ip]) ? $fmtBytes($ramTotalData[$ip]) : '-',
            'disk'       => $diskData[$ip] ?? null,
            'disk_used'  => isset($diskUsedData[$ip]) ? $fmtBytes($diskUsedData[$ip]) : '-',
            'disk_total' => isset($diskTotalData[$ip]) ? $fmtBytes($diskTotalData[$ip]) : '-',
            'status'     => isset($cpuData[$ip]) ? 'up' : 'down',
        ];
    }

    return response()->json($metrics);
})->name('api.metrics');

// 16. KEYCLOAK STATS API
Route::middleware(['auth'])->get('/api/keycloak/stats', function () {
    $base = env('KEYCLOAK_INTERNAL_URL', 'http://172.18.4.104:8080');

    $ch = curl_init($base . '/realms/master/protocol/openid-connect/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'client_id' => 'admin-cli',
            'username' => env('KC_ADMIN_USER', 'super-admin'),
            'password' => env('KC_ADMIN_PASS'),
            'grant_type' => 'password',
        ]),
        CURLOPT_TIMEOUT => 10,
    ]);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);
    $token = $res['access_token'] ?? null;
    if (!$token) return response()->json(['error' => 'auth failed'], 500);

    $headers = ['Authorization: Bearer ' . $token];

    $kcGet = function($base, $path, $headers) {
        $ch = curl_init($base . $path);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => $headers, CURLOPT_TIMEOUT => 10]);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    };

    // User count
    $userCount = $kcGet($base, '/admin/realms/logstack/users/count', $headers) ?? 0;

    // Clients
    $clients = $kcGet($base, '/admin/realms/logstack/clients?max=50', $headers) ?? [];
    $clientCount = count($clients);

    // Sessions per client + user sessions dari semua client aktif
    $mainApps = ['logstack-app', 'odoo-erp', 'nextcloud', 'sogo-mail'];
    $sessionData = [];
    $totalSessions = 0;
    $allUserSessions = [];
    $seenSessionIds = [];

    foreach ($clients as $client) {
        if (in_array($client['clientId'], $mainApps)) {
            $sessions = $kcGet($base, '/admin/realms/logstack/clients/' . $client['id'] . '/session-count', $headers);
            $count = $sessions['count'] ?? 0;
            $totalSessions += $count;
            $sessionData[] = [
                'client' => $client['clientId'],
                'count'  => $count,
                'id'     => $client['id'],
            ];

            // Get user sessions dari semua client aktif (deduplicate by session id)
            if ($count > 0) {
                $userSessions = $kcGet($base, '/admin/realms/logstack/clients/' . $client['id'] . '/user-sessions?max=50', $headers) ?? [];
                if (!is_array($userSessions)) $userSessions = [];
                foreach ($userSessions as $s) {
                    if (!is_array($s) || !isset($s['id'])) continue;
                    if (in_array($s['id'], $seenSessionIds)) continue;
                    $seenSessionIds[] = $s['id'];
                    $clientNames = array_values($s['clients'] ?? []);
                    $allUserSessions[] = [
                        'username'    => $s['username'] ?? '-',
                        'ip'          => $s['ipAddress'] ?? '-',
                        'apps'        => implode(', ', $clientNames),
                        'start'       => isset($s['start']) ? \Carbon\Carbon::createFromTimestampMs($s['start'])->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-',
                        'last_access' => isset($s['lastAccess']) ? \Carbon\Carbon::createFromTimestampMs($s['lastAccess'])->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-',
                    ];
                }
            }
        }
    }

    usort($sessionData, fn($a, $b) => $b['count'] - $a['count']);

    return response()->json([
        'user_count'     => $userCount,
        'client_count'   => $clientCount,
        'total_sessions' => $totalSessions,
        'sessions'       => $sessionData,
        'user_sessions'  => $allUserSessions,
    ]);
})->name('api.keycloak.stats');
