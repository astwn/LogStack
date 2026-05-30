<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class OdooService
{
    protected string $url;
    protected string $db;
    protected string $username;
    protected string $password;
    protected int $timeout;
    protected int $connectTimeout;
    protected ?int $uid = null;

    public function __construct()
    {
        $this->url      = config('services.odoo.url');
        $this->db       = config('services.odoo.db', 'odoo');
        $this->username = config('services.odoo.username');
        $this->password = config('services.odoo.password');
        $this->timeout = (int) env('ODOO_RPC_TIMEOUT', app()->environment('local') ? 1 : 5);
        $this->connectTimeout = (int) env('ODOO_RPC_CONNECT_TIMEOUT', app()->environment('local') ? 1 : 3);
    }

    protected function jsonrpc(string $service, string $method, array $args): mixed
    {
        if (!$this->url || !$this->username || !$this->password) {
            return null;
        }

        $payload = json_encode([
            'jsonrpc' => '2.0',
            'method'  => 'call',
            'id'      => rand(1, 9999),
            'params'  => [
                'service' => $service,
                'method'  => $method,
                'args'    => $args,
            ],
        ]);

        $ch = curl_init($this->url . '/jsonrpc');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_CONNECTTIMEOUT  => $this->connectTimeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_NOSIGNAL       => true,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            Log::warning('Odoo JSON-RPC timeout/error', [
                'url' => $this->url,
                'service' => $service,
                'method' => $method,
                'error' => curl_error($ch),
            ]);
        }
        curl_close($ch);

        if (!$response) return null;

        $data = json_decode($response, true);
        return $data['result'] ?? null;
    }

    protected function authenticate(): ?int
    {
        if ($this->uid) return $this->uid;

        $uid = $this->jsonrpc('common', 'authenticate', [
            $this->db, $this->username, $this->password, []
        ]);

        $this->uid = is_int($uid) ? $uid : null;
        return $this->uid;
    }

    protected function execute(string $model, string $method, array $args = [], array $kwargs = []): mixed
    {
        $uid = $this->authenticate();
        if (!$uid) return null;

        return $this->jsonrpc('object', 'execute_kw', [
            $this->db, $uid, $this->password,
            $model, $method, $args, $kwargs
        ]);
    }

    /**
     * Archive (disable) user di Odoo berdasarkan email
     * Odoo tidak support hard delete user yang punya data, jadi kita archive
     */
    public function archiveUser(string $userEmail): array
    {
        try {
            $uid = $this->authenticate();
            if (!$uid) {
                return ['success' => false, 'message' => 'Gagal autentikasi ke Odoo.'];
            }

            // Cari user berdasarkan email
            $users = $this->execute('res.users', 'search_read',
                [[['login', '=', $userEmail]]],
                ['fields' => ['id', 'name', 'login', 'active'], 'limit' => 1]
            );

            if (empty($users)) {
                Log::info("Odoo: user {$userEmail} tidak ditemukan, skip archive.");
                return ['success' => true, 'message' => 'User tidak ditemukan di Odoo (skip).'];
            }

            $odooUserId = $users[0]['id'];

            // Archive user (set active = false)
            $result = $this->execute('res.users', 'write',
                [[$odooUserId], ['active' => false]]
            );

            if ($result) {
                Log::info("Odoo: user {$userEmail} (ID: {$odooUserId}) berhasil di-archive.");
                return ['success' => true, 'message' => "User berhasil di-archive di Odoo."];
            }

            Log::error("Odoo: gagal archive user {$userEmail}.");
            return ['success' => false, 'message' => 'Gagal archive user di Odoo.'];

        } catch (\Exception $e) {
            Log::error("Odoo: archiveUser exception untuk {$userEmail}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }

    public function getUserDashboard(string $userEmail): array
    {
        if (app()->environment('local') && env('ODOO_DASHBOARD_LOCAL_ENABLED', false) === false) {
            return $this->emptyDashboard();
        }

        // Tasks assigned to user
        $tasks = $this->execute('project.task', 'search_read',
            [[['user_ids.login', '=', $userEmail]]],
            ['fields' => ['name', 'stage_id', 'project_id', 'date_deadline', 'priority'], 'limit' => 10, 'order' => 'priority desc, date_deadline asc']
        ) ?? [];

        // Activities assigned to user
        $activities = $this->execute('mail.activity', 'search_read',
            [[['user_id.login', '=', $userEmail]]],
            ['fields' => ['summary', 'activity_type_id', 'date_deadline', 'res_model', 'res_name'], 'limit' => 10, 'order' => 'date_deadline asc']
        ) ?? [];

        // Meetings today & upcoming (convert WIB to UTC for Odoo query)
        $todayUtc = (new \DateTime('today', new \DateTimeZone('Asia/Jakarta')))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $nextWeekUtc = (new \DateTime('+7 days', new \DateTimeZone('Asia/Jakarta')))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $nextWeek = date('Y-m-d', strtotime('+7 days'));
        $meetings = $this->execute('calendar.event', 'search_read',
            [[['partner_ids.email', '=', $userEmail], ['start', '>=', $todayUtc], ['start', '<=', $nextWeekUtc]]],
            ['fields' => ['name', 'start', 'stop', 'location', 'description'], 'limit' => 5, 'order' => 'start asc']
        ) ?? [];

        // To-do (tasks without project)
        $todos = $this->execute('project.task', 'search_read',
            [[['user_ids.login', '=', $userEmail], ['project_id', '=', false]]],
            ['fields' => ['name', 'date_deadline', 'priority', 'stage_id'], 'limit' => 5]
        ) ?? [];

        // CRM leads
        $leads = $this->execute('crm.lead', 'search_read',
            [[['user_id.login', '=', $userEmail]]],
            ['fields' => ['name', 'stage_id', 'expected_revenue', 'date_deadline', 'priority'], 'limit' => 5]
        ) ?? [];

        // Summary counts
        $taskCount     = $this->execute('project.task', 'search_count', [[['user_ids.login', '=', $userEmail]]]) ?? 0;
        $activityCount = $this->execute('mail.activity', 'search_count', [[['user_id.login', '=', $userEmail]]]) ?? 0;
        $meetingCount  = $this->execute('calendar.event', 'search_count', [[['partner_ids.email', '=', $userEmail], ['start', '>=', $todayUtc], ['start', '<=', (new \DateTime('tomorrow', new \DateTimeZone('Asia/Jakarta')))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s')]]]) ?? 0;
        $leadCount     = $this->execute('crm.lead', 'search_count', [[['user_id.login', '=', $userEmail]]]) ?? 0;

        return [
            'summary' => [
                'tasks'      => $taskCount,
                'activities' => $activityCount,
                'meetings'   => $meetingCount,
                'leads'      => $leadCount,
            ],
            'tasks'      => $tasks,
            'activities' => $activities,
            'meetings'   => $meetings,
            'todos'      => $todos,
            'leads'      => $leads,
        ];
    }

    protected function emptyDashboard(): array
    {
        return [
            'summary' => [
                'tasks'      => 0,
                'activities' => 0,
                'meetings'   => 0,
                'leads'      => 0,
            ],
            'tasks'      => [],
            'activities' => [],
            'meetings'   => [],
            'todos'      => [],
            'leads'      => [],
        ];
    }
}
