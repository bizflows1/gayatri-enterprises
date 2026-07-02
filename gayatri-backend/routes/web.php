<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LeaveController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// The public marketing site lives in the separate Vite/React app — this
// backend is API + admin/staff/client portal only. "/" just sends visitors
// there instead of rendering a page here.
Route::get('/', function () {
    return redirect()->away(rtrim(explode(',', env('FRONTEND_URLS', 'http://localhost:3010'))[0], '/'));
})->name('home');

// Storage Symlink Fallback Route for Hostinger (fixes 404s)
Route::get('/storage/{path}', function($path) {
    $lowercasePath = strtolower($path);
    $sensitivePrefixes = ['documents/', 'selfies/', 'chat_attachments/', 'chat_files/', 'clients/'];
    
    foreach ($sensitivePrefixes as $prefix) {
        if (str_starts_with($lowercasePath, $prefix)) {
            if (!auth()->check()) {
                abort(403, 'Unauthorized access to storage file. Please login to continue.');
            }
            
            $user = auth()->user();
            
            // IDOR Protection for Client Repositories
            if (str_starts_with($lowercasePath, 'clients/')) {
                if ($user->role !== 'admin' && $user->role !== 'staff') {
                    $parts = explode('/', $path);
                    if (count($parts) >= 2) {
                        $requestedStorageName = $parts[1];
                        if (empty($user->storage_name) || $user->storage_name !== $requestedStorageName) {
                            abort(403, 'Unauthorized access to this document repository.');
                        }
                    } else {
                        abort(403, 'Unauthorized access.');
                    }
                }
            }
            
            // IDOR Protection for Staff Selfies
            if (str_starts_with($lowercasePath, 'selfies/')) {
                if ($user->role !== 'admin' && $user->role !== 'staff') {
                    abort(403, 'Unauthorized access to attendance records.');
                }
            }
            
            // IDOR Protection for Chat Attachments
            if (str_starts_with($lowercasePath, 'chat_attachments/')) {
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
                            abort(403, 'Unauthorized access to this attachment.');
                        }
                    }
                }
            }

            // IDOR Protection for Task Chat Files
            if (str_starts_with($lowercasePath, 'chat_files/')) {
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
        }
    }

    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) abort(404);
    return response()->file($fullPath);
})->where('path', '.*');

// Public Chat File Route (Dynamic security handles sensitive attachments inside the controller)
Route::get('/chat/file/{path}', [App\Http\Controllers\ChatController::class, 'downloadFile'])
    ->where('path', '.*')
    ->name('chat.file');

Route::get('/install', function() {
    return response()->file(public_path('install.html'));
})->name('pwa.install');

Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| AUTH / LOGIN ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/portal', [AuthController::class, 'showLoginForm'])->name('portal.login');
Route::get('/portal-login', [AuthController::class, 'showLoginForm']); 
Route::get('/login', fn () => redirect()->route('portal.login'))->name('login');

// Unified Password Login
Route::post('/check-role', [AuthController::class, 'checkRole'])
    ->middleware('throttle:5,1')
    ->name('check.role');
Route::post('/verify-password', [AuthController::class, 'verifyPassword'])
    ->middleware('throttle:5,1')
    ->name('verify.password');

