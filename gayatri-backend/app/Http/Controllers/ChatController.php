<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\MessageRead;
use App\Events\MessageSent;
use App\Events\MessageRead as MessageReadEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index()
    {
        auth()->user()->update(['last_seen_at' => now()]);
        return view('chat.index');
    }

    public function fetchConversations()
    {
        try {
            $user = Auth::user();
            $conversations = Conversation::whereHas('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })->with(['users', 'latestMessage.reads', 'latestMessage.user'])
              ->withCount(['messages as unread_count' => function ($query) use ($user) {
                  $query->where('user_id', '!=', $user->id)
                        ->whereDoesntHave('reads', function ($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
              }])->get();
            
            foreach ($conversations as $convo) {
                $convo->users->map(function($u) {
                    $u->is_online = $u->last_seen_at && $u->last_seen_at->gt(now()->subMinutes(5));
                    return $u;
                });

                if ($convo->latestMessage) {
                    $convo->latestMessage->is_read = $convo->latestMessage->reads
                        ->where('user_id', '!=', $user->id)
                        ->count() > 0;
                }
            }

            return response()->json($conversations);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function fetchMessages($id)
    {
        auth()->user()->update(['last_seen_at' => now()]);
        try {
            $user = Auth::user();
            
            // Permission Check
            $this->checkConversationAccess($id);

            $messages = Message::where('conversation_id', $id)
                ->with(['user', 'parent.user', 'reads'])
                ->orderBy('created_at', 'asc')
                ->get()
                ->filter(function($msg) use ($user) {
                    $deletedBy = $msg->deleted_by;
                    if (is_string($deletedBy)) {
                        $deletedBy = json_decode($deletedBy, true);
                    }
                    $deletedBy = is_array($deletedBy) ? $deletedBy : [];
                    return !in_array($user->id, $deletedBy);
                })
                ->map(function($msg) use ($user) {
                    if ($msg->is_deleted_globally) {
                        $msg->body = 'This message was deleted.';
                        $msg->attachment = null;
                        $msg->is_deleted_globally_flag = true;
                    }
                    $starredBy = is_array($msg->starred_by) ? $msg->starred_by : (is_string($msg->starred_by) ? json_decode($msg->starred_by, true) : []);
                    $msg->is_starred = in_array($user->id, is_array($starredBy) ? $starredBy : []);

                    $pinnedBy = is_array($msg->pinned_by) ? $msg->pinned_by : (is_string($msg->pinned_by) ? json_decode($msg->pinned_by, true) : []);
                    $msg->is_pinned = in_array($user->id, is_array($pinnedBy) ? $pinnedBy : []);

                    $msg->is_read = $msg->reads->where('user_id', '!=', $user->id)->count() > 0;
                    return $msg;
                })
                ->values();

            // Mark as read — use raw DB to avoid MessageRead model issues
            $readsTable = \Schema::hasTable('team_message_reads') ? 'team_message_reads' : 'message_reads';

            // Get all message IDs already read by this user (raw query, no model)
            $allMsgIds = $messages->pluck('id')->toArray();
            $alreadyReadIds = [];
            if (!empty($allMsgIds)) {
                try {
                    $alreadyReadIds = DB::table($readsTable)
                        ->where('user_id', $user->id)
                        ->whereIn('message_id', $allMsgIds)
                        ->pluck('message_id')->toArray();
                } catch (\Exception $e) {
                    \Log::warning('Read fetch failed: ' . $e->getMessage());
                }
            }

            $unreadMessageIds = $messages
                ->where('user_id', '!=', $user->id)
                ->filter(fn($msg) => !in_array($msg->id, $alreadyReadIds))
                ->pluck('id')->toArray();

            if (!empty($unreadMessageIds)) {
                $now = now();
                // Detect schema: read_at vs created_at/updated_at
                $hasReadAt    = false;
                $hasTimestamps = false;
                try {
                    $hasReadAt    = \Schema::hasColumn($readsTable, 'read_at');
                    $hasTimestamps = \Schema::hasColumn($readsTable, 'created_at');
                } catch (\Exception $e) { }

                $inserts = array_map(function($msgId) use ($user, $now, $hasReadAt, $hasTimestamps) {
                    $row = ['message_id' => $msgId, 'user_id' => $user->id];
                    if ($hasReadAt)    $row['read_at']    = $now;
                    if ($hasTimestamps) {
                        $row['created_at'] = $now;
                        $row['updated_at'] = $now;
                    }
                    return $row;
                }, $unreadMessageIds);

                try {
                    DB::table($readsTable)->insertOrIgnore($inserts);
                } catch (\Exception $e) {
                    \Log::warning('Read insert failed: ' . $e->getMessage());
                }

                foreach ($unreadMessageIds as $msgId) {
                    try {
                        broadcast(new \App\Events\MessageRead($msgId, $user->id))->toOthers();
                    } catch (\Exception $e) {
                        \Log::error('Read broadcast error: ' . $e->getMessage());
                    }
                }
            }

            return response()->json($messages);
        } catch (\Exception $e) {
            \Log::error('Chat fetch error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            @file_put_contents(storage_path('logs/chat_debug.log'), $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sendMessage(Request $request, $id)
    {
        try {
            // Permission Check
            $this->checkConversationAccess($id);

            $request->validate([
                'body' => 'required_without:attachment',
                'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,gif,zip,rar,tar,gz',
                'mentioned_ids' => 'nullable|array',
                'mentioned_ids.*' => 'exists:users,id'
            ]);

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalName = $file->getClientOriginalName();
                $finalName = $this->getUniqueFilename('public', 'chat_attachments', $originalName);
                $attachmentPath = $file->storeAs('chat_attachments', $finalName, 'public');
            }

            $message = Message::create([
                'conversation_id' => $id,
                'user_id' => Auth::id(),
                'body' => $request->body,
                'attachment' => $attachmentPath,
                'parent_id' => $request->parent_id
            ]);

            $message->load('user', 'parent', 'reads');

            try {
                broadcast(new MessageSent($message))->toOthers();
                
                $mentionedIds = $request->input('mentioned_ids', []);
                $this->sendPushNotification($message, $mentionedIds);
            } catch (\Exception $e) {
                \Log::error('Notification error: ' . $e->getMessage());
            }

            return response()->json($message);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createGroup(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized. Only admins can create groups.'], 403);
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'staff_ids' => 'required|array',
                'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $iconPath = null;
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $originalName = $file->getClientOriginalName();
                $finalName = $this->getUniqueFilename('public', 'chat_icons', $originalName);
                $iconPath = $file->storeAs('chat_icons', $finalName, 'public');
            }

            $conversation = Conversation::create([
                'name' => $request->name,
                'is_group' => true,
                'created_by' => Auth::id(),
                'icon' => $iconPath
            ]);

            $uids = array_merge($request->staff_ids, [Auth::id()]);
            $conversation->users()->attach(array_unique($uids));

            // Log Activity
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Created Chat',
                'details' => 'Group: ' . $request->name,
            ]);

            return response()->json($conversation);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function startDirectMessage(Request $request)
    {
        try {
            $request->validate(['user_id' => 'required|exists:users,id']);
            
            $existing = Conversation::where('is_group', false)
                ->whereHas('users', fn($q) => $q->where('users.id', Auth::id()))
                ->whereHas('users', fn($q) => $q->where('users.id', $request->user_id))
                ->first();

            if ($existing) return response()->json($existing);

            $conversation = Conversation::create(['is_group' => false]);
            $conversation->users()->attach([Auth::id(), $request->user_id]);

            // Log if staff-to-staff
            $targetUser = User::find($request->user_id);
            if (Auth::user()->role !== 'client' && $targetUser && $targetUser->role !== 'client') {
                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Staff Chat Initiated',
                    'details' => 'Staff (' . Auth::user()->name . ') initiated chat with Staff (' . $targetUser->name . ')'
                ]);
            }

            return response()->json($conversation);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleStar($id)
    {
        try {
            $message = Message::findOrFail($id);
            $this->checkConversationAccess($message->conversation_id);

            $starredBy = $message->starred_by ?? [];
            if (in_array(Auth::id(), $starredBy)) {
                $starredBy = array_values(array_diff($starredBy, [Auth::id()]));
                $isStarred = false;
            } else {
                $starredBy[] = Auth::id();
                $isStarred = true;
            }

            $message->update(['starred_by' => $starredBy]);
            return response()->json(['success' => true, 'is_starred' => $isStarred]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function searchMessages(Request $request, $id)
    {
        try {
            // Permission Check
            $this->checkConversationAccess($id);

            $query = $request->query('q');
            $messages = Message::where('conversation_id', $id)
                ->where('body', 'like', "%$query%")
                ->with('user')
                ->get();
            return response()->json($messages);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function fetchStaff()
    {
        try {
            $users = User::where('role', '!=', 'client')
                ->where('id', '!=', Auth::id())
                ->select('id', 'name', 'role', 'last_seen_at')
                ->get()
                ->map(function($u) {
                    $u->is_online = $u->last_seen_at && $u->last_seen_at->gt(now()->subMinutes(5));
                    return $u;
                });

            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getTotalUnreadCount()
    {
        try {
            $user = Auth::user();
            if (!$user) return response()->json(['count' => 0]);

            $conversationIds = DB::table('team_conversation_user')
                ->where('user_id', $user->id)
                ->pluck('conversation_id');

            $readsTable = \Schema::hasTable('team_message_reads') ? 'team_message_reads' : 'message_reads';

            $count = Message::whereIn('team_messages.conversation_id', $conversationIds)
                ->where('team_messages.user_id', '!=', $user->id)
                ->leftJoin($readsTable, function ($join) use ($user, $readsTable) {
                    $join->on('team_messages.id', '=', $readsTable . '.message_id')
                         ->where($readsTable . '.user_id', '=', $user->id);
                })
                ->whereNull($readsTable . '.id')
                ->count('team_messages.id');

            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
             return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateGroupMembers(Request $request, $id)
    {
        try {
            $request->validate([
                'staff_ids' => 'required|array',
                'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            $conversation = Conversation::findOrFail($id);
            
            // Only admin or creator can update members
            if (Auth::user()->role !== 'admin' && $conversation->created_by !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized. Only admins or the group creator can update members.'], 403);
            }

            // Handle Icon update
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $originalName = $file->getClientOriginalName();
                $finalName = $this->getUniqueFilename('public', 'chat_icons', $originalName);
                $iconPath = $file->storeAs('chat_icons', $finalName, 'public');
                $conversation->update(['icon' => $iconPath]);
            }

            // Sync users (including current user who created it)
            $uids = array_unique(array_merge($request->staff_ids, [$conversation->created_by ?: Auth::id()]));
            $conversation->users()->sync($uids);

            // Log Activity
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated Group Members',
                'details' => 'Group: ' . $conversation->name . ' (ID: ' . $conversation->id . ')',
            ]);

            return response()->json(['success' => true, 'users' => $conversation->load('users')->users]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function archiveGroup($id)
    {
        try {
            $conversation = Conversation::findOrFail($id);

            // Only admin or creator can delete group
            if (Auth::user()->role !== 'admin' && $conversation->created_by !== Auth::id()) {
                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Unauthorized Chat Deletion Attempt',
                    'details' => 'Staff attempted to delete/archive chat: ' . ($conversation->name ?? 'Direct Chat') . ' (ID: ' . $conversation->id . ')'
                ]);
                return response()->json(['error' => 'Unauthorized. Only admins or the group creator can delete this group.'], 403);
            }

            $conversation->delete(); // Soft delete or full delete
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function downloadFile($path)
    {
        // Block directory traversal attempts
        if (str_contains($path, '..') || str_contains($path, '\\')) {
            abort(403, 'Directory traversal detected.');
        }

        $lowercasePath = strtolower($path);

        // Dynamic IDOR protection for Chat Attachments
        if (str_contains($lowercasePath, 'chat_attachments/')) {
            if (!auth()->check()) {
                abort(403, 'Unauthorized access to chat attachment. Please log in.');
            }
            
            $user = auth()->user();
            
            if ($user->role !== 'admin') {
                $message = \App\Models\Message::where('attachment', 'like', "%{$path}%")->first();
                if ($message) {
                    $isMember = \DB::table('team_conversation_user')
                        ->where('conversation_id', $message->conversation_id)
                        ->where('user_id', $user->id)
                        ->exists();
                    if (!$isMember) {
                        abort(403, 'Unauthorized access to this chat attachment.');
                    }
                } else {
                    if ($user->role !== 'staff') {
                        abort(403, 'Unauthorized access.');
                    }
                }
            }
        }

        // Dynamic IDOR protection for Task Chat Files
        if (str_contains($lowercasePath, 'chat_files/')) {
            if (!auth()->check()) {
                abort(403, 'Unauthorized access to task attachment. Please log in.');
            }
            
            $user = auth()->user();
            
            if ($user->role !== 'admin') {
                $chat = \App\Models\Chat::where('attachment', 'like', "%{$path}%")->first();
                if ($chat) {
                    $task = $chat->task;
                    if ($task && !$task->isAssignedTo($user->id)) {
                        abort(403, 'Unauthorized access to this task attachment.');
                    }
                } else {
                    if ($user->role !== 'staff') {
                        abort(403, 'Unauthorized access to this attachment.');
                    }
                }
            }
        }

        $fullPath = storage_path('app/public/' . $path);

        if (!file_exists($fullPath)) {
            abort(404);
        }
        
        // Force download if parameter is present
        if (request()->has('download')) {
            return response()->download($fullPath);
        }
        
        return response()->file($fullPath);
    }

    public function deleteMessage(Request $request, $id)
    {
        try {
            $message = Message::findOrFail($id);
            $type = $request->input('type'); // 'for_me' or 'for_everyone'

            // Permission Check
            $this->checkConversationAccess($message->conversation_id);

            // ONLY the sender of the message can delete it (for me or for everyone)
            if ($message->user_id !== Auth::id()) {
                return response()->json(['error' => 'Not authorized. Only the sender can delete this message or attachment.'], 403);
            }

            if ($type === 'for_everyone') {
                $message->update(['is_deleted_globally' => true]);
            } else {
                $deletedBy = $message->deleted_by;
                if (is_string($deletedBy)) {
                    $deletedBy = json_decode($deletedBy, true);
                }
                $deletedBy = is_array($deletedBy) ? $deletedBy : [];
                if (!in_array(Auth::id(), $deletedBy)) {
                    $deletedBy[] = Auth::id();
                    $message->update(['deleted_by' => $deletedBy]);
                }
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function forwardMessage(Request $request, $id)
    {
        try {
            $original = Message::findOrFail($id);
            
            // Check access to original conversation
            $this->checkConversationAccess($original->conversation_id);
            
            $request->validate(['conversation_id' => 'required|exists:team_conversations,id']);

            // Check access to target conversation
            $this->checkConversationAccess($request->conversation_id);

            $message = Message::create([
                'conversation_id' => $request->conversation_id,
                'user_id' => Auth::id(),
                'body' => $original->body,
                'attachment' => $original->attachment,
                'parent_id' => null
            ]);

            $message->load('user', 'parent', 'reads');
            try {
                broadcast(new MessageSent($message))->toOthers();
                $this->sendPushNotification($message);
            } catch (\Exception $e) { \Log::error('Forward notification error: ' . $e->getMessage()); }

            return response()->json($message);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper to handle filename conflicts by appending (1), (2), etc.
     */
    private function getUniqueFilename($disk, $directory, $originalName)
    {
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $extension = $extension ? '.' . $extension : '';
        
        $path = $directory . '/' . $originalName;
        $counter = 1;
        
        while (\Storage::disk($disk)->exists($path)) {
            $path = $directory . '/' . $filename . ' (' . $counter . ')' . $extension;
            $counter++;
        }
        
        return basename($path);
    }

    public function togglePin($id)
    {
        try {
            $message = Message::findOrFail($id);
            $this->checkConversationAccess($message->conversation_id);

            $pinnedBy = $message->pinned_by ?? [];
            if (in_array(Auth::id(), $pinnedBy)) {
                $pinnedBy = array_values(array_diff($pinnedBy, [Auth::id()]));
                $isPinned = false;
            } else {
                $pinnedBy[] = Auth::id();
                $isPinned = true;
            }

            $message->update(['pinned_by' => $pinnedBy]);
            return response()->json(['success' => true, 'is_pinned' => $isPinned]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getStarredMessages($id)
    {
        try {
            $user = Auth::user();
            
            // Permission Check
            $this->checkConversationAccess($id);

            $messages = Message::where('conversation_id', $id)
                ->whereJsonContains('starred_by', $user->id)
                ->with(['user'])
                ->get()
                ->filter(function($msg) use ($user) {
                    $deletedBy = $msg->deleted_by;
                    if (is_string($deletedBy)) {
                        $deletedBy = json_decode($deletedBy, true);
                    }
                    $deletedBy = is_array($deletedBy) ? $deletedBy : [];
                    return !in_array($user->id, $deletedBy) && !$msg->is_deleted_globally;
                })
                ->values();

            return response()->json($messages);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function clearGroupChats(Request $request, $id)
    {
        try {
            $conversation = Conversation::findOrFail($id);

            // Only admin can clear all messages
            if (Auth::user()->role !== 'admin') {
                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Unauthorized Chat Clear Attempt',
                    'details' => 'Staff attempted to clear chat history for: ' . ($conversation->name ?? 'Direct Chat') . ' (ID: ' . $conversation->id . ')'
                ]);
                return response()->json(['error' => 'Unauthorized. Only admins can clear chat history.'], 403);
            }

            $request->validate(['password' => 'required|string']);
            if (!\Hash::check($request->password, Auth::user()->password)) {
                return response()->json(['error' => 'Incorrect password'], 403);
            }

            $conversation = Conversation::findOrFail($id);
            
            // Delete all messages in the conversation
            Message::where('conversation_id', $id)->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check if the authenticated user has access to a conversation
     */
    protected function checkConversationAccess($conversationId)
    {
        $user = Auth::user();
        $isMember = DB::table('team_conversation_user')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            throw new \Exception('Unauthorized access to this conversation.', 403);
        }
    }

    protected function sendPushNotification($message, $mentionedIds = [])
    {
        try {
            $convo = $message->conversation;
            if (!$convo) return;
            $recipients = $convo->users()->where('users.id', '!=', Auth::id())->get();
            foreach ($recipients as $user) {
                $isMentioned = in_array($user->id, $mentionedIds);
                $title = $isMentioned ? 'You were mentioned by ' . Auth::user()->name : 'New Message from ' . Auth::user()->name;
                if ($convo->is_group && !$isMentioned) {
                     $title = 'New Message in ' . $convo->name;
                }

                $payload = json_encode([
                    'title' => $title,
                    'body' => $message->body ?: 'Sent an attachment',
                    'icon' => '/pwa-icon.png',
                    'url' => route('chat.index'),
                    // 'sound' => disabled
                ]);
                foreach ($user->pushSubscriptions as $sub) {
                    $this->dispatchPush($sub, $payload);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Push dispatch failure: ' . $e->getMessage());
        }
    }

    protected function dispatchPush($sub, $payload)
    {
        if (class_exists('\Minishlink\WebPush\WebPush')) {
            try {
                $auth = [
                    'VAPID' => [
                        'subject' => 'mailto:info@gayatrient.com',
                        'publicKey' => config('webpush.vapid.public_key') ?: env('VAPID_PUBLIC_KEY'),
                        'privateKey' => config('webpush.vapid.private_key') ?: env('VAPID_PRIVATE_KEY'),
                    ],
                ];
                
                $webPush = new \Minishlink\WebPush\WebPush($auth);
                $webPush->queueNotification(
                    \Minishlink\WebPush\Subscription::create([
                        'endpoint' => $sub->endpoint,
                        'publicKey' => $sub->public_key,
                        'authToken' => $sub->auth_token,
                        'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
                    ]),
                    $payload
                );
                
                foreach ($webPush->flush() as $report) {
                    if (!$report->isSuccess()) {
                        \Log::warning("Push Failed: " . $report->getReason());
                        // If endpoint no longer valid, delete subscription
                        if ($report->isSubscriptionExpired()) {
                            PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Push sending failed: ' . $e->getMessage());
            }
        }
    }

    public function fetchThread($id)
    {
        try {
            $parentMsg = Message::findOrFail($id);
            $this->checkConversationAccess($parentMsg->conversation_id);

            $messages = Message::where('id', $id)
                ->orWhere('parent_id', $id)
                ->with(['user', 'reads'])
                ->orderBy('created_at', 'asc')
                ->get()
                ->filter(function($msg) {
                    $deletedBy = $msg->deleted_by;
                    if (is_string($deletedBy)) {
                        $deletedBy = json_decode($deletedBy, true);
                    }
                    $deletedBy = is_array($deletedBy) ? $deletedBy : [];
                    return !in_array(Auth::id(), $deletedBy);
                })
                ->map(function($msg) {
                    if ($msg->is_deleted_globally) {
                        $msg->body = 'This message was deleted.';
                        $msg->attachment = null;
                        $msg->is_deleted_globally_flag = true;
                    }
                    return $msg;
                })
                ->values();

            return response()->json($messages);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

