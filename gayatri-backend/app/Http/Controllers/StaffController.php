<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\SiteNotice;

class StaffController extends Controller
{
    public function dashboard() {
        $user = Auth::user();

        if (!$user->isStaff()) {
            abort(403, 'Unauthorized access to Staff Dashboard.');
        }

        // 1. Pending Tasks (Count aur List)
        $pendingTasks = $user->tasks()
                            ->where('status', '!=', 'completed')
                            ->orderBy('due_date', 'asc')
                            ->get();

        // 2. Completed Tasks Count
        $completedCount = $user->tasks()
                              ->where('status', 'completed')
                              ->count();

        // 2a. Completed Tasks List (History)
        $completedTasks = $user->tasks()
                              ->where('status', 'completed')
                              ->latest()
                              ->limit(10)
                              ->get();

        // 3. Notices (New System)
        $notices = SiteNotice::active()
            ->where(function ($q) use ($user) {
                $q->where('target_type', 'all')
                  ->orWhere('target_type', 'staff')
                  ->orWhereHas('users', function ($uq) use ($user) {
                      $uq->where('users.id', $user->id);
                  });
            })
            ->with(['users' => function ($q) use ($user) {
                $q->where('users.id', $user->id);
            }])
            ->latest()
            ->get();

        $unreadNoticeCount = $notices->filter(function($n) use ($user) {
            $pivot = $n->users->where('id', $user->id)->first()?->pivot;
            return $pivot ? !$pivot->is_read : true;
        })->count();

        return view('staff.dashboard', compact('user', 'pendingTasks', 'completedCount', 'completedTasks', 'notices', 'unreadNoticeCount'));
    }
}