// Forgot Password


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (AUTH REQUIRED)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {


    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['role:admin,staff'])->group(function () {
        Route::get('/manage-clients', [ClientController::class, 'index'])->name('manage.clients');
        Route::get('/add-user', [ClientController::class, 'create'])->name('user.form');
        Route::post('/add-user', [ClientController::class, 'store'])->name('user.store');
        Route::get('/edit-user/{id}', [ClientController::class, 'edit'])->name('user.edit');
        Route::post('/edit-user/{id}', [ClientController::class, 'update'])->name('user.update');
        Route::patch('/toggle-status/{id}', [ClientController::class, 'toggleStatus'])->name('user.status');
        Route::delete('/delete-user/{id}', [ClientController::class, 'destroy'])->name('user.delete');
        Route::post('/import-clients', [ClientController::class, 'import'])->name('user.import');
        Route::get('/import-clients/sample', [ClientController::class, 'downloadSample'])->name('user.import.sample');
    });

    /* -------- ADMIN ONLY -------- */
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin/messages', [AdminController::class, 'messages'])->name('admin.messages');
        Route::delete('/admin/messages/delete', [AdminController::class, 'deleteMessages'])->name('admin.messages.delete');
        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity.logs');

        // Reviews Moderation (GET index removed — handled by Filament ReviewResource)
        Route::patch('/admin/reviews/{id}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
        Route::patch('/admin/reviews/{id}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
        Route::delete('/admin/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::get('/admin/assign-task', [TaskController::class, 'create'])->name('tasks.assign');
        Route::post('/admin/tasks/store', [TaskController::class, 'store'])->name('tasks.store');
        Route::delete('/admin/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('/admin/tasks/{id}/transfer', [TaskController::class, 'transferTask'])->name('tasks.transfer');

        // Professional Notice System
        Route::get('/admin/notices', [NoticeController::class, 'index'])->name('admin.notices');
        Route::post('/admin/notices', [NoticeController::class, 'store'])->name('notice.store');
        Route::delete('/admin/notices/{id}', [NoticeController::class, 'destroy'])->name('notice.destroy');

        // Advanced Attendance Administrative Management
        Route::post('/attendance/manual', [AttendanceController::class, 'storeManual'])->name('attendance.manual');
        Route::post('/attendance/{id}/update', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::delete('/attendance/{id}/delete', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
        Route::post('/attendance/toggle-remote/{userId}', [AttendanceController::class, 'toggleRemote'])->name('attendance.toggle_remote');
        Route::post('/attendance/settings', [AttendanceController::class, 'saveSettings'])->name('attendance.settings');
        Route::post('/admin/holidays', [AttendanceController::class, 'storeHoliday'])->name('attendance.holidays.store');
        Route::delete('/admin/holidays/{id}', [AttendanceController::class, 'destroyHoliday'])->name('attendance.holidays.destroy');
        Route::post('/attendance/purge-selfies', [AttendanceController::class, 'purgeSelfies'])->name('attendance.purge_selfies');
        Route::post('/attendance/send-report', [AttendanceController::class, 'sendEmailReport'])->name('attendance.send_report');
        Route::post('/admin/weekly-efficiency/generate', [AttendanceController::class, 'generateWeeklyReportAJAX'])->name('weekly_efficiency.generate');

        // Leave Administrative Management
        Route::post('/admin/leave/{id}/approve', [LeaveController::class, 'approve'])->name('admin.leave.approve');
        Route::post('/admin/leave/{id}/reject', [LeaveController::class, 'reject'])->name('admin.leave.reject');

        // Advanced Settings
        Route::get('/admin/settings/advanced', [SettingController::class, 'advanced'])->name('admin.settings.advanced');
        Route::post('/admin/settings/advanced', [SettingController::class, 'updateAdvanced'])->name('admin.settings.update');
    });

    /* -------- STAFF & ADMIN -------- */
    Route::middleware(['role:staff,admin'])->group(function () {
        // Attendance System Routes
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock_in');
        Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock_out');
        Route::post('/attendance/start-lunch', [AttendanceController::class, 'startLunch'])->name('attendance.start_lunch');
        Route::post('/attendance/end-lunch', [AttendanceController::class, 'endLunch'])->name('attendance.end_lunch');

        // Leave System Routes
        Route::post('/leave/request', [LeaveController::class, 'store'])->name('leave.request');
        Route::delete('/leave/{id}/cancel', [LeaveController::class, 'destroy'])->name('leave.destroy');

        Route::get('/staff/dashboard', [StaffController::class, 'dashboard'])->name('staff.dashboard');
        // GET /admin/tasks removed — handled by Filament TaskResource
        Route::post('/tasks/{id}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
        Route::get('/manage-documents', [DocumentController::class, 'index'])->name('documents.manage');
        Route::get('/client-documents/{id}', [DocumentController::class, 'viewClientDocuments'])->name('documents.view');
        Route::match(['delete', 'post'], '/delete-file/{id}', [DocumentController::class, 'destroy'])->name('file.delete');
        Route::post('/create-folder', [DocumentController::class, 'createFolder'])->name('folder.create');
        Route::get('/profile', [AdminController::class, 'profile'])->name('admin.profile');
        Route::post('/profile', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
        Route::post('/rename-file/{id}', [DocumentController::class, 'renameFile'])->name('file.rename');
        Route::post('/rename-folder/{id}', [DocumentController::class, 'renameFolder'])->name('folder.rename');
        Route::get('/upload-file', [DocumentController::class, 'create'])->name('file.form');
        Route::post('/upload-file', [DocumentController::class, 'store'])->name('file.upload');

        // Team Chat (Standalone Module)
        Route::get('/team-chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
        Route::get('/api/conversations', [App\Http\Controllers\ChatController::class, 'fetchConversations']);
        Route::get('/api/conversations/{id}/messages', [App\Http\Controllers\ChatController::class, 'fetchMessages']);
        Route::post('/api/conversations/{id}/messages', [App\Http\Controllers\ChatController::class, 'sendMessage']);
        Route::post('/api/conversations/group', [App\Http\Controllers\ChatController::class, 'createGroup']);
        Route::post('/api/conversations/dm', [App\Http\Controllers\ChatController::class, 'startDirectMessage']);
        Route::post('/api/messages/{id}/star', [App\Http\Controllers\ChatController::class, 'toggleStar']);
        Route::get('/api/messages/{id}/thread', [App\Http\Controllers\ChatController::class, 'fetchThread']);
        Route::get('/api/conversations/{id}/search', [App\Http\Controllers\ChatController::class, 'searchMessages']);
        Route::get('/api/staff/list', [App\Http\Controllers\ChatController::class, 'fetchStaff']);
        Route::post('/api/conversations/{id}/members', [App\Http\Controllers\ChatController::class, 'updateGroupMembers']);
        Route::delete('/api/conversations/{id}', [App\Http\Controllers\ChatController::class, 'archiveGroup']);
        Route::get('/api/chat/unread', [App\Http\Controllers\ChatController::class, 'getTotalUnreadCount'])->name('chat.unread');
        
        // Chat Enhancements: Delete, Forward, Pin, Starred, Clear
        Route::post('/api/messages/{id}/delete', [App\Http\Controllers\ChatController::class, 'deleteMessage']);
        Route::post('/api/messages/{id}/forward', [App\Http\Controllers\ChatController::class, 'forwardMessage']);
        Route::post('/api/messages/{id}/pin', [App\Http\Controllers\ChatController::class, 'togglePin']);
        Route::get('/api/conversations/{id}/starred', [App\Http\Controllers\ChatController::class, 'getStarredMessages']);
        Route::post('/api/conversations/{id}/clear', [App\Http\Controllers\ChatController::class, 'clearGroupChats']);
        
        // Push Notification Routes
        Route::post('/api/push/subscribe', [App\Http\Controllers\PushSubscriptionController::class, 'subscribe']);
        Route::post('/api/push/unsubscribe', [App\Http\Controllers\PushSubscriptionController::class, 'unsubscribe']);

        // Mailbox Module (IMAP — shared company inbox)
        Route::get('/mailbox', [App\Http\Controllers\MailboxController::class, 'index'])->name('mailbox.index');
        Route::get('/api/mailbox/inbox', [App\Http\Controllers\MailboxController::class, 'apiInbox'])->name('mailbox.api.inbox');
        Route::get('/api/mailbox/message/{uid}', [App\Http\Controllers\MailboxController::class, 'apiMessage'])->name('mailbox.api.message');
        Route::post('/api/mailbox/reply', [App\Http\Controllers\MailboxController::class, 'reply'])->name('mailbox.api.reply');
        Route::post('/api/mailbox/send', [App\Http\Controllers\MailboxController::class, 'send'])->name('mailbox.api.send');
        Route::post('/api/mailbox/star/{uid}', [App\Http\Controllers\MailboxController::class, 'toggleStar'])->name('mailbox.api.star');
        Route::delete('/api/mailbox/delete/{uid}', [App\Http\Controllers\MailboxController::class, 'delete'])->name('mailbox.api.delete');
        Route::post('/api/mailbox/spam/{uid}', [App\Http\Controllers\MailboxController::class, 'markAsSpam'])->name('mailbox.api.spam');
        
        // Custom Mailbox Accounts & Outbox Manager
        Route::get('/api/mailbox/accounts', [App\Http\Controllers\MailboxController::class, 'apiGetAccounts'])->name('mailbox.api.accounts.index');
        Route::post('/api/mailbox/accounts', [App\Http\Controllers\MailboxController::class, 'apiSaveAccount'])->name('mailbox.api.accounts.store');
        Route::delete('/api/mailbox/accounts/{id}', [App\Http\Controllers\MailboxController::class, 'apiDeleteAccount'])->name('mailbox.api.accounts.destroy');
        Route::get('/api/mailbox/outbox', [App\Http\Controllers\MailboxController::class, 'apiOutbox'])->name('mailbox.api.outbox');
        Route::post('/api/mailbox/summarize', [App\Http\Controllers\MailboxController::class, 'apiSummarize'])->name('mailbox.api.summarize');

        // Dynamic Website Gallery Management Workspace
        Route::get('/admin/gallery', [App\Http\Controllers\AdminGalleryController::class, 'index'])->name('admin.gallery.index');
        Route::post('/admin/gallery/upload', [App\Http\Controllers\AdminGalleryController::class, 'store'])->name('admin.gallery.store');
        Route::delete('/admin/gallery/delete', [App\Http\Controllers\AdminGalleryController::class, 'destroy'])->name('admin.gallery.destroy');
        Route::post('/admin/gallery/ai-adjust', [App\Http\Controllers\AdminGalleryController::class, 'aiAdjust'])->name('admin.gallery.ai_adjust');
        Route::post('/admin/gallery/reorder', [App\Http\Controllers\AdminGalleryController::class, 'reorder'])->name('admin.gallery.reorder');

        // Dynamic Website Staff Management Workspace
        Route::get('/admin/team', [App\Http\Controllers\AdminTeamController::class, 'index'])->name('admin.team.index');
        Route::get('/admin/team/create', [App\Http\Controllers\AdminTeamController::class, 'create'])->name('admin.team.create');
        Route::post('/admin/team/store', [App\Http\Controllers\AdminTeamController::class, 'store'])->name('admin.team.store');
        Route::get('/admin/team/{id}/edit', [App\Http\Controllers\AdminTeamController::class, 'edit'])->name('admin.team.edit');
        Route::post('/admin/team/{id}/update', [App\Http\Controllers\AdminTeamController::class, 'update'])->name('admin.team.update');
        Route::delete('/admin/team/{id}/delete', [App\Http\Controllers\AdminTeamController::class, 'destroy'])->name('admin.team.destroy');
        Route::post('/admin/team/reorder', [App\Http\Controllers\AdminTeamController::class, 'reorder'])->name('admin.team.reorder');

    });


    /* -------- CLIENT DASHBOARD -------- */
    Route::middleware(['role:client'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'clientDashboard'])->name('client.dashboard');
    });

    // Notice read - accessible by ALL authenticated users (clients, staff, admin)
    Route::post('/notices/{id}/read', [NoticeController::class, 'markAsRead'])->name('notice.read');

    Route::get('/track-download/{id}', [DocumentController::class, 'trackAndDownload'])->name('file.download');
    Route::get('/view-file/{id}', [DocumentController::class, 'viewInline'])->name('file.view');
    Route::get('/api/staff/summary', [TaskController::class, 'getStaffSummary'])->name('staff.summary');
    Route::post('/api/ping', [TaskController::class, 'pingPresence'])->name('api.ping');
    Route::post('/api/task/{id}/status', [TaskController::class, 'updateStatus'])->name('task.status.json');
    Route::get('/task/{id}', [TaskController::class, 'show'])->name('task.view');
    Route::post('/task/{id}/chat', [TaskController::class, 'sendMessage'])->name('task.chat');
    Route::get('/task/{id}/fetch-messages', [TaskController::class, 'fetchMessages'])->name('task.fetch');
    Route::get('/notifications/unread', [TaskController::class, 'checkUnread'])->name('notifications.unread');
    Route::get('/files/folders/{userId}', [DocumentController::class, 'getFolders']);
    
    // WhatsApp Standalone Web Portal Route
    Route::get('/portal/whatsapp', function() {
        return view('admin.whatsapp');
    })->name('whatsapp.portal');
    
    // Secure Administrative Tool: Recalculate historical working hours
    Route::get('/admin/recalculate-all-attendance-hours-securely-xyz', function() {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
        
        $records = \App\Models\Attendance::whereNotNull('clock_out')->with('user')->get();
        $html = "<!DOCTYPE html><html><head><title>Attendance Recalculator</title>";
        $html .= "<style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 30px; background: #f8fafc; color: #1e293b; }
            .container { max-width: 1200px; margin: 0 auto; background: #ffffff; padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border: 1px solid #e2e8f0; }
            h2 { color: #0f172a; margin-top: 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; font-weight: 700; }
            .stats { display: inline-block; background: #e0f2fe; color: #0369a1; padding: 8px 16px; border-radius: 9999px; font-weight: 600; font-size: 0.875rem; margin-bottom: 20px; }
            table { border-collapse: collapse; width: 100%; margin-top: 10px; }
            th, td { border-bottom: 1px solid #e2e8f0; padding: 12px 16px; text-align: left; font-size: 0.9rem; }
            th { background: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; tracking: 0.05em; }
            tr:hover { background: #f8fafc; }
            .pill { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
            .pill-old { background: #fee2e2; color: #991b1b; }
            .pill-new { background: #dcfce7; color: #166534; }
            .badge-lunch { background: #fef3c7; color: #92400e; }
        </style></head><body><div class='container'>";
        $html .= "<h2>Attendance Hours Recalculation & Sync Log</h2>";
        
        $count = 0;
        $tableRows = "";
        
        foreach ($records as $record) {
            $oldHours = $record->total_hours;
            
            $clockIn = $record->clock_in;
            $clockOut = $record->clock_out;
            $totalMinutes = $clockIn->diffInMinutes($clockOut);
            
            $lunchMinutes = 0;
            if ($record->lunch_duration) {
                $lunchMinutes = round($record->lunch_duration * 60);
            }
            
            $netMinutes = max(0, $totalMinutes - $lunchMinutes);
            $newHours = round($netMinutes / 60, 2);
            
            $record->total_hours = $newHours;
            $record->save();
            $count++;
            
            $lunchText = $record->lunch_duration ? "<span class='pill badge-lunch'>{$record->lunch_duration} hrs</span>" : "<span style='color:#cbd5e1;'>-</span>";
            
            $tableRows .= "<tr>";
            $tableRows .= "<td><strong>#{$record->id}</strong></td>";
            $tableRows .= "<td>" . e($record->user->name ?? 'Deleted Staff') . "</td>";
            $tableRows .= "<td>" . $record->date->format('d M, Y') . "</td>";
            $tableRows .= "<td>" . $clockIn->format('h:i A') . "</td>";
            $tableRows .= "<td>" . $clockOut->format('h:i A') . "</td>";
            $tableRows .= "<td style='text-align:center;'>{$lunchText}</td>";
            $tableRows .= "<td><span class='pill pill-old'>{$oldHours} hrs</span></td>";
            $tableRows .= "<td><span class='pill pill-new'>{$newHours} hrs</span></td>";
            $tableRows .= "</tr>";
        }
        
        $html .= "<div class='stats'>Successfully verified and recalculated {$count} logs!</div>";
        $html .= "<table><thead><tr><th>ID</th><th>Staff Name</th><th>Date</th><th>Clock In</th><th>Clock Out</th><th style='text-align:center;'>Lunch Break</th><th>Original Hours</th><th>Recalculated Hours</th></tr></thead><tbody>";
        $html .= $tableRows ?: "<tr><td colspan='8' style='text-align:center;color:#64748b;'>No checked-out attendance records found to process.</td></tr>";
        $html .= "</tbody></table></div></body></html>";
        
        return response($html);
    });
});

Route::get('/emergency-reset', function() {
    return view('admin.emergency-reset');
})->name('emergency-reset');

Route::post('/emergency-reset/send', function() {
    $otp = rand(100000, 999999);
    Illuminate\Support\Facades\Cache::put('admin_emergency_otp', $otp, now()->addMinutes(15));
    
    try {
        Illuminate\Support\Facades\Mail::to('info@gayatrient.com')->send(new App\Mail\AdminEmergencyResetOtpMail($otp));
        return back()->with('otp_sent', true)->with('success', 'OTP has been securely sent to info@gayatrient.com. It is valid for 15 minutes.');
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to send OTP: ' . $e->getMessage());
    }
})->name('emergency-reset.send');

Route::post('/emergency-reset/verify', function(Illuminate\Http\Request $request) {
    $request->validate([
        'otp' => 'required|digits:6',
        'password' => 'required|min:8|confirmed'
    ]);

    $cachedOtp = Illuminate\Support\Facades\Cache::get('admin_emergency_otp');
    
    if (!$cachedOtp || $cachedOtp != $request->otp) {
        return back()->with('otp_sent', true)->with('error', 'Invalid or expired OTP. Please request a new one.');
    }

    $admin = App\Models\User::where('role', 'admin')->first();
    if (!$admin) {
        return back()->with('error', 'Critical Error: Admin user not found in the database.');
    }

    $admin->password = Illuminate\Support\Facades\Hash::make($request->password);
    $admin->save();

    Illuminate\Support\Facades\Cache::forget('admin_emergency_otp');

    return redirect()->route('login')->with('success', 'Admin password has been securely reset! You may now login.');
})->name('emergency-reset.verify');

