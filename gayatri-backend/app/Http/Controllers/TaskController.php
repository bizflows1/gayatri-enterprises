<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    // 1. Manage Tasks Page (Listing with Filters)
    public function index(Request $request) {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'staff') {
            abort(403, 'Unauthorized access.');
        }

        $query = Task::with(['assignees'])
            ->withCount(['chats as unread_chats_count' => function ($query) {
                $query->where('is_read', false)
                      ->where('user_id', '!=', auth()->id());
            }]);

        // Filter by Task Name
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by Status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        } elseif (!$request->has('show_completed') || $request->show_completed != '1') {
            // Default behavior: Hide completed tasks if not explicitly requested
            $query->where('status', '!=', 'completed');
        }

        // Filter by Priority
        if ($request->has('priority') && $request->priority != 'all') {
            $query->where('priority', $request->priority);
        }

        // Filter by Timeline
        if ($request->has('timeline')) {
            if ($request->timeline == 'today') {
                $query->whereDate('due_date', today());
            } elseif ($request->timeline == 'overdue') {
                $query->where('due_date', '<', today())->where('status', '!=', 'completed');
            } elseif ($request->timeline == 'upcoming') {
                $query->where('due_date', '>', today());
            }
        }

        // Filter by Assigned Staff
        if ($request->filled('staff_id') && $request->staff_id != 'all') {
            $query->whereHas('assignees', function($q) use ($request) {
                $q->where('users.id', $request->staff_id);
            });
        }

        // Staff only see their own tasks
        if (Auth::user()->role === 'staff') {
            $query->whereHas('assignees', fn($q) => $q->where('user_id', auth()->id()));
        }

        $tasks = $query->latest()->paginate(20)->withQueryString();
        $users = User::where('role', 'staff')->get();

        return view('admin.tasks.index', compact('tasks', 'users'));
    }

    // Assign Task Form
    public function create() {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }
        $users = User::where('role', 'staff')->get();
        return view('admin.tasks.create', compact('users'));
    }

    // 2. Naya Task Save karne ke liye
    public function store(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'staff_ids' => 'required|array',
            'staff_ids.*' => 'exists:users,id',
            'priority' => 'required',
            'due_date' => 'required|date',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'status' => 'pending'
        ]);

        $task->assignees()->attach($request->staff_ids);

        // Send Push Notification to assigned staff
        try {
            $assignedStaff = User::whereIn('id', $request->staff_ids)->get();
            \App\Services\PushNotificationService::sendToUsers(
                $assignedStaff,
                'New Task Assigned: ' . $task->title,
                'Priority: ' . strtoupper($task->priority) . ' | Due: ' . \Carbon\Carbon::parse($task->due_date)->format('d M, Y'),
                route('staff.dashboard')
            );
        } catch (\Exception $e) {
            \Log::error('Task assigned push notification failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Task assigned successfully.');
    }

    // 3. Task Details aur Chat dikhane ke liye
    public function show($id) {
        $task = Task::with(['assignees', 'chats.user'])->findOrFail($id);
        $user = auth()->user();

        // Security Check
        if($user->role !== 'admin' && !$task->isAssignedTo($user->id)){
            abort(403, 'Unauthorized access');
        }

        // LOGIC: Jab chat khule, toh saamne wale ke messages ko 'Read' mark kar do
        \App\Models\Chat::where('task_id', $id)
                        ->where('user_id', '!=', $user->id) // Mere khud ke msg nahi
                        ->update(['is_read' => true]);

        $users = [];
        if ($user->role === 'admin') {
            $users = User::where('role', 'staff')->get();
        }

        return view('admin.task-view', compact('task', 'users'));
    }

    // Task Re-assign / Transfer (Admin Only)
    public function transferTask(Request $request, $id) {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'staff_ids' => 'required|array',
            'staff_ids.*' => 'exists:users,id',
        ]);

        $task = Task::findOrFail($id);
        
        // Sync assignees (it will detach old and attach new)
        $task->assignees()->sync($request->staff_ids);

        // Send Push Notification to newly assigned staff
        try {
            $assignedStaff = User::whereIn('id', $request->staff_ids)->get();
            \App\Services\PushNotificationService::sendToUsers(
                $assignedStaff,
                'Task Re-assigned: ' . $task->title,
                'You have been assigned to this task. Priority: ' . strtoupper($task->priority),
                route('task.view', $task->id)
            );
        } catch (\Exception $e) {
            \Log::error('Task transfer push notification failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Task assignees updated successfully.');
    }

    // 4. Message aur Attachment Save karne ke liye
    public function sendMessage(Request $request, $id) {
        // SECURITY: Verify user is authorized for this task
        $task = Task::findOrFail($id);
        $user = auth()->user();
        
        if ($user->role !== 'admin' && !$task->isAssignedTo($user->id)) {
            abort(403, 'Unauthorized access to this task.');
        }
        
        // Aligned with main ChatController — using Laravel 'mimes' validation
        $request->validate([
            'message' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,gif,webp,zip,rar,tar,gz',
        ]);

        // Agar message aur attachment dono nahi hain, toh error do
        if(!$request->message && !$request->hasFile('attachment')){
            return back()->with('error', 'Please write a message or attach a file.');
        }

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $originalName = $file->getClientOriginalName();
            $finalName = $this->getUniqueFilename('public', 'chat_files', $originalName);
            $filePath = $file->storeAs('chat_files', $finalName, 'public');
        }

        $chat = \App\Models\Chat::create([
            'task_id' => $id,
            'user_id' => auth()->id(),
            'message' => $request->message ?? '',
            'attachment' => $filePath,
            'is_read' => false
        ]);

        // Universal Push Notification for Task Chat discussion
        try {
            $sender = auth()->user();
            
            // Get all other assignees
            $assignees = $task->assignees()->where('users.id', '!=', $sender->id)->get();
            
            // Get all admins (excluding sender)
            $admins = User::where('role', 'admin')->where('id', '!=', $sender->id)->get();
            
            // Merge recipients
            $recipients = $assignees->merge($admins);
            
            $body = $chat->message ?: 'Sent an attachment';
            
            \App\Services\PushNotificationService::sendToUsers(
                $recipients,
                "New message from {$sender->name} on task: {$task->title}",
                $body,
                route('task.view', $task->id)
            );
        } catch (\Exception $e) {
            \Log::error('Task Chat message push notification failed: ' . $e->getMessage());
        }

        return back(); // Success message ki zaroorat nahi, chat flow fast rakho
    }
    // 5. Task Status Update (Mark as Done / Reopen)
    public function updateStatus($id) {
        $task = Task::findOrFail($id);
        $user = Auth::user();

        // If the task is already completed, allow admin to revoke / reopen it
        if ($task->status === 'completed') {
            if ($user->role !== 'admin') {
                abort(403, 'Only admins can revoke completed tasks.');
            }
            
            $task->update(['status' => 'pending']);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Task marked as pending (Reopened)!']);
            }

            return back()->with('success', 'Task marked as pending (Reopened)!');
        }

        // Security Check: Only Assignee or Admin can update
        if($user->role !== 'admin' && !$task->isAssignedTo($user->id)){
            abort(403, 'Unauthorized to update this task.');
        }
        
        // Status badal kar completed kar do
        $task->update(['status' => 'completed']);

        // IF request expects JSON (like the floating hub AJAX), return JSON
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Task marked as completed!']);
        }

        return back()->with('success', 'Task marked as completed!');
    }

    // ==========================================
    // REAL-TIME CHAT API (POLLING)
    // ==========================================

    public function fetchMessages(Request $request, $id) {
        $task = Task::findOrFail($id);
        $user = Auth::user();

        if($user->role !== 'admin' && !$task->isAssignedTo($user->id)){
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Fetch messages newer than last_id
        $lastId = $request->input('last_id', 0);
        
        $messages = \App\Models\Chat::where('task_id', $id)
            ->where('id', '>', $lastId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark fetched messages as read (if they are not mine)
        if($messages->isNotEmpty()){
            \App\Models\Chat::where('task_id', $id)
                ->where('id', '>', $lastId)
                ->where('user_id', '!=', $user->id)
                ->update(['is_read' => true]);
        }

        // Return JSON for JS
        return response()->json([
            'messages' => $messages->map(function($msg) use ($user) {
                // Serve task attachments via /chat/file route for Hostinger/symlink resilience
                $url = $msg->attachment ? url('/chat/file/' . $msg->attachment) : null;
                
                return [
                    'id' => $msg->id,
                    'user_name' => $msg->user->name,
                    'user_initial' => substr($msg->user->name, 0, 1),
                    'user_avatar' => $msg->user->avatar_url,
                    'is_me' => $msg->user_id == $user->id,
                    'message' => $msg->message,
                    'attachment_url' => $url,
                    'attachment_name' => $msg->attachment ? basename($msg->attachment) : null,
                    'is_image' => $msg->attachment ? in_array(strtolower(pathinfo($msg->attachment, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp']) : false,
                    'time' => $msg->created_at->format('h:i A')
                ];
            })
        ]);
    }

    public function checkUnread() {
        $user = Auth::user();
        
        $unreadCount = 0;

        if($user->role === 'admin') {
            // Admin sees ALL unread messages sent by others
            $unreadCount = \App\Models\Chat::where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->count();
        } elseif ($user->role === 'staff') {
            // Staff sees unread messages in tasks assigned to them
            $myTaskIds = Task::whereHas('assignees', function($q) use ($user){
                $q->where('user_id', $user->id);
            })->pluck('id');

            $unreadCount = \App\Models\Chat::whereIn('task_id', $myTaskIds)
                ->where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->count();
        }

        return response()->json(['unread_count' => $unreadCount]);
    }

    public function getStaffSummary() {
        $user = Auth::user();
        $is_admin = strtolower($user->role) === 'admin';
        
        // 1. Pending Tasks
        $pendingQuery = Task::where('status', 'pending');
        if(!$is_admin) {
            $pendingQuery->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
        }
        $pendingCount = $pendingQuery->count();

        // 2. Completed Today
        $completedTodayQuery = Task::where('status', 'completed')
            ->whereDate('updated_at', now()->toDateString());
        if(!$is_admin) {
            $completedTodayQuery->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
        }
        $completedTodayCount = $completedTodayQuery->count();

        // 3. Unread Messages 
        $unreadCount = 0;
        if($is_admin) {
            // Firm-wide unread messages where the admin is NOT the sender
            $unreadCount = \App\Models\Chat::where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->count();
        } else {
            // Staff personal unread messages (Only from Active tasks)
            $myTaskIds = Task::where('status', '!=', 'completed')
                ->whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
                ->pluck('id');
            $unreadCount = \App\Models\Chat::whereIn('task_id', $myTaskIds)
                ->where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->count();
        }

        // 4. Latest Tasks (Only show Active tasks in the Hub)
        $latestTasksQuery = Task::where('status', '!=', 'completed')
            ->with(['assignees' => function($q) {
                $q->select('users.id', 'users.name');
            }])->latest();
        if(!$is_admin) {
            $latestTasksQuery->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
        }
        $latestTasks = $latestTasksQuery->take(5)->get(['id', 'title', 'status', 'priority', 'due_date']);

        // 5. Admin specific metrics (Due Today & Active Staff)
        $dueTodayCount = 0;
        $activeStaff = [];

        if ($is_admin) {
            $dueTodayCount = Task::where('status', 'pending')
                ->whereDate('due_date', now()->toDateString())
                ->count();
                
            $activeStaff = \App\Models\User::where('role', 'staff')
                ->where('is_active', true)
                ->whereNotNull('last_seen_at')
                ->where('last_seen_at', '>=', now()->subMinutes(5))
                ->get(['id', 'name', 'last_seen_at']);
        }

        // 6. Team Chat and Mailbox unread counts
        $conversationIds = \DB::table('team_conversation_user')
            ->where('user_id', $user->id)
            ->pluck('conversation_id');
        $readsTable = \Schema::hasTable('team_message_reads') ? 'team_message_reads' : 'message_reads';
        $teamChatUnread = \App\Models\Message::whereIn('team_messages.conversation_id', $conversationIds)
            ->where('team_messages.user_id', '!=', $user->id)
            ->leftJoin($readsTable, function ($join) use ($user, $readsTable) {
                $join->on('team_messages.id', '=', $readsTable . '.message_id')
                     ->where($readsTable . '.user_id', '=', $user->id);
            })
            ->whereNull($readsTable . '.id')
            ->count('team_messages.id');

        $mailboxUnread = \App\Models\MailboxEmail::where('folder_name', 'INBOX')
            ->where('seen', false)
            ->count();

        return response()->json([
            'pending' => $pendingCount,
            'completed_today' => $completedTodayCount,
            'due_today' => $dueTodayCount,
            'unread' => $unreadCount,
            'tasks' => $latestTasks,
            'active_staff' => $activeStaff,
            'admin_mode' => $is_admin,
            'team_chat_unread' => $teamChatUnread,
            'mailbox_unread' => $mailboxUnread,
        ]);
    }

    /**
     * Delete a task (Admin Only)
     */
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $task = Task::findOrFail($id);
        
        // Delete related chats/files if necessary (optional depending on DB cascades)
        // For now, simple delete
        $task->delete();

        return back()->with('success', 'Task deleted successfully.');
    }

    public function pingPresence()
    {
        $user = Auth::user();
        if ($user) {
            $user->update(['last_seen_at' => now()]);
        }
        return response()->json(['status' => 'ok']);
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
}