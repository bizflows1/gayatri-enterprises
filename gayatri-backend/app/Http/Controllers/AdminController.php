<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Document;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /* =========================================================
       1. DASHBOARD
    ========================================================= */

    public function dashboard()
    {
        $stats = [
            'total_users'     => User::count(),
            'active_users'    => User::where('is_active', 1)->orWhereNull('is_active')->count(),
            'new_users'       => User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'blocked_users'   => User::where('is_active', 0)->count(),
            'total_clients'   => User::where('role', 'client')->count(),
            'total_documents' => Document::count(),
            'pending_tasks'   => \App\Models\Task::where('status', 'pending')->count(),
        ];

        $recent_logs = ActivityLog::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_logs'));
    }

    public function clientDashboard() {
        $user = Auth::user(); 
        
        // Case-insensitive role check
        if(strtolower($user->role) !== 'client') {
            return redirect()->route('home');
        }
        
        // Initialize all variables with default empty collections for robustness
        $folders = collect();
        $rootDocuments = collect();
        $recentDocuments = collect();
        $recentViews = collect();
        $notices = collect();

        try {
            // 1. Fetch Folders
            if (class_exists('\App\Models\Folder')) {
                $folders = \App\Models\Folder::where('user_id', $user->id)
                    ->whereNull('parent_id')
                    ->orderBy('name', 'desc')
                    ->get();
            }

            // 2. Fetch Root Documents
            $rootDocuments = Document::where('user_id', $user->id)
                ->whereNull('folder_id')
                ->orderBy('created_at', 'desc')
                ->get();

            // 3. Fetch Recent Documents
            $recentDocuments = Document::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            // 4. Fetch Activity Logs
            if (class_exists('\App\Models\ActivityLog')) {
                $recentViews = ActivityLog::where('user_id', $user->id)
                                          ->whereIn('action', ['Viewed Document', 'Downloaded Document'])
                                          ->orderBy('created_at', 'desc')
                                          ->take(5)
                                          ->get();
            }

            // 5. Fetch Notices
            if (class_exists('\App\Models\SiteNotice')) {
                $notices = \App\Models\SiteNotice::active()
                    ->where(function ($q) use ($user) {
                        $q->where('target_type', 'all')
                          ->orWhereHas('users', function ($uq) use ($user) {
                              $uq->where('users.id', $user->id);
                          });
                    })
                    ->with(['users' => function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    }])
                    ->latest()
                    ->get();
            }
        } catch (\Exception $e) {
            \Log::error("Client Dashboard Data Fetch Error: " . $e->getMessage());
        }

        // Pre-calculate unread count safely
        $unreadNoticeCount = $notices->filter(function($n) {
            return ($n->pivot && $n->pivot->user_id == Auth::id()) ? !$n->pivot->is_read : true; // Assume true for global if no specific pivot record
        })->count();

        return response()
            ->view('client.dashboard', compact('user', 'folders', 'rootDocuments', 'recentDocuments', 'recentViews', 'notices', 'unreadNoticeCount'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /* =========================================================
       2. NOTICE
    ========================================================= */

    public function notices()
    {
        $notices = \App\Models\SiteNotice::withCount('users')->latest()->paginate(10);
        return view('admin.notices.index', compact('notices'));
    }

    public function createNotice()
    {
        $users = User::where('role', 'client')->get();
        $staff = User::where('role', 'staff')->get();
        return view('admin.notices.create', compact('users', 'staff'));
    }

    /* =========================================================
       3. ACTIVITY LOGS
    ========================================================= */

    public function activityLogs(Request $request)
    {
        $query = ActivityLog::with('user');

        // Filter by Activity Type
        if ($request->filled('activity_type') && $request->activity_type !== 'all') {
            $query->where('action', $request->activity_type);
        }

        // Filter by User Role
        if ($request->filled('role_filter') && $request->role_filter !== 'all') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('role', $request->role_filter);
            });
        }

        // Fetch distinct log action types for dynamic dropdown options
        $actionTypes = ActivityLog::distinct()->orderBy('action')->pluck('action');

        $logs = $query->latest()->paginate(30)->withQueryString();

        return view('admin.activity-logs', compact('logs', 'actionTypes'));
    }

    /* =========================================================
       4. PROFILE & CLIENT LOGS - Add here as needed
    ========================================================= */

    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:8',
            'profile_photo' => 'nullable|mimes:jpeg,jpg,png,gif,svg,webp|max:2048', // Max 2MB
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        // Handle photo removal flag
        if ($request->input('remove_photo') === '1') {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $user->profile_photo = null;
        }

        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $user->profile_photo = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }

    /* =========================================================
       5. MESSAGES / INQUIRIES
    ========================================================= */

    public function messages()
    {
        $messages = \App\Models\Contact::latest()->paginate(10);
        return view('admin.messages', compact('messages'));
    }

    public function deleteMessages(Request $request)
    {
        if ($request->has('delete_all') && $request->input('delete_all') == '1') {
            \App\Models\Contact::truncate();
            return back()->with('success', 'All inquiries deleted successfully.');
        }

        $ids = $request->input('ids');
        if ($ids && is_array($ids)) {
            \App\Models\Contact::whereIn('id', $ids)->delete();
            return back()->with('success', 'Selected inquiries deleted successfully.');
        }

        return back()->with('error', 'No inquiries selected for deletion.');
    }
}