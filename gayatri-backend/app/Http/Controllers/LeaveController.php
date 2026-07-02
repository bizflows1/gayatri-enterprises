<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leave;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Store a newly created leave request in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $startDate = Carbon::parse($request->start_date)->toDateString();
        $endDate = Carbon::parse($request->end_date)->toDateString();

        // 1. Strictly enforce request must be made BEFORE 12:00 AM (midnight) of the start date
        $startOfLeave = Carbon::parse($startDate)->startOfDay();
        if (now()->greaterThanOrEqualTo($startOfLeave)) {
            return back()->with('error', 'Leaves must be applied before 12:00 AM (midnight) of the leave start date.');
        }

        // 2. Prevent overlapping leave requests (pending or approved)
        $overlap = Leave::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->exists();

        if ($overlap) {
            return back()->with('error', 'You have already applied for a pending or approved leave during these dates.');
        }

        // 3. Create the Leave request
        Leave::create([
            'user_id' => $user->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Leave request submitted successfully! Awaiting administrator approval.');
    }

    /**
     * Admin Approve Leave Handler
     */
    public function approve(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $leave = Leave::findOrFail($id);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending leave requests can be approved.');
        }

        // Strictly verify the 12:00 AM (midnight) constraint
        $startOfLeave = Carbon::parse($leave->start_date)->startOfDay();
        if (now()->greaterThanOrEqualTo($startOfLeave)) {
            $leave->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => 'Automatically rejected: Passed the 12:00 AM deadline of the leave date before approval.'
            ]);
            return back()->with('error', 'Approval failed: 12:00 AM (midnight) of the leave start date has already passed. The request has been marked as rejected.');
        }

        // Update leave status to approved
        $leave->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        // Auto-write 'leave' status records into the attendances table for each day in the range
        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateStr = $date->toDateString();
            
            // Standard office hours (matching settings standard starts or defaults)
            $clockInTime = Carbon::parse($dateStr . ' 10:00:00');
            $clockOutTime = Carbon::parse($dateStr . ' 18:30:00');
            
            Attendance::updateOrCreate(
                [
                    'user_id' => $leave->user_id,
                    'date' => $dateStr
                ],
                [
                    'clock_in' => $clockInTime->format('Y-m-d H:i:s'),
                    'clock_out' => $clockOutTime->format('Y-m-d H:i:s'),
                    'total_hours' => 8.50,
                    'overtime_hours' => 0.00,
                    'status' => 'leave',
                    'auto_logged_out' => false,
                    'notes' => 'Approved Leave: ' . $leave->reason,
                    'admin_remarks' => 'Leave approved and logged by Admin ' . Auth::user()->name
                ]
            );
        }

        return back()->with('success', 'Leave request approved successfully, and corresponding attendance logs have been populated.');
    }

    /**
     * Admin Reject Leave Handler
     */
    public function reject(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $leave = Leave::findOrFail($id);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending leave requests can be rejected.');
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason
        ]);

        return back()->with('success', 'Leave request has been rejected.');
    }

    /**
     * Cancel Leave Handler (Staff can cancel their own pending leaves)
     */
    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->user_id !== Auth::id()) {
            abort(403);
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending leave requests can be cancelled.');
        }

        $leave->delete();

        return back()->with('success', 'Leave request has been successfully cancelled.');
    }
}
