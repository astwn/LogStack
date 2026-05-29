<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class DoveadmService
{
    protected string $host;
    protected int $port;
    protected string $password;
    protected string $domain;
    public function __construct()
    {
        $this->host     = env('DOVEADM_HOST', '172.18.4.107');
        $this->port     = (int) env('DOVEADM_PORT', 8888);
        $this->password = env('DOVEADM_PASSWORD', '');
        $this->domain   = env('DOVEADM_MAIL_DOMAIN', 'logstack.web.id');
    }
    private function apiUrl(): string
    {
        return "http://{$this->host}:{$this->port}/doveadm/v1";
    }
    private function fullEmail(string $username): string
    {
        if (str_contains($username, '@')) return $username;
        return "{$username}@{$this->domain}";
    }
    public function getMailStats(string $username): array
    {
        $email = $this->fullEmail($username);
        $stats = [
            'inbox_total'   => 0,
            'inbox_unread'  => 0,
            'junk_total'    => 0,
            'junk_unread'   => 0,
            'trash_total'   => 0,
            'sent_total'    => 0,
            'recent_emails' => [],
            'error'         => null,
        ];
        try {
            $response = Http::withBasicAuth('doveadm', $this->password)
                ->timeout(5)
                ->post($this->apiUrl(), [
                    ["mailboxStatus", ["user" => $email, "mailboxMask" => "INBOX", "field" => ["messages", "unseen", "recent"]], "inbox"],
                    ["mailboxStatus", ["user" => $email, "mailboxMask" => "Junk",  "field" => ["messages", "unseen"]], "junk"],
                    ["mailboxStatus", ["user" => $email, "mailboxMask" => "Trash", "field" => ["messages"]], "trash"],
                    ["mailboxStatus", ["user" => $email, "mailboxMask" => "Sent",  "field" => ["messages"]], "sent"],
                ]);
            if (!$response->successful()) {
                $stats['error'] = 'Doveadm API tidak dapat dijangkau.';
                return $stats;
            }
            foreach ($response->json() as $item) {
                $tag  = $item[2] ?? null;
                $data = $item[1][0] ?? [];
                if ($item[0] === 'error') continue;
                if ($tag === 'inbox') {
                    $stats['inbox_total']  = (int)($data['messages'] ?? 0);
                    $stats['inbox_unread'] = (int)($data['unseen'] ?? 0);
                } elseif ($tag === 'junk') {
                    $stats['junk_total']  = (int)($data['messages'] ?? 0);
                    $stats['junk_unread'] = (int)($data['unseen'] ?? 0);
                } elseif ($tag === 'trash') {
                    $stats['trash_total'] = (int)($data['messages'] ?? 0);
                } elseif ($tag === 'sent') {
                    $stats['sent_total'] = (int)($data['messages'] ?? 0);
                }
            }
            $stats['recent_emails'] = $this->getRecentEmails($email);
        } catch (\Exception $e) {
            Log::error("DoveadmService: " . $e->getMessage());
            $stats['error'] = 'Gagal terhubung ke mail server.';
        }
        return $stats;
    }
    private function getRecentEmails(string $email): array
    {
        try {
            $response = Http::withBasicAuth('doveadm', $this->password)
                ->timeout(5)
                ->post($this->apiUrl(), [
                    ["fetch", [
                        "user"  => $email,
                        "field" => ["uid", "flags", "imap.envelope"],
                        "query" => ["mailbox", "INBOX", "all"],
                    ], "fetch"],
                ]);
            if (!$response->successful()) return [];
            $emails = [];
            foreach ($response->json() as $item) {
                if (($item[0] ?? '') !== 'doveadmResponse') continue;
                $messages = array_reverse($item[1] ?? []);
                $messages = array_slice($messages, 0, 5);
                foreach ($messages as $msg) {
                    $envelope = $msg['imap.envelope'] ?? '';
                    $flags    = $msg['flags'] ?? '';
                    // Parse subject — field ke-2 dalam envelope string
                    // Format: "date" "subject" ((from)) ...
                    preg_match('/^"[^"]*"\s+"((?:[^"\\\\]|\\\\.)*)"/u', $envelope, $subjectMatch);
                    $subject = isset($subjectMatch[1]) ? $this->decodeMimeStr($subjectMatch[1]) : '(No Subject)';
                    // Parse date — field pertama
                    preg_match('/^"([^"]+)"/', $envelope, $dateMatch);
                    $date = isset($dateMatch[1]) ? date('d M Y, H:i', strtotime($dateMatch[1])) : '-';
                    // Parse from name — ambil dari grup pertama setelah subject
                    preg_match('/\(\("([^"]*)" NIL "([^"]*)" "([^"]*)"\)\)/', $envelope, $fromMatch);
                    $fromName  = $fromMatch[1] ?? '';
                    $fromEmail = isset($fromMatch[2], $fromMatch[3]) ? "{$fromMatch[2]}@{$fromMatch[3]}" : '';
                    $from      = $fromName ?: $fromEmail;
                    $emails[] = [
                        'subject' => $subject,
                        'from'    => $from,
                        'date'    => $date,
                        'seen'    => str_contains($flags, '\\Seen'),
                        'uid'     => $msg['uid'] ?? null,
                    ];
                }
            }
            return $emails;
        } catch (\Exception $e) {
            Log::warning("DoveadmService fetch: " . $e->getMessage());
            return [];
        }
    }
    private function decodeMimeStr(string $str): string
    {
        if (!str_contains($str, '=?')) return $str;
        $decoded = imap_mime_header_decode($str);
        $result  = '';
        foreach ($decoded as $part) {
            $charset = $part->charset ?? 'UTF-8';
            $text    = $part->text ?? '';
            if (strtolower($charset) !== 'default' && strtolower($charset) !== 'utf-8') {
                $text = mb_convert_encoding($text, 'UTF-8', $charset);
            }
            $result .= $text;
        }
        return $result ?: $str;
    }
}
