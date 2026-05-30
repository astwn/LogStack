<?php

namespace App\Http\Controllers;

use App\Models\AccessRequest;
use App\Services\FreeIPAService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AccessRequestController extends Controller
{
    /**
     * Store a new access request from welcome page
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'username'   => 'required|alpha_num|min:3|max:32|unique:access_requests,username',
            'email'      => 'required|email|max:100',
            'department' => 'nullable|string|max:100',
            'reason'     => 'nullable|string|max:500',
        ]);

        // Cek apakah email sudah pernah request
        $existing = AccessRequest::where('email', $request->email)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Email ini sudah memiliki request yang sedang diproses.'
            ], 422);
        }

        // Cek apakah username sudah ada di FreeIPA
        try {
            $freeIpa = app(FreeIPAService::class);
            $ipaCheck = $freeIpa->callRpc('user_show', ['uid' => $request->username]);
            if ($ipaCheck['success']) {
                return response()->json([
                    'success' => false,
                    'message' => "Username '{$request->username}' sudah digunakan. Silakan pilih username lain."
                ], 422);
            }
        } catch (\Exception $e) {
            Log::warning("AccessRequest: gagal cek username di FreeIPA: " . $e->getMessage());
        }

        AccessRequest::create([
            'name'       => strip_tags(trim($request->name)),
            'username'   => strtolower(trim($request->username)),
            'email'      => strtolower(trim($request->email)),
            'department' => strip_tags(trim($request->department ?? '')),
            'reason'     => strip_tags(trim($request->reason ?? '')),
            'status'     => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request akses berhasil dikirim. Admin akan meninjau permintaan Anda.'
        ]);
    }

    /**
     * List all access requests (admin)
     */
    public function index()
    {
        $requests = AccessRequest::latest()->paginate(20);
        $pendingCount = AccessRequest::pending()->count();

        return response()->json([
            'requests'     => $requests,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Approve access request - create user in FreeIPA
     */
    public function approve(Request $request, $id)
    {
        $accessRequest = AccessRequest::findOrFail($id);

        if (!$accessRequest->isPending()) {
            return redirect()->back()->with('error_user', 'Request ini sudah diproses sebelumnya.');
        }

        // Generate random password
        $password = $this->generatePassword();

        // Buat user di FreeIPA
        $freeIpa = app(FreeIPAService::class);

        // Split nama jadi first + last
        $nameParts = explode(' ', $accessRequest->name, 2);
        $firstName = $nameParts[0];
        $lastName  = $nameParts[1] ?? $nameParts[0];

        $createResult = $freeIpa->createUser(
            $accessRequest->username,
            $firstName,
            $lastName,
            $accessRequest->email,
            $password
        );

        if (!$createResult['success']) {
            Log::error("AccessRequest approve: gagal buat user {$accessRequest->username} di FreeIPA: " . $createResult['message']);
            return redirect()->back()->with('error_user', "Gagal membuat akun: " . $createResult['message']);
        }

        // Tambah ke group dash_user
        $freeIpa->addUserToGroup($accessRequest->username, 'dash_user');

        // Update status request
        $accessRequest->update([
            'status'       => 'approved',
            'processed_by' => Auth::user()->username ?? Auth::user()->email,
            'processed_at' => now(),
        ]);

        // Kirim email credential ke user
        $this->sendApprovalEmail($accessRequest, $password);

        Log::info("AccessRequest: user {$accessRequest->username} approved by " . Auth::user()->username);

        return redirect()->back()->with('success_user', "Akun {$accessRequest->username} berhasil dibuat dan credential dikirim ke {$accessRequest->email}.");
    }

    /**
     * Reject access request
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reject_reason' => 'required|string|max:500',
        ]);

        $accessRequest = AccessRequest::findOrFail($id);

        if (!$accessRequest->isPending()) {
            return redirect()->back()->with('error_user', 'Request ini sudah diproses sebelumnya.');
        }

        $accessRequest->update([
            'status'        => 'rejected',
            'processed_by'  => Auth::user()->username ?? Auth::user()->email,
            'processed_at'  => now(),
            'reject_reason' => strip_tags(trim($request->reject_reason)),
        ]);

        // Kirim email notifikasi reject
        $this->sendRejectionEmail($accessRequest, $request->reject_reason);

        Log::info("AccessRequest: request {$accessRequest->username} rejected by " . Auth::user()->username);

        return redirect()->back()->with('success_user', "Request dari {$accessRequest->name} telah ditolak.");
    }

    /**
     * Get pending count for badge
     */
    public function pendingCount()
    {
        return response()->json([
            'count' => AccessRequest::pending()->count()
        ]);
    }

    /**
     * Generate secure random password
     */
    private function generatePassword(): string
    {
        $upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower   = 'abcdefghjkmnpqrstuvwxyz';
        $numbers = '23456789';
        $special = '@#$!';

        $password = '';
        $password .= $upper[random_int(0, strlen($upper) - 1)];
        $password .= $upper[random_int(0, strlen($upper) - 1)];
        $password .= $lower[random_int(0, strlen($lower) - 1)];
        $password .= $lower[random_int(0, strlen($lower) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        $password .= $lower[random_int(0, strlen($lower) - 1)];

        return str_shuffle($password);
    }

    /**
     * Send approval email with credentials
     */
    private function sendApprovalEmail(AccessRequest $accessRequest, string $password): void
    {
        try {
            $mailDomain = env('SERVICE_MAIL_DOMAIN', 'logstack.web.id');
            $appName    = \App\Services\BrandingService::value('app_full_name', 'LogStack Central');
            $appUrl     = config('app.url');

            $subject = "Akses Disetujui - {$appName}";
            $body = "Halo {$accessRequest->name},\n\n"
                . "Permintaan akses Anda ke {$appName} telah DISETUJUI.\n\n"
                . "Berikut adalah credential akun Anda:\n"
                . "Username : {$accessRequest->username}\n"
                . "Password : {$password}\n"
                . "Email    : {$accessRequest->username}@{$mailDomain}\n\n"
                . "Silakan login di: {$appUrl}\n\n"
                . "PENTING: Segera ganti password Anda setelah login pertama.\n\n"
                . "Salam,\n{$appName}";

            \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($accessRequest, $subject) {
                $message->to($accessRequest->email, $accessRequest->name)
                        ->subject($subject);
            });

            Log::info("AccessRequest: approval email sent to {$accessRequest->email}");
        } catch (\Exception $e) {
            Log::error("AccessRequest: gagal kirim approval email: " . $e->getMessage());
        }
    }

    /**
     * Send rejection email
     */
    private function sendRejectionEmail(AccessRequest $accessRequest, string $reason): void
    {
        try {
            $mailDomain = env('SERVICE_MAIL_DOMAIN', 'logstack.web.id');
            $appName    = \App\Services\BrandingService::value('app_full_name', 'LogStack Central');

            $subject = "Request Akses Ditolak - {$appName}";
            $body = "Halo {$accessRequest->name},\n\n"
                . "Mohon maaf, permintaan akses Anda ke {$appName} tidak dapat disetujui.\n\n"
                . "Alasan: {$reason}\n\n"
                . "Jika Anda memiliki pertanyaan, silakan hubungi administrator.\n\n"
                . "Salam,\n{$appName}";

            \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($accessRequest, $subject) {
                $message->to($accessRequest->email, $accessRequest->name)
                        ->subject($subject);
            });

            Log::info("AccessRequest: rejection email sent to {$accessRequest->email}");
        } catch (\Exception $e) {
            Log::error("AccessRequest: gagal kirim rejection email: " . $e->getMessage());
        }
    }
}
