<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Holiday;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\MonthlyAttendanceReport;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Get Attendance Settings from JSON Storage
     */
    private static function getSettings()
    {
        $path = storage_path('app/attendance_settings.json');
        if (file_exists($path)) {
            $settings = json_decode(file_get_contents($path), true);
        } else {
            $settings = [];
        }

        // Return with defaults if keys are missing
        return array_merge([
            'office_ip' => '127.0.0.1', // Default local loopback, change in settings
            'latitude' => '28.6139',
            'longitude' => '77.2090',
            'radius' => '100', // 100 meters
            'office_start_time' => '10:00', // Cutoff for Late status
            'grace_time_minutes' => '15', // Grace buffer minutes
            'standard_exit_time' => '18:30', // Standard Exit Time (6:30 PM)
            'auto_logout_time' => '23:59', // Default auto logout time (11:59 PM)
            'overtime_comp_off_threshold' => '8.0', // 8 Hours of overtime = 1 Comp-off day
            'holidays' => '', // Comma-separated list of holidays, e.g. "2026-05-01,2026-08-15"
        ], $settings);
    }

    /**
     * Check and process automatic checkouts for staff who didn't clock out yesterday
     */
    private static function checkAutoCheckouts()
    {
        $settings = self::getSettings();
        $autoLogoutTime = $settings['auto_logout_time'] ?? '23:59';
        $appTimezone = config('app.timezone', 'Asia/Kolkata');
        
        $activeLogs = Attendance::whereNull('clock_out')
            ->where('auto_logged_out', false)
            ->get();
            
        $now = Carbon::now($appTimezone);
            
        foreach ($activeLogs as $log) {
            // Build the auto-logout datetime in IST
            $logDate = Carbon::parse($log->date->toDateString() . ' ' . $autoLogoutTime, $appTimezone);
            
            // If autoLogoutTime is early-AM (e.g. 02:00), it's actually the next calendar day
            $hour = (int) substr($autoLogoutTime, 0, 2);
            if ($hour < 12) {
                $logDate->addDay();
            }
            
            if ($now->greaterThan($logDate)) {
                $exitTimeStr = $settings['standard_exit_time'] ?? '18:30';
                $clockOutTime = Carbon::parse($log->date->toDateString() . ' ' . $exitTimeStr, $appTimezone);
                $clockIn = $log->clock_in;

                // Calculate total raw duration of shift in minutes
                $totalMinutes = (int) max(0, $clockIn->diffInMinutes($clockOutTime));
                
                // Subtract lunch break duration if recorded
                $lunchMinutes = 0;
                if ($log->lunch_duration) {
                    $lunchMinutes = round($log->lunch_duration * 60);
                }
                
                // Subtract lunch minutes from total minutes to compute net hours worked
                $netWorkedMinutes = max(0, $totalMinutes - $lunchMinutes);
                $totalHours = round($netWorkedMinutes / 60, 2);

                // Compute overtime if any (or 0)
                $log->clock_out = $clockOutTime;
                $log->total_hours = $totalHours;
                $overtimeHours = self::calculateOvertimeHours($log, $settings);

                $log->update([
                    'clock_out' => $clockOutTime->format('Y-m-d H:i:s'),
                    'total_hours' => $totalHours,
                    'overtime_hours' => $overtimeHours,
                    'auto_logged_out'=> true,
                    'work_log'       => $log->work_log
                                        ? $log->work_log . "\n• System Auto-Checkout: Missing manual checkout. Work log not submitted."
                                        : "• System Auto-Checkout: Missing manual checkout. Work log not submitted.",
                    'notes'          => $log->notes
                                        ? $log->notes . " (System Auto-Checkout at 6:30 PM)"
                                        : "System Auto-Checkout at 6:30 PM",
                ]);
            }
        }

        // 2. Auto-create Absent records for staff who did not clock in and have no approved leave
        $startDateObj = Carbon::now($appTimezone)->startOfMonth();
        $today = Carbon::now($appTimezone);
        
        // If it's today after 6:30 PM (18:30), check up to today, otherwise up to yesterday
        $endDateObj = $today->hour >= 18 || ($today->hour == 18 && $today->minute >= 30) 
            ? $today->copy() 
            : $today->copy()->subDay();

        if ($startDateObj->lte($endDateObj)) {
            // Get all holidays in this range
            $holidays = Holiday::whereBetween('date', [$startDateObj->toDateString(), $endDateObj->toDateString()])
                ->pluck('date')
                ->map(fn($d) => $d instanceof Carbon ? $d->toDateString() : substr($d, 0, 10))
                ->toArray();

            // Get all staff users
            $staffUsers = User::where('role', 'staff')->get();

            // Get all leaves in this range that are approved
            $approvedLeaves = collect();
            if (\Illuminate\Support\Facades\Schema::hasTable('leaves')) {
                $approvedLeaves = \App\Models\Leave::where('status', 'approved')
                    ->where(function($q) use ($startDateObj, $endDateObj) {
                        $q->whereBetween('start_date', [$startDateObj->toDateString(), $endDateObj->toDateString()])
                          ->orWhereBetween('end_date', [$startDateObj->toDateString(), $endDateObj->toDateString()])
                          ->orWhere(function($qi) use ($startDateObj, $endDateObj) {
                              $qi->where('start_date', '<=', $startDateObj->toDateString())
                                 ->where('end_date', '>=', $endDateObj->toDateString());
                          });
                    })
                    ->get();
            }

            // Get all existing attendance logs in this range
            $existingLogs = Attendance::whereBetween('date', [$startDateObj->toDateString(), $endDateObj->toDateString()])
                ->get()
                ->groupBy('user_id');

            // Iterate over each date in the range
            for ($date = $startDateObj->copy(); $date->lte($endDateObj); $date->addDay()) {
                $dateStr = $date->toDateString();
                
                // Skip Sundays
                if ($date->isSunday()) {
                    continue;
                }
                
                // Skip Holidays
                if (in_array($dateStr, $holidays)) {
                    continue;
                }

                foreach ($staffUsers as $staff) {
                    // Check if there is an existing log
                    $staffLogs = $existingLogs->get($staff->id) ?? collect();
                    $hasLog = $staffLogs->contains(function($log) use ($dateStr) {
                        $logDate = $log->date instanceof Carbon ? $log->date->toDateString() : substr($log->date, 0, 10);
                        return $logDate === $dateStr;
                    });

                    if (!$hasLog) {
                        // Check if they have an approved leave for this date
                        $isOnLeave = $approvedLeaves->contains(function($leave) use ($staff, $dateStr) {
                            if ($leave->user_id !== $staff->id) return false;
                            $start = $leave->start_date instanceof Carbon ? $leave->start_date->toDateString() : substr($leave->start_date, 0, 10);
                            $end = $leave->end_date instanceof Carbon ? $leave->end_date->toDateString() : substr($leave->end_date, 0, 10);
                            return $dateStr >= $start && $dateStr <= $end;
                        });

                        if (!$isOnLeave) {
                            // Create Absent record safely (prevent duplicates via firstOrCreate)
                            Attendance::firstOrCreate(
                                [
                                    'user_id' => $staff->id,
                                    'date' => $dateStr
                                ],
                                [
                                    'clock_in' => Carbon::parse($dateStr . ' 10:00:00'),
                                    'clock_out' => null,
                                    'status' => 'absent',
                                    'total_hours' => 0.00,
                                    'overtime_hours' => 0.00,
                                    'notes' => 'System Auto-Absent: Did not clock in and no approved leave recorded.',
                                    'auto_logged_out' => true
                                ]
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Helper to calculate overtime hours for an attendance record
     */
    private static function calculateOvertimeHours($attendance, $settings)
    {
        if (!$attendance->clock_in || !$attendance->clock_out) {
            return 0.00;
        }

        $date = Carbon::parse($attendance->date);
        $isSunday = $date->isSunday();
        
        // Check if date is in holiday list
        $isHoliday = false;
        $holidays = array_map('trim', explode(',', $settings['holidays'] ?? ''));
        if (in_array($date->toDateString(), $holidays)) {
            $isHoliday = true;
        }

        // If Sunday or Holiday, 100% of worked hours is overtime!
        if ($isSunday || $isHoliday) {
            return floatval($attendance->total_hours);
        }

        // For regular days, overtime is work done after the standard exit time
        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::parse($attendance->clock_out);
        
        // Standard exit datetime on that specific date
        $standardExitStr = $settings['standard_exit_time'] ?? '18:30';
        $standardExit = Carbon::parse($attendance->date->toDateString() . ' ' . $standardExitStr);

        if ($clockOut->gt($standardExit)) {
            // Net overtime minutes = duration between standard exit and clock out
            // What if they clocked in after standard exit? Then it's between clock-in and clock-out!
            $startOfOT = $clockIn->gt($standardExit) ? $clockIn : $standardExit;
            $otMinutes = $clockOut->diffInMinutes($startOfOT);
            
            return round($otMinutes / 60, 2);
        }

        return 0.00;
    }

    /**
     * Unified Attendance Dashboard (Index)
     */
    public function index(Request $request)
    {
        self::checkAutoCheckouts();
        $user = Auth::user();
        $settings = self::getSettings();
        $holidaysList = Holiday::orderBy('date', 'asc')->get();

        if ($user->isAdmin()) {
            // ADMIN WORKSPACE
            
            // 1. Fetch live metrics for today
            $todayDate = Carbon::today()->toDateString();
            
            $totalStaff = User::where('role', 'staff')->count();
            
            $todayLogs = Attendance::with('user')
                ->whereDate('date', $todayDate)
                ->get();

            $presentToday = $todayLogs->whereIn('status', ['present', 'grace', 'late', 'half_day'])->unique('user_id')->count();
            $lateToday = $todayLogs->where('status', 'late')->unique('user_id')->count();
            $activeClockedIn = $todayLogs->whereNull('clock_out')->unique('user_id')->count();
            $absentToday = max(0, $totalStaff - $presentToday);

            // 2. Fetch all staff members for dropdowns & controls
            $staffList = User::where('role', 'staff')
                ->orderBy('name', 'asc')
                ->get();

            // 3. Build filtered logs list
            $query = Attendance::with('user')->orderBy('date', 'desc')->orderBy('clock_in', 'desc');

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            // Filter by date range (default to current month)
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', Carbon::now()->toDateString());

            $query->whereBetween('date', [$startDate, $endDate]);

            $logs = $query->paginate(20)->withQueryString();

            // 4. Gather Weekly Performance Analytics for Chart.js (past 7 days)
            $days = collect();
            for ($i = 6; $i >= 0; $i--) {
                $days->push(Carbon::today()->subDays($i));
            }

            $past7DaysLogs = Attendance::whereBetween('date', [
                Carbon::today()->subDays(6)->toDateString(),
                Carbon::today()->toDateString()
            ])->get();

            $chartLabels = [];
            $chartHours = [];
            $chartLogsCount = [];

            foreach ($days as $day) {
                $dateStr = $day->toDateString();
                $chartLabels[] = $day->format('D d M');

                $dayLogs = $past7DaysLogs->where('date', $dateStr);
                $chartHours[] = round($dayLogs->sum('total_hours'), 2);

                $logsCount = 0;
                foreach ($dayLogs as $log) {
                    if ($log->work_log) {
                        $lines = explode("\n", $log->work_log);
                        foreach ($lines as $line) {
                            if (trim($line)) {
                                $logsCount++;
                            }
                        }
                    }
                }
                    $chartLogsCount[] = $logsCount;
                }
            $weeklyReports = collect();
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('weekly_efficiency_reports')) {
                    $weeklyReports = \App\Models\WeeklyEfficiencyReport::orderBy('created_at', 'desc')->get();
                }
            } catch (\Exception $e) {
                // Keep it empty if there is any database exception
            }

            $pendingLeaves = [];
            $leavesHistory = [];
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('leaves')) {
                    $pendingLeaves = \App\Models\Leave::with('user')->where('status', 'pending')->orderBy('created_at', 'desc')->get();
                    $leavesHistory = \App\Models\Leave::with(['user', 'approver'])
                        ->whereIn('status', ['approved', 'rejected'])
                        ->orderBy('updated_at', 'desc')
                        ->take(30)
                        ->get();
                }
            } catch (\Exception $e) {
                // Ignore if table doesn't exist
            }

            return view('attendance.index', compact(
                'settings',
                'presentToday',
                'lateToday',
                'activeClockedIn',
                'absentToday',
                'staffList',
                'logs',
                'startDate',
                'endDate',
                'chartLabels',
                'chartHours',
                'chartLogsCount',
                'weeklyReports',
                'pendingLeaves',
                'leavesHistory',
                'holidaysList'
            ));

        } else {
            // STAFF WORKSPACE
            $todayDate = Carbon::today()->toDateString();

            // 1. Fetch today's or the active/open attendance record for this staff member
            $latestLog = Attendance::where('user_id', $user->id)
                ->orderBy('date', 'desc')
                ->orderBy('clock_in', 'desc')
                ->first();

            $todayLog = null;
            if ($latestLog) {
                // If it matches today's date, or if it is an open check-in that hasn't been clocked out yet,
                // we keep it active so the employee can clock out robustly.
                if ($latestLog->date->toDateString() === $todayDate || (!$latestLog->clock_out && !$latestLog->auto_logged_out)) {
                    $todayLog = $latestLog;
                }
            }

            // 2. Monthly logs for history
            $currentMonthStart = Carbon::now()->startOfMonth()->toDateString();
            $currentMonthEnd = Carbon::now()->endOfMonth()->toDateString();

            $monthlyLogs = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$currentMonthStart, $currentMonthEnd])
                ->orderBy('date', 'desc')
                ->get();

            // Generate list of dates from start of month to today for full calendar display
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now();
            $monthlyDates = [];
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $monthlyDates[] = $date->copy();
            }
            $monthlyDates = array_reverse($monthlyDates);

            // 3. Calculate Monthly Summary Stats (counting unique dates)
            $totalPresent = $monthlyLogs->whereIn('status', ['present', 'grace', 'late', 'half_day'])
                ->map(fn($log) => $log->date->toDateString())
                ->unique()
                ->count();

            $totalLate = $monthlyLogs->where('status', 'late')
                ->map(fn($log) => $log->date->toDateString())
                ->unique()
                ->count();
            $totalHalfDay = $monthlyLogs->where('status', 'half_day')->count();
            $totalHours = $monthlyLogs->sum('total_hours');
            $totalOvertime = $monthlyLogs->sum('overtime_hours');

            // Calculate Comp-Off Days
            $threshold = floatval($settings['overtime_comp_off_threshold'] ?? 8.0);
            $compOffDays = $threshold > 0 ? round($totalOvertime / $threshold, 2) : 0;

            $myLeaves = [];
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('leaves')) {
                    $myLeaves = \App\Models\Leave::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
                }
            } catch (\Exception $e) {
                // Ignore if table doesn't exist
            }

            return view('attendance.index', compact(
                'settings',
                'todayLog',
                'monthlyLogs',
                'monthlyDates',
                'totalPresent',
                'totalLate',
                'totalHalfDay',
                'totalHours',
                'totalOvertime',
                'compOffDays',
                'myLeaves',
                'holidaysList'
            ));
        }
    }

    /**
     * Staff Clock-In Handler
     */
    public function clockIn(Request $request)
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return back()->with('error', 'Admins are not required to mark daily attendance.');
        }

        $settings = self::getSettings();
        $todayDate = Carbon::today()->toDateString();

        // 1. Enforce single daily attendance record per user
        $existingLog = Attendance::where('user_id', $user->id)
            ->whereDate('date', $todayDate)
            ->first();

        if ($existingLog) {
            return back()->with('error', 'You have already marked your attendance for today.');
        }

        // 2. NETWORK & LOCATION SECURITY CHECK
        $allowedIps = array_map('trim', explode(',', $settings['office_ip']));
        $isOfficeIp = in_array($request->ip(), $allowedIps);
        
        $isWithinGeofence = false;
        $distance = null;
        $userLat = $request->input('latitude');
        $userLng = $request->input('longitude');

        // Check physical distance if GPS coordinates are sent by client device
        if ($userLat && $userLng && !empty($settings['latitude']) && !empty($settings['longitude'])) {
            $distance = self::calculateDistance(
                $userLat,
                $userLng,
                $settings['latitude'],
                $settings['longitude']
            );
            // Fallback requirement: Must be within 100 meters of the office coordinates!
            $isWithinGeofence = ($distance <= 100);
        }

        // If remote mode is OFF, user must match either:
        // A. The Office Wi-Fi IP network
        // B. OR be physically within 100 meters of the saved office coordinates!
        if (!$user->allow_remote_attendance && !$isOfficeIp && !$isWithinGeofence) {
            $distanceText = ($distance !== null) ? round($distance, 1) . ' meters away' : 'unknown distance (GPS location not allowed/disabled)';
            return back()->with('error', "Verification failed! You are not on the Office Wi-Fi, and your GPS shows you are {$distanceText} (must be within 100 meters). Please connect to the Wi-Fi or step inside the office.");
        }

        // 3. PROCESSS SELFIE PHOTO (Base64 webcam image string)
        $selfiePath = null;
        if ($request->filled('selfie')) {
            try {
                $image_parts = explode(";base64,", $request->input('selfie'));
                if (count($image_parts) === 2) {
                    $image_base64 = base64_decode($image_parts[1]);
                    $filename = 'selfies/' . $user->id . '_' . time() . '.jpg';
                    
                    // Save to public disk
                    Storage::disk('public')->put($filename, $image_base64);
                    $selfiePath = $filename;
                }
            } catch (\Exception $e) {
                \Log::error('Selfie saving failed: ' . $e->getMessage());
            }
        }

        // If camera is required but failed/not captured
        if (!$selfiePath) {
            return back()->with('error', 'Selfie camera capture required to verify identity. Please allow camera permissions.');
        }

        // 4. DETERMINE LATE STATUS
        $appTimezone = config('app.timezone', 'Asia/Kolkata');
        $clockInTime = Carbon::now($appTimezone);

        // If this is a subsequent check-in of the day, inherit the status of their first check-in today
        $firstLogToday = Attendance::where('user_id', $user->id)
            ->whereDate('date', $todayDate)
            ->orderBy('clock_in', 'asc')
            ->first();

        if ($firstLogToday) {
            $status = $firstLogToday->status;
        } else {
            // This is the first check-in of the day, determine status based on cutoff time and grace time buffer
            $cutoffTime = Carbon::createFromFormat('H:i', $settings['office_start_time'], $appTimezone);
            $graceTimeMins = intval($settings['grace_time_minutes'] ?? 15);
            $graceEndTime = $cutoffTime->copy()->addMinutes($graceTimeMins);

            $clockInStr = $clockInTime->format('H:i');
            $cutoffStr = $cutoffTime->format('H:i');
            $graceEndStr = $graceEndTime->format('H:i');

            if ($clockInStr <= $cutoffStr) {
                $status = 'present';
            } elseif ($clockInStr <= $graceEndStr) {
                $status = 'grace';
            } else {
                $status = 'late';
            }
        }

        // 5. CREATE THE DAILY ATTENDANCE RECORD
        Attendance::create([
            'user_id' => $user->id,
            'date' => $todayDate,
            'clock_in' => $clockInTime->format('Y-m-d H:i:s'),
            'clock_out' => null,
            'total_hours' => null,
            'status' => $status,
            'clock_in_ip' => $request->ip(),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'selfie_photo' => $selfiePath,
            'notes' => $request->input('notes'),
        ]);

        return back()->with('success', 'Clock-In successful! Have a great productive day ahead!');
    }

    /**
     * Staff Clock-Out Handler
     */
    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $todayDate = Carbon::today()->toDateString();

        // 1. Find the latest open check-in log (timezone-robust)
        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->orderBy('date', 'desc')
            ->orderBy('clock_in', 'desc')
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Clock-In record not found, or you have already clocked out.');
        }

        // Validate Daily 3-Bullet-Point Work Log inputs
        $request->validate([
            'tasks' => 'required|array|min:3',
            'tasks.*' => 'required|string|min:10|max:500',
        ], [
            'tasks.min' => 'You must enter at least 3 tasks to clock out.',
            'tasks.*.required' => 'Each task description is required.',
            'tasks.*.min' => 'Each task must be at least 10 characters long.',
        ]);

        $tasks = array_map('trim', $request->input('tasks'));
        $workLog = implode("\n", array_map(function($task) {
            return "• " . $task;
        }, $tasks));

        // 2. UPDATE RECORD WITH OUT DETAILS
        $appTimezone = config('app.timezone', 'Asia/Kolkata');
        $clockOutTime = Carbon::now($appTimezone);
        $clockIn = $attendance->clock_in;

        // Auto-close lunch break if they clock out directly during lunch break
        if ($attendance->lunch_start && !$attendance->lunch_end) {
            $attendance->lunch_end = $clockOutTime;
            $lunchMinutes = (int) max(0, $attendance->lunch_start->diffInMinutes($clockOutTime));
            $attendance->lunch_duration = round($lunchMinutes / 60, 2);
            $attendance->save();
        }

        // Calculate total raw duration of shift in minutes
        $totalMinutes = (int) max(0, $clockIn->diffInMinutes($clockOutTime));

        // Calculate lunch break duration if recorded
        $lunchMinutes = 0;
        if ($attendance->lunch_start && $attendance->lunch_end) {
            $lunchMinutes = (int) max(0, $attendance->lunch_start->diffInMinutes($attendance->lunch_end));
        }

        // Subtract lunch minutes from total minutes to compute net hours worked
        $netWorkedMinutes = max(0, $totalMinutes - $lunchMinutes);
        $totalHours = round($netWorkedMinutes / 60, 2);

        // Temporary updates to the model instance for the overtime calculator
        $attendance->clock_out = $clockOutTime->format('Y-m-d H:i:s');
        $attendance->total_hours = $totalHours;
        $settings = self::getSettings();
        $overtimeHours = self::calculateOvertimeHours($attendance, $settings);

        $attendance->update([
            'clock_out' => $clockOutTime->format('Y-m-d H:i:s'),
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'clock_out_ip' => $request->ip(),
            'work_log' => $workLog,
        ]);

        return back()->with('success', 'Clock-Out successful! Thank you for your work today.');
    }

    /**
     * Admin Manual Logging Handler
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,grace,late,half_day,absent,leave',
            'clock_in' => 'nullable|string',
            'clock_out' => 'nullable|string',
        ]);

        $userId = $request->user_id;
        $date = Carbon::parse($request->date)->toDateString();

        // Check if a record already exists for this staff on this date
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $date)
            ->first();

        $clockIn = null;
        $clockOut = null;
        $totalHours = null;
        $overtimeHours = null;
        $settings = self::getSettings();
        $appTimezone = config('app.timezone', 'Asia/Kolkata');

        if ($request->status !== 'absent') {
            if ($request->filled('clock_in')) {
                $clockIn = Carbon::parse($date . ' ' . $request->clock_in, $appTimezone);
            } else {
                $clockIn = Carbon::parse($date . ' ' . $settings['office_start_time'], $appTimezone);
            }

            if ($request->filled('clock_out')) {
                $clockOut = Carbon::parse($date . ' ' . $request->clock_out, $appTimezone);
                $totalMinutes = $clockIn->diffInMinutes($clockOut);
                
                // Subtract lunch break duration if recorded
                $lunchMinutes = 0;
                if ($attendance && $attendance->lunch_duration) {
                    $lunchMinutes = round($attendance->lunch_duration * 60);
                }
                
                $netMinutes = max(0, $totalMinutes - $lunchMinutes);
                $totalHours = round($netMinutes / 60, 2);

                // Calculate Overtime
                $tempAtt = new Attendance([
                    'date' => $date,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'total_hours' => $totalHours
                ]);
                $overtimeHours = self::calculateOvertimeHours($tempAtt, $settings);
            }
        }

        $data = [
            'user_id' => $userId,
            'date' => $date,
            'clock_in' => $clockIn ? $clockIn->format('Y-m-d H:i:s') : Carbon::parse($date . ' 10:00', $appTimezone)->format('Y-m-d H:i:s'),
            'clock_out' => $clockOut ? $clockOut->format('Y-m-d H:i:s') : null,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'status' => $request->status,
            'notes' => $request->input('notes', 'Manually logged by Admin'),
            'admin_remarks' => $request->input('admin_remarks'),
            'auto_logged_out' => false,
        ];

        if ($attendance) {
            $attendance->update($data);
        } else {
            Attendance::create($data);
        }

        return back()->with('success', 'Attendance record saved successfully!');
    }

    /**
     * Admin Log Edit/Update Handler
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $request->validate([
            'status' => 'required|in:present,grace,late,half_day,absent,leave',
            'clock_in' => 'required|string',
            'clock_out' => 'nullable|string',
        ]);

        $date = $attendance->date->toDateString();
        $appTimezone = config('app.timezone', 'Asia/Kolkata');
        $clockIn = Carbon::parse($date . ' ' . $request->clock_in, $appTimezone);
        $clockOut = null;
        $totalHours = null;
        $overtimeHours = null;
        $settings = self::getSettings();

        if ($request->filled('clock_out')) {
            $clockOut = Carbon::parse($date . ' ' . $request->clock_out, $appTimezone);
            $totalMinutes = $clockIn->diffInMinutes($clockOut);
            
            // Subtract lunch break duration if recorded
            $lunchMinutes = 0;
            if ($attendance && $attendance->lunch_duration) {
                $lunchMinutes = round($attendance->lunch_duration * 60);
            }
            
            $netMinutes = max(0, $totalMinutes - $lunchMinutes);
            $totalHours = round($netMinutes / 60, 2);

            // Calculate Overtime
            $tempAtt = new Attendance([
                'date' => $attendance->date,
                'clock_in' => $clockIn->format('Y-m-d H:i:s'),
                'clock_out' => $clockOut->format('Y-m-d H:i:s'),
                'total_hours' => $totalHours
            ]);
            $overtimeHours = self::calculateOvertimeHours($tempAtt, $settings);
        }

        $attendance->update([
            'status' => $request->status,
            'clock_in' => $clockIn->format('Y-m-d H:i:s'),
            'clock_out' => $clockOut ? $clockOut->format('Y-m-d H:i:s') : null,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'admin_remarks' => $request->input('admin_remarks'),
            'auto_logged_out' => false,
        ]);

        return back()->with('success', 'Attendance log updated successfully!');
    }

    /**
     * Admin Log Deletion Handler (With Secure Authorization)
     */
    public function destroy($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $attendance = Attendance::findOrFail($id);
        
        // Delete associated selfie photo if exists
        if ($attendance->selfie_photo && Storage::disk('public')->exists($attendance->selfie_photo)) {
            Storage::disk('public')->delete($attendance->selfie_photo);
        }

        $attendance->delete();

        return back()->with('success', 'Attendance log deleted successfully.');
    }

    /**
     * Toggle Remote Attendance Permission for Employee
     */
    public function toggleRemote(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $user->allow_remote_attendance = !$user->allow_remote_attendance;
        $user->save();

        return response()->json([
            'success' => true,
            'allow_remote' => $user->allow_remote_attendance,
            'message' => $user->name . ' remote mode toggled successfully.'
        ]);
    }

    /**
     * Save Admin Attendance Settings
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'office_ip' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'office_start_time' => 'required|string',
            'grace_time_minutes' => 'required|integer|min:0',
            'standard_exit_time' => 'required|string',
            'auto_logout_time' => 'required|string',
            'overtime_comp_off_threshold' => 'required|numeric|min:0.5',
            'holidays' => 'nullable|string',
        ]);

        $settings = [
            'office_ip' => trim($request->office_ip),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'office_start_time' => $request->office_start_time,
            'grace_time_minutes' => intval($request->grace_time_minutes),
            'standard_exit_time' => $request->standard_exit_time,
            'auto_logout_time' => $request->auto_logout_time,
            'overtime_comp_off_threshold' => $request->overtime_comp_off_threshold,
            'holidays' => trim($request->input('holidays', '')),
        ];

        file_put_contents(
            storage_path('app/attendance_settings.json'),
            json_encode($settings, JSON_PRETTY_PRINT)
        );

        return back()->with('success', 'Attendance settings updated successfully.');
    }

    /**
     * Purge Selfie Photos to Optimize Disk Space (Manual Trigger)
     */
    public function purgeSelfies()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        // Get all selfies from the storage/public/selfies directory
        $files = Storage::disk('public')->files('selfies');
        $deletedCount = 0;

        foreach ($files as $file) {
            // Check if file modified time is older than 30 days
            $time = Storage::disk('public')->lastModified($file);
            if (Carbon::createFromTimestamp($time)->isBefore(Carbon::now()->subDays(30))) {
                Storage::disk('public')->delete($file);
                $deletedCount++;
            }
        }

        // Optionally update database column reference to null for deleted files
        // We'll leave the DB reference as-is or nullify expired ones:
        Attendance::where('created_at', '<', Carbon::now()->subDays(30))
            ->whereNotNull('selfie_photo')
            ->update(['selfie_photo' => null]);

        return back()->with('success', "Storage Optimized! {$deletedCount} old selfie snapshots were successfully deleted.");
    }

    /**
     * Direct E-mail Report Dispatcher (On-Demand)
     */
    public function sendEmailReport(Request $request)
    {
        $admin = Auth::user();
        if (!$admin->isAdmin()) {
            abort(403);
        }

        try {
            // Get data for custom date range or fallback to current month
            $startOfMonth = $request->input('start_date') 
                ? Carbon::parse($request->input('start_date'))->toDateString()
                : Carbon::now()->startOfMonth()->toDateString();
            $endOfMonth = $request->input('end_date')
                ? Carbon::parse($request->input('end_date'))->toDateString()
                : Carbon::now()->toDateString();
            
            $staffList = User::where('role', 'staff')->orderBy('name', 'asc')->get();
            $logs = Attendance::with('user')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->get();

            // Construct Report Data
            $perfectAttendanceStaff = [];
            $reportData = [];
            $settings = self::getSettings();
            foreach ($staffList as $staff) {
                $staffLogs = $logs->where('user_id', $staff->id);
                
                // Compile daily log details for lunch tracking
                $dailyDetails = [];
                foreach ($staffLogs->sortBy('date') as $log) {
                    $hasLunch = false;
                    $lunchStr = 'No Lunch';
                    if ($log->lunch_start && $log->lunch_end) {
                        $hasLunch = true;
                        $lunchDuration = $log->lunch_duration ?? 0;
                        $lunchDurationMinutes = round($lunchDuration * 60);
                        if ($lunchDurationMinutes >= 60) {
                            $hours = floor($lunchDurationMinutes / 60);
                            $mins = $lunchDurationMinutes % 60;
                            $lunchStr = $hours . 'h' . ($mins > 0 ? ' ' . $mins . 'm' : '');
                        } else {
                            $lunchStr = $lunchDurationMinutes . 'm';
                        }
                    }
                    $dailyDetails[] = [
                        'date' => Carbon::parse($log->date)->format('d M'),
                        'has_lunch' => $hasLunch,
                        'lunch' => $lunchStr
                    ];
                }

                $staffOvertime = floatval($staffLogs->sum('overtime_hours'));
                $threshold = floatval($settings['overtime_comp_off_threshold'] ?? 8.0);
                $compOffDays = $threshold > 0 ? round($staffOvertime / $threshold, 2) : 0;
                
                // Format overtime nicely, e.g. "12 hrs 30 min" or "-"
                $otFormatted = '-';
                if ($staffOvertime > 0) {
                    $totalMinutes = round($staffOvertime * 60);
                    $otHours = floor($totalMinutes / 60);
                    $otMins = $totalMinutes % 60;
                    if ($otHours > 0 && $otMins > 0) {
                        $otFormatted = "{$otHours} hrs {$otMins} min";
                    } elseif ($otHours > 0) {
                        $otFormatted = "{$otHours} hrs";
                    } else {
                        $otFormatted = "{$otMins} min";
                    }
                }

                $presentCount = $staffLogs->whereIn('status', ['present', 'grace', 'late', 'half_day'])->map(fn($l) => Carbon::parse($l->date)->toDateString())->unique()->count();
                $lateCount = $staffLogs->where('status', 'late')->map(fn($l) => Carbon::parse($l->date)->toDateString())->unique()->count();
                $halfDayCount = $staffLogs->where('status', 'half_day')->count();
                $absentCount = $staffLogs->where('status', 'absent')->count();
                $leaveCount = $staffLogs->where('status', 'leave')->count();

                $daysWorked = $staffLogs->whereIn('status', ['present', 'grace'])->map(fn($l) => Carbon::parse($l->date)->toDateString())->unique()->count();
                if ($daysWorked > 0 && $lateCount === 0 && $halfDayCount === 0 && $absentCount === 0) {
                    $perfectAttendanceStaff[] = $staff->name;
                }

                $reportData[] = [
                    'name' => $staff->name,
                    'email' => $staff->email,
                    'present' => $presentCount,
                    'late' => $lateCount,
                    'half_day' => $halfDayCount,
                    'absent' => $absentCount,
                    'leave' => $leaveCount,
                    'total_hours' => round($staffLogs->sum('total_hours'), 2),
                    'overtime_formatted' => $otFormatted,
                    'comp_off_days' => $compOffDays,
                    'daily_details' => $dailyDetails
                ];
            }

            // Generate detailed Excel/CSV data
            $holidaysList = Holiday::orderBy('date', 'asc')->get();
            $csvData = MonthlyAttendanceReport::generateCsvData($staffList, $logs, $holidaysList, $startOfMonth, $endOfMonth);

            // Dispatch mail
            Mail::to($admin->email)->send(new MonthlyAttendanceReport($admin, $reportData, $startOfMonth, $endOfMonth, $perfectAttendanceStaff, $csvData));

            return back()->with('success', 'Attendance Summary Report for the selected period has been compiled and emailed to your address successfully!');
        } catch (\Exception $e) {
            \Log::error('Attendance email failed: ' . $e->getMessage());
            return back()->with('error', 'Email failed to dispatch. Error: ' . $e->getMessage());
        }
    }

    /**
     * Start Lunch Break session
     */
    public function startLunch(Request $request)
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->orderBy('date', 'desc')
            ->orderBy('clock_in', 'desc')
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Active attendance record not found.');
        }

        if ($attendance->lunch_start) {
            return back()->with('error', 'Lunch break has already been started today.');
        }

        $appTimezone = config('app.timezone', 'Asia/Kolkata');
        $attendance->update([
            'lunch_start' => Carbon::now($appTimezone)->format('Y-m-d H:i:s'),
        ]);

        return back()->with('success', 'Lunch break started successfully.');
    }

    /**
     * End Lunch Break session
     */
    public function endLunch(Request $request)
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->orderBy('date', 'desc')
            ->orderBy('clock_in', 'desc')
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Active attendance record not found.');
        }

        if (!$attendance->lunch_start) {
            return back()->with('error', 'Lunch break has not been started yet.');
        }

        if ($attendance->lunch_end) {
            return back()->with('error', 'Lunch break has already been completed today.');
        }

        $appTimezone = config('app.timezone', 'Asia/Kolkata');
        $lunchEnd = Carbon::now($appTimezone);
        $rawLunchStart = $attendance->getRawOriginal('lunch_start');
        
        $tsLunchStart = strtotime($rawLunchStart);
        $tsLunchEnd   = strtotime($lunchEnd->format('Y-m-d H:i:s'));
        $durationMinutes = (int) max(0, ($tsLunchEnd - $tsLunchStart) / 60);
        $lunchDuration = round($durationMinutes / 60, 2);

        $attendance->update([
            'lunch_end' => $lunchEnd->format('Y-m-d H:i:s'),
            'lunch_duration' => $lunchDuration,
        ]);

        return back()->with('success', 'Lunch break ended successfully. Welcome back!');
    }

    /**
     * Calculate distance between two GPS coordinates in meters (Haversine formula)
     */
    private static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius; // distance in meters
    }

    /**
     * AJAX Trigger to generate a Weekly Office Efficiency Report
     */
    public function generateWeeklyReportAJAX(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        try {
            // Call our console command to generate the report
            $exitCode = \Illuminate\Support\Facades\Artisan::call('app:generate-weekly-efficiency');

            if ($exitCode === 0) {
                // Fetch the newly generated report
                $report = \App\Models\WeeklyEfficiencyReport::latest()->first();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Weekly Office Efficiency Report successfully generated!',
                    'report' => $report
                ]);
            } else {
                $output = \Illuminate\Support\Facades\Artisan::output();
                return response()->json([
                    'success' => false,
                    'message' => 'Report generation process failed: ' . trim($output)
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exception occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store Holiday
     */
    public function storeHoliday(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'reason' => 'required|string|max:255',
        ]);

        Holiday::create([
            'date' => $request->date,
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'Holiday added successfully!');
    }

    /**
     * Destroy Holiday
     */
    public function destroyHoliday($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        return back()->with('success', 'Holiday deleted successfully!');
    }
}
