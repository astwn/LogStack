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
        $this->host     = config('services.doveadm.host', '172.18.4.107');
        $this->port     = (int) config('services.doveadm.port', 8888);
        $this->password = config('services.doveadm.password', '');
        $this->domain   = config('services.doveadm.domain', 'logstack.web.id');
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
    public function searchEmails(string $username, string $keyword, string $mailbox = 'INBOX'): array
    {
        $email = $this->fullEmail($username);
        $results = [];
        $seenUids = [];

        try {
            $response = Http::withBasicAuth('doveadm', $this->password)
                ->timeout(10)
                ->post($this->apiUrl(), [
                    ["fetch", [
                        "user"  => $email,
                        "field" => ["uid", "flags", "imap.envelope"],
                        "query" => ["mailbox", $mailbox, "header", "subject", $keyword],
                    ], "by_subject"],
                    ["fetch", [
                        "user"  => $email,
                        "field" => ["uid", "flags", "imap.envelope"],
                        "query" => ["mailbox", $mailbox, "header", "from", $keyword],
                    ], "by_from"],
                ]);

            if (!$response->successful()) return [];

            foreach ($response->json() as $item) {
                if (($item[0] ?? '') !== 'doveadmResponse') continue;
                $messages = array_reverse($item[1] ?? []);
                foreach ($messages as $msg) {
                    $uid = $msg['uid'] ?? null;
                    if (!$uid || in_array($uid, $seenUids)) continue;
                    $seenUids[] = $uid;

                    $envelope = $msg['imap.envelope'] ?? '';
                    $flags    = $msg['flags'] ?? '';
                    preg_match('/^"[^"]*"\s+"((?:[^"\\\\]|\\\\.)*)"/u', $envelope, $subjectMatch);
                    $subject = isset($subjectMatch[1]) ? $this->decodeMimeStr($subjectMatch[1]) : '(No Subject)';
                    preg_match('/^"([^"]+)"/', $envelope, $dateMatch);
                    $date = isset($dateMatch[1]) ? date('d M Y, H:i', strtotime($dateMatch[1])) : '-';
                    preg_match('/\(\("([^"]*)" NIL "([^"]*)" "([^"]*)"\)\)/', $envelope, $fromMatch);
                    $fromName  = $fromMatch[1] ?? '';
                    $fromEmail = isset($fromMatch[2], $fromMatch[3]) ? "{$fromMatch[2]}@{$fromMatch[3]}" : '';
                    $from      = $fromName ?: $fromEmail;
                    $results[] = [
                        'subject' => $subject,
                        'from'    => $from,
                        'date'    => $date,
                        'seen'    => str_contains($flags, '\\Seen'),
                        'uid'     => $uid,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("DoveadmService searchEmails: " . $e->getMessage());
        }

        return array_slice($results, 0, 20);
    }

    public function getMailBody(string $username, string $uid, string $mailbox = 'INBOX'): array
    {
        $email = $this->fullEmail($username);
        $result = [
            'uid'     => $uid,
            'subject' => '',
            'from'    => '',
            'date'    => '',
            'body'    => '',
            'error'   => null,
        ];

        try {
            $response = Http::withBasicAuth('doveadm', $this->password)
                ->timeout(10)
                ->post($this->apiUrl(), [
                    ["fetch", [
                        "user"  => $email,
                        "field" => ["uid", "hdr.subject", "hdr.from", "hdr.date", "hdr.content-type", "body"],
                        "query" => ["mailbox", $mailbox, "uid", $uid],
                    ], "msgbody"],
                ]);

            if (!$response->successful()) {
                $result['error'] = 'Gagal mengambil isi email.';
                return $result;
            }

            foreach ($response->json() as $item) {
                if (($item[0] ?? '') !== 'doveadmResponse') continue;
                $msg = $item[1][0] ?? null;
                if (!$msg) continue;

                $result['subject'] = $this->decodeMimeStr($msg['hdr.subject'] ?? '(No Subject)');
                $result['from']    = $msg['hdr.from'] ?? '';
                $result['date']    = $msg['hdr.date'] ?? '';
                $result['body']    = $this->extractPlainBody($msg['body'] ?? '', $msg['hdr.content-type'] ?? '');
            }
        } catch (\Exception $e) {
            Log::error("DoveadmService getMailBody: " . $e->getMessage());
            $result['error'] = 'Gagal terhubung ke mail server.';
        }

        return $result;
    }

    private function extractPlainBody(string $rawBody, string $contentType): string
    {
        // Kalau bukan multipart, return langsung
        if (!str_contains($contentType, 'multipart')) {
            return nl2br(htmlspecialchars(trim($rawBody), ENT_QUOTES, 'UTF-8'));
        }

        // Ambil boundary dari content-type
        preg_match('/boundary="?([^";]+)"?/i', $contentType, $boundaryMatch);
        $boundary = $boundaryMatch[1] ?? null;

        if (!$boundary) {
            return nl2br(htmlspecialchars(trim($rawBody), ENT_QUOTES, 'UTF-8'));
        }

        // Split per part
        $parts = preg_split('/--' . preg_quote($boundary, '/') . '(--)?\r?\n?/', $rawBody);

        $plainText = '';
        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part) || $part === '--') continue;

            // Pisah header dan body part
            $split = preg_split('/\r?\n\r?\n/', $part, 2);
            $partHeaders = $split[0] ?? '';
            $partBody    = $split[1] ?? '';

            // Cari text/plain
            if (str_contains(strtolower($partHeaders), 'text/plain')) {
                // Handle quoted-printable
                if (str_contains(strtolower($partHeaders), 'quoted-printable')) {
                    $partBody = quoted_printable_decode($partBody);
                }
                // Handle base64
                if (str_contains(strtolower($partHeaders), 'base64')) {
                    $partBody = base64_decode($partBody);
                }
                $plainText = trim($partBody);
                break;
            }
        }

        // Fallback: kalau tidak ada text/plain, ambil teks mentah
        if (empty($plainText)) {
            // Strip MIME headers dan boundary markers
            $plainText = preg_replace('/--[^\n]+\n/', '', $rawBody);
            $plainText = preg_replace('/^(Content-[^\n]+\n)+/m', '', $plainText);
            $plainText = trim($plainText);
        }

        return nl2br(htmlspecialchars($plainText, ENT_QUOTES, 'UTF-8'));
    }

    public function getMailQuota(string $username): array
    {
        $email = $this->fullEmail($username);
        $defaultQuotaBytes = 5 * 1024 * 1024 * 1024; // 5GB default

        $quota = [
            'used_bytes'  => 0,
            'limit_bytes' => $defaultQuotaBytes,
            'used_mb'     => 0,
            'limit_gb'    => 5,
            'percentage'  => 0,
            'used_human'  => '0 MB',
            'limit_human' => '5 GB',
            'color'       => 'emerald',
            'error'       => null,
        ];

        try {
            $response = Http::withBasicAuth('doveadm', $this->password)
                ->timeout(5)
                ->post($this->apiUrl(), [
                    ["quota-get", ["user" => $email], "quota"],
                ]);

            if (!$response->successful()) {
                $quota['error'] = 'Doveadm API tidak dapat dijangkau.';
                return $quota;
            }

            foreach ($response->json() as $item) {
                if (($item[0] ?? '') === 'error') continue;
                if (($item[2] ?? '') !== 'quota') continue;

                $rows = $item[1] ?? [];
                foreach ($rows as $row) {
                    // Doveadm quota-get returns rows with 'type' STORAGE or MESSAGE
                    if (($row['type'] ?? '') !== 'STORAGE') continue;

                    $usedKb  = (int)($row['value'] ?? 0);
                    $limitKb = (int)($row['limit'] ?? 0);

                    $usedBytes  = $usedKb * 1024;
                    // If no limit set by server, use 5GB default
                    $limitBytes = $limitKb > 0 ? $limitKb * 1024 : $defaultQuotaBytes;

                    $percentage = $limitBytes > 0 ? round(($usedBytes / $limitBytes) * 100, 1) : 0;
                    $percentage = min($percentage, 100);

                    $quota['used_bytes']  = $usedBytes;
                    $quota['limit_bytes'] = $limitBytes;
                    $quota['used_mb']     = round($usedBytes / 1024 / 1024, 1);
                    $quota['limit_gb']    = round($limitBytes / 1024 / 1024 / 1024, 1);
                    $quota['percentage']  = $percentage;
                    $quota['used_human']  = $usedBytes >= 1024 * 1024 * 1024
                        ? round($usedBytes / 1024 / 1024 / 1024, 2) . ' GB'
                        : round($usedBytes / 1024 / 1024, 1) . ' MB';
                    $quota['limit_human'] = round($limitBytes / 1024 / 1024 / 1024, 1) . ' GB';
                    $quota['color']       = $percentage >= 90 ? 'red' : ($percentage >= 70 ? 'yellow' : 'emerald');
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::error("DoveadmService getMailQuota: " . $e->getMessage());
            $quota['error'] = 'Gagal mengambil data quota mail.';
        }

        return $quota;
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
