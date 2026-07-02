<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Webklex\PHPIMAP\ClientManager;
use App\Models\MailboxAccount;
use App\Models\MailboxSentLog;

class MailboxController extends Controller
{
    private function getAllowedAccounts(): array
    {
        $user = auth()->user();
        $accounts = [];

        if ($user->role === 'admin') {
            $accounts['info'] = [
                'label'      => 'info@gayatrient.com',
                'address'    => env('IMAP_INFO_USERNAME'),
                'host'       => env('IMAP_INFO_HOST', 'imap.hostinger.com'),
                'port'       => (int) env('IMAP_INFO_PORT', 993),
                'encryption' => env('IMAP_INFO_ENCRYPTION', 'ssl'),
                'username'   => env('IMAP_INFO_USERNAME'),
                'password'   => env('IMAP_INFO_PASSWORD'),
            ];
        }

        $accounts['office'] = [
            'label'      => 'office@gayatrient.com',
            'address'    => env('IMAP_OFFICE_USERNAME'),
            'host'       => env('IMAP_OFFICE_HOST', 'imap.hostinger.com'),
            'port'       => (int) env('IMAP_OFFICE_PORT', 993),
            'encryption' => env('IMAP_OFFICE_ENCRYPTION', 'ssl'),
            'username'   => env('IMAP_OFFICE_USERNAME'),
            'password'   => env('IMAP_OFFICE_PASSWORD'),
        ];

        // Load custom user accounts from DB
        try {
            $customDbAccounts = MailboxAccount::where('user_id', $user->id)->get();
            foreach ($customDbAccounts as $dbAcc) {
                $key = 'custom_' . $dbAcc->id;
                $accounts[$key] = [
                    'id'              => $dbAcc->id,
                    'label'           => $dbAcc->label . ' (' . $dbAcc->address . ')',
                    'address'         => $dbAcc->address,
                    'host'            => $dbAcc->host,
                    'port'            => (int) $dbAcc->port,
                    'encryption'      => $dbAcc->encryption,
                    'username'        => $dbAcc->username,
                    'password'        => $dbAcc->getDecryptedPassword(),
                    'is_custom'       => true,
                    'smtp_host'       => $dbAcc->smtp_host,
                    'smtp_port'       => (int) $dbAcc->smtp_port,
                    'smtp_encryption' => $dbAcc->smtp_encryption,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load custom mailbox accounts: ' . $e->getMessage());
        }

        return $accounts;
    }

    private function makeClient(array $account)
    {
        $cm = new ClientManager();
        $client = $cm->make([
            'host'          => $account['host'],
            'port'          => $account['port'],
            'encryption'    => $account['encryption'],
            'validate_cert' => false,
            'username'      => $account['username'],
            'password'      => $account['password'],
            'protocol'      => 'imap',
        ]);
        $client->connect();
        return $client;
    }

    private function findFolderByCandidates($client, $folderName)
    {
        $nameLower = strtolower($folderName);
        
        // Map folders to a robust set of dynamic candidates (handles INBOX prefixes, Sent vs Sent Items, Junk vs Spam vs Trash)
        $candidates = [];
        if ($nameLower === 'inbox') {
            $candidates = ['INBOX', 'inbox'];
        } elseif ($nameLower === 'sent') {
            $candidates = ['Sent', 'sent', 'INBOX.Sent', 'INBOX/Sent', 'Sent Messages', 'Sent Items', 'INBOX.Sent Items', 'INBOX.Sent Messages'];
        } elseif ($nameLower === 'drafts') {
            $candidates = ['Drafts', 'drafts', 'INBOX.Drafts', 'INBOX/Drafts', 'Drafts Messages', 'Draft Messages'];
        } elseif ($nameLower === 'trash') {
            $candidates = ['Trash', 'trash', 'INBOX.Trash', 'INBOX/Trash', 'Deleted', 'Deleted Messages', 'Deleted Items', 'Bin', 'INBOX.Bin', 'Trash Messages'];
        } elseif ($nameLower === 'spam') {
            $candidates = ['Spam', 'spam', 'INBOX.Spam', 'INBOX/Spam', 'Junk', 'Junk Messages', 'INBOX.Junk', 'Spam Messages'];
        } else {
            $candidates = [$folderName, $nameLower, 'INBOX.' . $folderName, 'INBOX/' . $folderName];
        }

        // 1. Try exact match queries first to avoid fetching all folders (massive network speedup)
        foreach ($candidates as $candidate) {
            try {
                $folder = $client->getFolderByPath($candidate);
                if ($folder) {
                    return $folder;
                }
            } catch (\Exception $e) {
                // Ignore and try next candidate
            }
        }

        // Try direct getFolder as alternate exact match lookup
        foreach ($candidates as $candidate) {
            try {
                $folder = $client->getFolder($candidate);
                if ($folder) {
                    return $folder;
                }
            } catch (\Exception $e) {
                // Ignore and try next candidate
            }
        }

        // 2. Fallback: Flat list lookup (original logic, run only if exact-matches fail)
        try {
            $folders = $client->getFolders(false);
            $lowerCandidates = array_map('strtolower', $candidates);
            foreach ($folders as $f) {
                $fNameLower = strtolower($f->name);
                if (in_array($fNameLower, $lowerCandidates)) {
                    return $f;
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Fallback folder listing failed: " . $e->getMessage());
        }
        
        // Final literal fallback
        try {
            return $client->getFolder($folderName);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function findFolderByKeywords($client, array $keywords)
    {
        try {
            $folders = $client->getFolders(false); // Flat list
            foreach ($folders as $f) {
                $fNameLower = strtolower($f->name);
                foreach ($keywords as $kw) {
                    if (str_contains($fNameLower, $kw)) {
                        return $f;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Error listing folders for keywords: " . $e->getMessage());
        }
        return null;
    }

    /** Main mailbox page (shell only — content loaded via AJAX) */
    public function index()
    {
        $accounts = $this->getAllowedAccounts();
        return view('mailbox.index', compact('accounts'));
    }

    /** AJAX: Fetch inbox list with real-time IMAP quota storage statistics */
    public function apiInbox(Request $request)
    {
        $accounts  = $this->getAllowedAccounts();
        $activeKey = $request->get('account', array_key_first($accounts));

        if (!array_key_exists($activeKey, $accounts)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $account = $accounts[$activeKey];
        $folderName = $request->get('folder', 'INBOX');
        $isStarredFolder = (strtolower($folderName) === 'starred');
        
        $page = (int) $request->get('page', 1);
        $limit = 30;
        $offset = ($page - 1) * $limit;
        
        $shouldSync = $request->get('sync', '0') === '1';

        // 1. Fetch from Cache first
        $query = \App\Models\MailboxEmail::where('account_key', $activeKey);
        if ($isStarredFolder) {
            $query->where('starred', true);
        } else {
            $query->where('folder_name', $folderName);
        }
        
        $cachedEmailsCount = $query->count();
        $cachedEmails = $query->orderBy('imap_timestamp', 'desc')
                              ->offset($offset)
                              ->limit($limit)
                              ->get();

        // If not forcing sync, and we have cached emails, return instantly!
        if (!$shouldSync && $cachedEmailsCount > 0) {
            $emails = [];
            foreach ($cachedEmails as $msg) {
                $emails[] = [
                    'uid'     => (string)$msg->uid,
                    'subject' => (string)($msg->subject ?? '(No Subject)'),
                    'from'    => $msg->from_name ?: $msg->from_raw ?: 'Unknown',
                    'date'    => $msg->date_string ?? '',
                    'seen'    => (bool)$msg->seen,
                    'starred' => (bool)$msg->starred,
                ];
            }
            return response()->json([
                'emails'  => $emails,
                'folder'  => $folderName,
                'storage' => null, // Will be loaded by background sync
                'cached'  => true
            ]);
        }

        // 2. Perform Sync (if forcing sync, or database cache is empty)
        try {
            \Log::info("Mailbox Cache Sync: Connecting to " . $account['username'] . " @ " . $account['host']);
            $client = $this->makeClient($account);
            
            // Get Quota root if supported
            $storage = null;
            try {
                $quota = $client->getQuota();
                if ($quota && isset($quota['usage']) && isset($quota['limit'])) {
                    $usageMb = round($quota['usage'] / 1024, 2);
                    $limitMb = round($quota['limit'] / 1024, 2);
                    $percent = $limitMb > 0 ? round(($usageMb / $limitMb) * 100, 1) : 0;
                    
                    $storage = [
                        'usage'   => $usageMb . ' MB',
                        'limit'   => $limitMb >= 1024 ? round($limitMb / 1024, 2) . ' GB' : $limitMb . ' MB',
                        'percent' => $percent,
                    ];
                }
            } catch (\Exception $qe) {
                \Log::warning("Quota query skipped: " . $qe->getMessage());
            }

            $targetFolderQuery = $isStarredFolder ? 'INBOX' : $folderName;
            $folder = $this->findFolderByCandidates($client, $targetFolderQuery);

            if (!$folder) {
                $client->disconnect();
                return response()->json(['emails' => [], 'warning' => 'Folder not found', 'storage' => $storage]);
            }

            // Sync latest 50 messages to local database for fast responsive navigation
            $syncLimit = 50;
            if ($isStarredFolder) {
                $messages = $folder->query()->flagged()->limit($syncLimit, 1)->get();
            } else {
                $messages = $folder->query()->all()->limit($syncLimit, 1)->get();
            }

            $fetchedUids = [];
            foreach ($messages as $msg) {
                $uid = (string)$msg->getUid();
                $fetchedUids[] = $uid;

                $from = $msg->getFrom();
                $fromPersonal = $from ? $from->first()->personal : null;
                $fromMail = $from ? $from->first()->mail : null;

                // Seen check
                $isSeen = false;
                foreach ($msg->getFlags() as $flag) {
                    $fLower = strtolower($flag->name ?? $flag);
                    if ($fLower === 'seen' || $fLower === '\\seen') {
                        $isSeen = true;
                        break;
                    }
                }

                // Starred check
                $isStarred = false;
                foreach ($msg->getFlags() as $flag) {
                    $fLower = strtolower($flag->name ?? $flag);
                    if ($fLower === 'flagged' || $fLower === '\\flagged') {
                        $isStarred = true;
                        break;
                    }
                }

                $subject = (string)($msg->getSubject() ?? '(No Subject)');
                $replyTo = $msg->getReplyTo() ? $msg->getReplyTo()->first()->mail : null;
                $dateString = $msg->getDate()?->toDate()?->format('d M, h:i A') ?? '';
                $imapTimestamp = $msg->getDate()?->toDate()?->timestamp ?? time();

                // Check if email already exists in local cache
                $alreadyExists = \App\Models\MailboxEmail::where('account_key', $activeKey)
                    ->where('folder_name', $folderName)
                    ->where('uid', $uid)
                    ->exists();

                // Upsert to local cache
                \App\Models\MailboxEmail::updateOrCreate(
                    [
                        'account_key' => $activeKey,
                        'folder_name' => $folderName,
                        'uid'         => $uid,
                    ],
                    [
                        'subject'        => $subject,
                        'from_name'      => $fromPersonal,
                        'from_raw'       => $fromMail,
                        'reply_to'       => $replyTo,
                        'date_string'    => $dateString,
                        'imap_timestamp' => $imapTimestamp,
                        'seen'           => $isSeen,
                        'starred'        => $isStarred,
                    ]
                );

                // If this is a new email in the INBOX folder and it hasn't been seen yet, send Web Push!
                if (!$alreadyExists && strtolower($folderName) === 'inbox' && !$isSeen) {
                    try {
                        $recipients = \App\Models\User::whereIn('role', ['admin', 'staff'])->get();
                        $fromLabel = $fromPersonal ? "{$fromPersonal} <{$fromMail}>" : $fromMail;
                        \App\Services\PushNotificationService::sendToUsers(
                            $recipients,
                            "New Email: " . $subject,
                            "From: " . $fromLabel,
                            route('mailbox.index')
                        );
                    } catch (\Exception $pushEx) {
                        \Log::error('New mailbox email push notification error: ' . $pushEx->getMessage());
                    }
                }
            }

            // Pruning step: Delete local emails that are no longer on the server
            if (!empty($fetchedUids)) {
                $minFetchedUid = min(array_map('intval', $fetchedUids));
                \App\Models\MailboxEmail::where('account_key', $activeKey)
                    ->where('folder_name', $folderName)
                    ->where('uid', '>=', $minFetchedUid)
                    ->whereNotIn('uid', $fetchedUids)
                    ->delete();
            }

            $client->disconnect();

            // Refetch fresh list from cache to ensure correct pagination
            $freshCachedEmails = \App\Models\MailboxEmail::where('account_key', $activeKey);
            if ($isStarredFolder) {
                $freshCachedEmails->where('starred', true);
            } else {
                $freshCachedEmails->where('folder_name', $folderName);
            }

            $freshEmails = $freshCachedEmails->orderBy('imap_timestamp', 'desc')
                                             ->offset($offset)
                                             ->limit($limit)
                                             ->get()
                                             ->map(function ($msg) {
                                                 return [
                                                     'uid'     => (string)$msg->uid,
                                                     'subject' => (string)($msg->subject ?? '(No Subject)'),
                                                     'from'    => $msg->from_name ?: $msg->from_raw ?: 'Unknown',
                                                     'date'    => $msg->date_string ?? '',
                                                     'seen'    => (bool)$msg->seen,
                                                     'starred' => (bool)$msg->starred,
                                                 ];
                                             });

            return response()->json([
                'emails'  => $freshEmails,
                'folder'  => $folderName,
                'storage' => $storage,
                'cached'  => false
            ]);

        } catch (\Exception $e) {
            \Log::error('Mailbox apiInbox sync error: ' . $e->getMessage());
            
            // Fallback: If sync fails but we have cached emails, return cache!
            if ($cachedEmailsCount > 0) {
                $emails = [];
                foreach ($cachedEmails as $msg) {
                    $emails[] = [
                        'uid'     => (string)$msg->uid,
                        'subject' => (string)($msg->subject ?? '(No Subject)'),
                        'from'    => $msg->from_name ?: $msg->from_raw ?: 'Unknown',
                        'date'    => $msg->date_string ?? '',
                        'seen'    => (bool)$msg->seen,
                        'starred' => (bool)$msg->starred,
                    ];
                }
                return response()->json([
                    'emails'  => $emails,
                    'folder'  => $folderName,
                    'storage' => null,
                    'cached'  => true,
                    'warning' => 'Connection to server failed. Displaying offline cached emails.'
                ]);
            }
            
            return response()->json(['error' => 'Unable to connect to the mail server and no cached emails found.'], 500);
        }
    }

    /** AJAX: Fetch single email */
    public function apiMessage(Request $request, $uid)
    {
        // Support virtual outbox / local message viewing instantly
        if (str_starts_with($uid, 'local_')) {
            $logId = str_replace('local_', '', $uid);
            $log = MailboxSentLog::findOrFail($logId);
            return response()->json([
                'uid'      => $uid,
                'subject'  => $log->subject,
                'from'     => 'Local Outbox Log',
                'from_raw' => $log->account_key,
                'reply_to' => '',
                'date'     => $log->created_at->format('D, d M Y H:i'),
                'body'     => $log->body,
            ]);
        }

        $accounts  = $this->getAllowedAccounts();
        $activeKey = $request->get('account', array_key_first($accounts));
        $folderName = $request->get('folder', 'INBOX');
        if (strtolower($folderName) === 'starred') {
            $folderName = 'INBOX';
        }

        if (!array_key_exists($activeKey, $accounts)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $account = $accounts[$activeKey];

        // 1. Check if we have the full message body cached
        $cachedEmail = \App\Models\MailboxEmail::where('account_key', $activeKey)
            ->where('folder_name', $folderName)
            ->where('uid', $uid)
            ->first();

        if ($cachedEmail && !empty($cachedEmail->body)) {
            // Mark as seen locally
            if (!$cachedEmail->seen) {
                $cachedEmail->update(['seen' => true]);
            }

            return response()->json([
                'uid'      => $cachedEmail->uid,
                'subject'  => (string)($cachedEmail->subject ?? '(No Subject)'),
                'from'     => $cachedEmail->from_name ?: $cachedEmail->from_raw ?: 'Unknown',
                'from_raw' => $cachedEmail->from_raw ?? '',
                'reply_to' => $cachedEmail->reply_to ?? '',
                'date'     => $cachedEmail->date_string ?? '',
                'body'     => $cachedEmail->body,
                'starred'  => (bool)$cachedEmail->starred,
                'seen'     => true,
            ]);
        }

        // 2. Fallback to live IMAP fetch if body is not cached yet
        try {
            $client  = $this->makeClient($account);
            $folder  = $this->findFolderByCandidates($client, $folderName);
            $message = $folder ? $folder->query()->getMessageByUid($uid) : null;

            if (!$message) {
                $client->disconnect();
                return response()->json(['error' => 'Email not found'], 404);
            }

            // Mark seen on server
            try {
                $message->setFlag('Seen');
            } catch (\Exception $fe) {}

            $from    = $message->getFrom();
            $replyTo = $message->getReplyTo();

            $htmlBody = $message->getHTMLBody() ?? '';
            $safeBody = strip_tags($htmlBody, '<p><br><b><i><strong><em><u><ul><ol><li><a><img><h1><h2><h3><h4><h5><h6><blockquote><pre><span><div><table><thead><tbody><tr><td><th><style><head><title>');

            // Starred check
            $isStarred = false;
            foreach ($message->getFlags() as $flag) {
                $fLower = strtolower($flag->name ?? $flag);
                if ($fLower === 'flagged' || $fLower === '\\flagged') {
                    $isStarred = true;
                    break;
                }
            }

            $fromStr = $from ? ($from->first()->personal ?: $from->first()->mail) : 'Unknown';
            $fromRaw = $from ? $from->first()->mail : '';
            $replyToStr = $replyTo ? $replyTo->first()->mail : ($from ? $from->first()->mail : '');
            $dateStr = $message->getDate()?->toDate()?->format('D, d M Y H:i') ?? '';
            $finalBody = $htmlBody ? $safeBody : nl2br(e($message->getTextBody() ?? ''));

            // Cache single email content
            if ($cachedEmail) {
                $cachedEmail->update([
                    'seen' => true,
                    'body' => $finalBody,
                ]);
            } else {
                \App\Models\MailboxEmail::create([
                    'account_key'    => $activeKey,
                    'folder_name'    => $folderName,
                    'uid'            => $uid,
                    'subject'        => (string)($message->getSubject() ?? '(No Subject)'),
                    'from_name'      => $from ? $from->first()->personal : null,
                    'from_raw'       => $fromRaw,
                    'reply_to'       => $replyToStr,
                    'date_string'    => $dateStr,
                    'imap_timestamp' => $message->getDate()?->toDate()?->timestamp ?? time(),
                    'seen'           => true,
                    'starred'        => $isStarred,
                    'body'           => $finalBody,
                ]);
            }

            $data = [
                'uid'      => $uid,
                'subject'  => (string)($message->getSubject() ?? '(No Subject)'),
                'from'     => $fromStr,
                'from_raw' => $fromRaw,
                'reply_to' => $replyToStr,
                'date'     => $dateStr,
                'body'     => $finalBody,
                'starred'  => $isStarred,
                'seen'     => true,
            ];

            $client->disconnect();
            return response()->json($data);

        } catch (\Exception $e) {
            \Log::error('Mailbox apiMessage error: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch email content.'], 500);
        }
    }

    /** Send a reply via SMTP */
    public function reply(Request $request)
    {
        $request->validate([
            'to'            => 'required|email',
            'subject'       => 'required|string|max:255',
            'body'          => 'required|string',
            'account'       => 'required|string',
            'cc'            => 'nullable|string',
            'bcc'           => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240', // Max 10MB
        ]);

        $accounts  = $this->getAllowedAccounts();
        $activeKey = $request->input('account');

        if (!array_key_exists($activeKey, $accounts)) {
            return response()->json(['error' => 'Invalid account'], 403);
        }

        $account = $accounts[$activeKey];

        // Map dynamic SMTP configuration based on core or custom providers
        $smtpHost = isset($account['is_custom']) ? $account['smtp_host'] : 'smtp.hostinger.com';
        $smtpPort = isset($account['is_custom']) ? $account['smtp_port'] : 465;
        $smtpEnc  = isset($account['is_custom']) ? $account['smtp_encryption'] : 'ssl';

        try {
            config([
                'mail.mailers.smtp.host'       => $smtpHost,
                'mail.mailers.smtp.port'       => $smtpPort,
                'mail.mailers.smtp.encryption' => $smtpEnc,
                'mail.mailers.smtp.username'   => $account['username'],
                'mail.mailers.smtp.password'   => $account['password'],
                'mail.from.address'            => $account['address'],
                'mail.from.name'               => 'Gayatri Enterprises',
            ]);

            Mail::purge('smtp');

            $sentMail = Mail::html($request->body, function ($msg) use ($request, $account) {
                $msg->to($request->to)
                    ->subject($request->subject)
                    ->from($account['address'], 'Gayatri Enterprises');
                
                if ($request->filled('cc')) {
                    $ccEmails = array_map('trim', explode(',', $request->cc));
                    $msg->cc($ccEmails);
                }
                
                if ($request->filled('bcc')) {
                    $bccEmails = array_map('trim', explode(',', $request->bcc));
                    $msg->bcc($bccEmails);
                }

                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $msg->attach($file->getRealPath(), [
                            'as'   => $file->getClientOriginalName(),
                            'mime' => $file->getClientMimeType(),
                        ]);
                    }
                }
            });

            // Sync sent mail dynamically into IMAP "Sent" folder of the active provider
            if ($sentMail) {
                $rawMessage = $sentMail->getSymfonySentMessage()->toString();
                try {
                    $client = $this->makeClient($account);
                    $folders = $client->getFolders(false);
                    $sentFolder = null;
                    $sentFolderCandidates = ['Sent', 'Sent Messages', 'Sent Items', 'INBOX.Sent', 'INBOX/Sent'];
                    foreach ($folders as $f) {
                        if (in_array(strtolower($f->name), array_map('strtolower', $sentFolderCandidates))) {
                            $sentFolder = $f;
                            break;
                        }
                    }
                    if (!$sentFolder) {
                        $sentFolder = $client->getFolder('Sent');
                    }
                    if ($sentFolder) {
                        $sentFolder->appendMessage($rawMessage);
                    }
                    $client->disconnect();
                } catch (\Exception $ie) {
                    \Log::warning("Failed to upload copy to IMAP Sent: " . $ie->getMessage());
                }
            }

            // Create sent logger entry for virtual outbox history audit
            MailboxSentLog::create([
                'user_id'     => auth()->id(),
                'account_key' => $activeKey,
                'to'          => $request->to,
                'subject'     => $request->subject,
                'body'        => $request->body,
                'status'      => 'sent',
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            \Log::error('Mailbox reply error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send reply.'], 500);
        }
    }

    /** Send a new email via SMTP */
    public function send(Request $request)
    {
        $request->validate([
            'to'            => 'required|email',
            'subject'       => 'required|string|max:255',
            'body'          => 'required|string',
            'account'       => 'required|string',
            'cc'            => 'nullable|string',
            'bcc'           => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240', // Max 10MB
        ]);

        $accounts  = $this->getAllowedAccounts();
        $activeKey = $request->input('account');

        if (!array_key_exists($activeKey, $accounts)) {
            return response()->json(['error' => 'Invalid account'], 403);
        }

        $account = $accounts[$activeKey];

        // Map dynamic SMTP configurations dynamically
        $smtpHost = isset($account['is_custom']) ? $account['smtp_host'] : 'smtp.hostinger.com';
        $smtpPort = isset($account['is_custom']) ? $account['smtp_port'] : 465;
        $smtpEnc  = isset($account['is_custom']) ? $account['smtp_encryption'] : 'ssl';

        try {
            config([
                'mail.mailers.smtp.host'       => $smtpHost,
                'mail.mailers.smtp.port'       => $smtpPort,
                'mail.mailers.smtp.encryption' => $smtpEnc,
                'mail.mailers.smtp.username'   => $account['username'],
                'mail.mailers.smtp.password'   => $account['password'],
                'mail.from.address'            => $account['address'],
                'mail.from.name'               => 'Gayatri Enterprises',
            ]);

            Mail::purge('smtp');

            $sentMail = Mail::html($request->body, function ($msg) use ($request, $account) {
                $msg->to($request->to)
                    ->subject($request->subject)
                    ->from($account['address'], 'Gayatri Enterprises');
                
                if ($request->filled('cc')) {
                    $ccEmails = array_map('trim', explode(',', $request->cc));
                    $msg->cc($ccEmails);
                }
                
                if ($request->filled('bcc')) {
                    $bccEmails = array_map('trim', explode(',', $request->bcc));
                    $msg->bcc($bccEmails);
                }

                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $msg->attach($file->getRealPath(), [
                            'as'   => $file->getClientOriginalName(),
                            'mime' => $file->getClientMimeType(),
                        ]);
                    }
                }
            });

            // Sync sent mail dynamically into IMAP "Sent" folder of the active provider
            if ($sentMail) {
                $rawMessage = $sentMail->getSymfonySentMessage()->toString();
                try {
                    $client = $this->makeClient($account);
                    $folders = $client->getFolders(false);
                    $sentFolder = null;
                    $sentFolderCandidates = ['Sent', 'Sent Messages', 'Sent Items', 'INBOX.Sent', 'INBOX/Sent'];
                    foreach ($folders as $f) {
                        if (in_array(strtolower($f->name), array_map('strtolower', $sentFolderCandidates))) {
                            $sentFolder = $f;
                            break;
                        }
                    }
                    if (!$sentFolder) {
                        $sentFolder = $client->getFolder('Sent');
                    }
                    if ($sentFolder) {
                        $sentFolder->appendMessage($rawMessage);
                    }
                    $client->disconnect();
                } catch (\Exception $ie) {
                    \Log::warning("Failed to upload copy to IMAP Sent: " . $ie->getMessage());
                }
            }

            // Create sent logger entry for virtual outbox history audit
            MailboxSentLog::create([
                'user_id'     => auth()->id(),
                'account_key' => $activeKey,
                'to'          => $request->to,
                'subject'     => $request->subject,
                'body'        => $request->body,
                'status'      => 'sent',
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            \Log::error('Mailbox send error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send email.'], 500);
        }
    }

    /** Delete an email */
    public function delete(Request $request, $uid)
    {
        // Support deleting locally-saved outbox logs
        if (str_starts_with($uid, 'local_')) {
            $logId = str_replace('local_', '', $uid);
            $log = MailboxSentLog::where('user_id', auth()->id())->findOrFail($logId);
            $log->delete();
            return response()->json(['success' => true]);
        }

        $accounts  = $this->getAllowedAccounts();
        $activeKey = $request->get('account', array_key_first($accounts));
        $folderName = $request->get('folder', 'INBOX');
        if (strtolower($folderName) === 'starred') {
            $folderName = 'INBOX';
        }

        if (!array_key_exists($activeKey, $accounts)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $account = $accounts[$activeKey];

        // 1. Delete from local database cache instantly!
        \App\Models\MailboxEmail::where('account_key', $activeKey)
            ->where('folder_name', $folderName)
            ->where('uid', $uid)
            ->delete();

        // 2. Perform IMAP deletion
        try {
            $client  = $this->makeClient($account);
            $folder  = $this->findFolderByCandidates($client, $folderName);
            $message = $folder ? $folder->query()->getMessageByUid($uid) : null;
            if ($message) {
                // Determine if we are already in the Trash folder
                $fNameLower = strtolower($folderName);
                $isTrashFolder = false;
                foreach (['trash', 'deleted', 'bin'] as $kw) {
                    if (str_contains($fNameLower, $kw)) {
                        $isTrashFolder = true;
                        break;
                    }
                }

                if ($isTrashFolder) {
                    // Already in Trash - perform permanent deletion
                    $message->delete(true);
                } else {
                    // Move to Trash folder if found
                    $trashFolder = $this->findFolderByKeywords($client, ['trash', 'deleted', 'bin']);
                    if ($trashFolder && $trashFolder->path !== $folder->path) {
                        $message->move($trashFolder->path);
                        // Update locally in cache to show it moved to trash if we sync trash later
                        try {
                            \App\Models\MailboxEmail::where('account_key', $activeKey)
                                ->where('uid', $uid)
                                ->update(['folder_name' => 'Trash']);
                        } catch (\Exception $ue) {}
                    } else {
                        // No trash folder, or same path - permanent delete fallback
                        $message->delete(true);
                    }
                }

                \App\Models\ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'Deleted/Moved Email',
                    'details' => 'Account: ' . $account['address'] . ', Subject: ' . ($message->getSubject() ?? '(No Subject)'),
                ]);
            }
            $client->disconnect();
        } catch (\Exception $e) {
            \Log::error('Mailbox delete error: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    /** Toggle Star/Flag on an email */
    public function toggleStar(Request $request, $uid)
    {
        if (str_starts_with($uid, 'local_')) {
            return response()->json(['success' => true, 'starred' => false]);
        }

        $accounts  = $this->getAllowedAccounts();
        $activeKey = $request->get('account', array_key_first($accounts));
        $folderName = $request->get('folder', 'INBOX');
        if (strtolower($folderName) === 'starred') {
            $folderName = 'INBOX';
        }

        if (!array_key_exists($activeKey, $accounts)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $account = $accounts[$activeKey];

        // 1. Toggle locally in cache first for instant feedback!
        $cached = \App\Models\MailboxEmail::where('account_key', $activeKey)
            ->where('folder_name', $folderName)
            ->where('uid', $uid)
            ->first();

        $newStarred = true;
        if ($cached) {
            $newStarred = !$cached->starred;
            $cached->update(['starred' => $newStarred]);
        }

        // 2. Perform IMAP toggle
        try {
            $client  = $this->makeClient($account);
            $folder  = $this->findFolderByCandidates($client, $folderName);
            $message = $folder ? $folder->query()->getMessageByUid($uid) : null;
            
            if ($message) {
                if ($newStarred) {
                    $message->setFlag('Flagged');
                } else {
                    $message->unsetFlag('Flagged');
                }
            }
            $client->disconnect();
        } catch (\Exception $e) {
            \Log::error('Mailbox toggleStar error: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'starred' => $newStarred]);
    }

    /** Mark an email as Spam */
    public function markAsSpam(Request $request, $uid)
    {
        if (str_starts_with($uid, 'local_')) {
            return response()->json(['success' => true]);
        }

        $accounts  = $this->getAllowedAccounts();
        $activeKey = $request->get('account', array_key_first($accounts));
        $folderName = $request->get('folder', 'INBOX');
        if (strtolower($folderName) === 'starred') {
            $folderName = 'INBOX';
        }

        if (!array_key_exists($activeKey, $accounts)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $account = $accounts[$activeKey];

        // 1. Delete from local cache instantly to remove from list
        \App\Models\MailboxEmail::where('account_key', $activeKey)
            ->where('folder_name', $folderName)
            ->where('uid', $uid)
            ->delete();

        // 2. Perform IMAP Spam move
        try {
            $client  = $this->makeClient($account);
            $folder  = $this->findFolderByCandidates($client, $folderName);
            $message = $folder ? $folder->query()->getMessageByUid($uid) : null;
            if ($message) {
                $spamFolder = $this->findFolderByKeywords($client, ['spam', 'junk']);
                if ($spamFolder && $spamFolder->path !== $folder->path) {
                    $message->move($spamFolder->path);
                } else {
                    $message->delete(true);
                }
            }
            $client->disconnect();
        } catch (\Exception $e) {
            \Log::error('Mailbox markAsSpam error: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    /** AJAX: Load user's custom added mailbox accounts */
    public function apiGetAccounts()
    {
        $accounts = MailboxAccount::where('user_id', auth()->id())
            ->select('id', 'label', 'address', 'host', 'port', 'encryption', 'smtp_host', 'smtp_port', 'smtp_encryption')
            ->get();
        return response()->json($accounts);
    }

    /** AJAX: Add and encrypt new email account credentials */
    public function apiSaveAccount(Request $request)
    {
        $request->validate([
            'label'           => 'required|string|max:255',
            'address'         => 'required|email|max:255',
            'host'            => 'required|string|max:255',
            'port'            => 'required|integer',
            'encryption'      => 'required|string|in:ssl,tls,none',
            'username'        => 'required|string|max:255',
            'password'        => 'required|string',
            'smtp_host'       => 'required|string|max:255',
            'smtp_port'       => 'required|integer',
            'smtp_encryption' => 'required|string|in:ssl,tls,none',
        ]);

        $account = MailboxAccount::create([
            'user_id'         => auth()->id(),
            'label'           => $request->label,
            'address'         => $request->address,
            'host'            => $request->host,
            'port'            => $request->port,
            'encryption'      => $request->encryption,
            'username'        => $request->username,
            'password'        => $request->password,
            'smtp_host'       => $request->smtp_host,
            'smtp_port'       => $request->smtp_port,
            'smtp_encryption' => $request->smtp_encryption,
        ]);

        return response()->json(['success' => true, 'account' => $account]);
    }

    /** AJAX: Delete an account connection */
    public function apiDeleteAccount($id)
    {
        $account = MailboxAccount::where('user_id', auth()->id())->findOrFail($id);
        $account->delete();
        return response()->json(['success' => true]);
    }

    /** AJAX: Get Outbox Logs */
    public function apiOutbox(Request $request)
    {
        $logs = MailboxSentLog::where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(function ($log) {
                return [
                    'uid'      => 'local_' . $log->id,
                    'subject'  => $log->subject,
                    'from'     => 'Local Outbox Log',
                    'date'     => $log->created_at->format('d M, h:i A'),
                    'seen'     => true,
                    'starred'  => false,
                    'body'     => $log->body,
                    'is_local' => true,
                ];
            });

        return response()->json(['emails' => $logs]);
    }

    /** AJAX: Summarize an email using Gemini AI */
    public function apiSummarize(Request $request)
    {
        $request->validate([
            'uid'     => 'required|string',
            'account' => 'nullable|string',
            'folder'  => 'nullable|string',
        ]);

        $uid    = $request->input('uid');
        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            $apiKey = env('GEMINI_API_KEY');
        }

        if (!$apiKey) {
            return response()->json([
                'error' => 'Gemini AI API Key is not configured. Please add GEMINI_API_KEY in your .env file.'
            ], 400);
        }

        $body = '';

        try {
            if (str_starts_with($uid, 'local_')) {
                $logId = str_replace('local_', '', $uid);
                $log   = MailboxSentLog::findOrFail($logId);
                $body  = $log->body;
            } else {
                $accounts   = $this->getAllowedAccounts();
                $activeKey  = $request->get('account', array_key_first($accounts));
                $folderName = $request->get('folder', 'INBOX');

                if (!array_key_exists($activeKey, $accounts)) {
                    return response()->json(['error' => 'Unauthorized account accessor.'], 403);
                }

                $account = $accounts[$activeKey];
                $client  = $this->makeClient($account);
                $folder  = $client->getFolder($folderName);
                $message = $folder->query()->getMessageByUid($uid);

                if (!$message) {
                    $client->disconnect();
                    return response()->json(['error' => 'Email message not found.'], 404);
                }

                $body = $message->getHTMLBody() ?: $message->getTextBody() ?: '';
                $client->disconnect();
            }

            // Strip style sheets and HTML tags to get raw clean text for Gemini
            $cleanText = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $body);
            $cleanText = strip_tags($cleanText);
            $cleanText = trim(preg_replace('/\s+/', ' ', $cleanText));
            $cleanText = substr($cleanText, 0, 4000); // Token safe crop

            if (empty($cleanText)) {
                return response()->json(['error' => 'No readable text content found in this email to summarize.'], 400);
            }

            // Call Gemini 2.5 Flash Content Generation API
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

            $prompt = "You are an intelligent corporate AI assistant at Gayatri Enterprises (a B2B chemical & laboratory reagent distributor in India).\n" .
                      "Read the email content below and provide a concise, high-impact executive summary.\n" .
                      "CRITICAL INSTRUCTIONS:\n" .
                      "- Provide exactly 3 or 4 clear, professional bullet points.\n" .
                      "- Formulate the output purely in clean HTML lists (using <ul> and <li> tags).\n" .
                      "- Highlight important figures, dates, specific queries, and requested actions in bold (<strong>).\n" .
                      "- Keep the language extremely professional, concise, and easy to read.\n\n" .
                      "Email Content:\n" . $cleanText;

            $response = \Illuminate\Support\Facades\Http::timeout(30)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                ]
            ]);

            if ($response->successful()) {
                $resData = $response->json();
                if (isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
                    $summary = $resData['candidates'][0]['content']['parts'][0]['text'];
                    
                    // Strip any markdown wrappers if added by the API
                    $summary = preg_replace('/^```html\s*|```\s*$/i', '', trim($summary));
                    
                    return response()->json(['success' => true, 'summary' => $summary]);
                }
            }

            return response()->json(['error' => 'Failed to obtain AI response: ' . $response->body()], 502);

        } catch (\Exception $e) {
            \Log::error('Mailbox AI Summarize error: ' . $e->getMessage());
            return response()->json(['error' => 'AI Summarize process encountered an error: ' . $e->getMessage()], 500);
        }
    }
}
