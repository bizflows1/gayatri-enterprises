<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use Carbon\Carbon;

class RecalculateAttendanceHours extends Command
{
    protected $signature = 'attendance:recalculate-hours
                            {--dry-run : Preview changes without saving}';

    protected $description = 'Retroactively: (1) recalculate total_hours for records with clock_in+clock_out but 0/NULL hours, (2) auto-checkout old stale active sessions.';

    /**
     * Calculate minutes between two raw DB timestamp strings.
     * Uses Unix timestamp subtraction — works on every Carbon/PHP version.
     * No timezone parameter needed: duration math is timezone-agnostic.
     */
    private function minutesBetween(string $from, string $to): int
    {
        $tsFrom = strtotime($from);
        $tsTo   = strtotime($to);

        if ($tsFrom === false || $tsTo === false) {
            return 0;
        }

        return (int) max(0, ($tsTo - $tsFrom) / 60);
    }

    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        // Load attendance settings for auto_logout_time
        $settingsPath   = storage_path('app/attendance_settings.json');
        $settings       = file_exists($settingsPath)
                          ? json_decode(file_get_contents($settingsPath), true)
                          : [];
        $autoLogoutTime = $settings['auto_logout_time'] ?? '23:59';

        if ($isDryRun) {
            $this->warn('=== DRY RUN MODE — No changes will be saved ===');
        }

        // ─────────────────────────────────────────────────────────────────
        // PART 1: Fix records that have clock_in + clock_out but hours = 0
        // total_hours = (clock_out - clock_in) - lunch_minutes
        // ─────────────────────────────────────────────────────────────────
        $this->newLine();
        $this->info('── PART 1: Recalculate hours for complete records with 0/NULL total_hours ──');

        $completeRecords = Attendance::whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->where(function ($q) {
                $q->whereNull('total_hours')->orWhere('total_hours', '<=', 0);
            })
            ->orderBy('date', 'asc')
            ->get();

        $this->info("Found {$completeRecords->count()} records to fix.");

        $fixed = 0; $skipped = 0;

        foreach ($completeRecords as $record) {
            $rawIn  = $record->getRawOriginal('clock_in');
            $rawOut = $record->getRawOriginal('clock_out');

            if (!$rawIn || !$rawOut) {
                $this->warn("  SKIP ID:{$record->id} — missing timestamps");
                $skipped++;
                continue;
            }

            $tsIn  = strtotime($rawIn);
            $tsOut = strtotime($rawOut);

            if ($tsOut <= $tsIn) {
                $this->warn("  SKIP ID:{$record->id} [{$record->date->toDateString()}] — clock_out <= clock_in");
                $skipped++;
                continue;
            }

            $totalMinutes = (int)(($tsOut - $tsIn) / 60);

            // Subtract lunch break if recorded
            $lunchMinutes = 0;
            if ($record->lunch_duration && floatval($record->lunch_duration) > 0) {
                $lunchMinutes = (int) round(floatval($record->lunch_duration) * 60);
            }

            $netMinutes = max(0, $totalMinutes - $lunchMinutes);
            $totalHours = round($netMinutes / 60, 2);

            $hrs = floor($totalHours);
            $mins = (int)(($totalHours - $hrs) * 60);
            $displayHours = "{$hrs}h {$mins}m";

            $this->line("  FIX  ID:{$record->id} [{$record->date->toDateString()}] In:{$rawIn} Out:{$rawOut} Lunch:{$lunchMinutes}min => {$totalHours} hrs ({$displayHours})");

            if (!$isDryRun) {
                $record->update(['total_hours' => $totalHours]);
            }
            $fixed++;
        }

        $this->info("Part 1 done. Fixed: {$fixed}, Skipped: {$skipped}");

        // ─────────────────────────────────────────────────────────────────
        // PART 2: Auto-checkout stale sessions (past days, no clock_out)
        // ─────────────────────────────────────────────────────────────────
        $this->newLine();
        $this->info('── PART 2: Auto-checkout old stale active sessions at Standard Exit Time (18:30) ──');

        $today = date('Y-m-d');

        $staleRecords = Attendance::whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->whereDate('date', '<', $today)
            ->orderBy('date', 'asc')
            ->get();

        $this->info("Found {$staleRecords->count()} stale active sessions.");

        $autoFixed = 0; $autoSkipped = 0;

        foreach ($staleRecords as $record) {
            $rawIn = $record->getRawOriginal('clock_in');

            if (!$rawIn) {
                $this->warn("  SKIP ID:{$record->id} — missing clock_in");
                $autoSkipped++;
                continue;
            }

            $tsIn = strtotime($rawIn);

            // Build auto-checkout datetime string from the clock_in date
            $recordDateStr = substr($rawIn, 0, 10); // "YYYY-MM-DD"
            
            // Standard exit time from settings
            $exitTimeStr = $settings['standard_exit_time'] ?? '18:30';
            $checkoutStr = $recordDateStr . ' ' . $exitTimeStr . ':00';

            $tsOut = strtotime($checkoutStr);

            if ($tsOut <= $tsIn) {
                $this->warn("  SKIP ID:{$record->id} — checkout would be <= clock_in");
                $autoSkipped++;
                continue;
            }

            $totalMinutes = (int)(($tsOut - $tsIn) / 60);

            $lunchMinutes = 0;
            if ($record->lunch_duration && floatval($record->lunch_duration) > 0) {
                $lunchMinutes = (int) round(floatval($record->lunch_duration) * 60);
            }

            $netMinutes = max(0, $totalMinutes - $lunchMinutes);
            $totalHours = round($netMinutes / 60, 2);

            $hrs  = floor($totalHours);
            $mins = (int)(($totalHours - $hrs) * 60);

            $this->line("  FIX ID:{$record->id} [{$recordDateStr}] In:{$rawIn} => Auto-checkout at {$exitTimeStr} ({$totalHours} hrs).");

            if (!$isDryRun) {
                $autoNote = "• System Auto-Checkout: Missing manual checkout. Work log not submitted.";
                $newLog   = $record->work_log
                            ? $record->work_log . "\n" . $autoNote
                            : $autoNote;

                // Calculate Overtime
                $overtimeHours = 0.00;
                $dateObj = Carbon::parse($recordDateStr);
                $isSunday = $dateObj->isSunday();
                
                // Fetch holidays
                $isHoliday = false;
                $holidays = array_map('trim', explode(',', $settings['holidays'] ?? ''));
                if (in_array($recordDateStr, $holidays)) {
                    $isHoliday = true;
                }

                if ($isSunday || $isHoliday) {
                    $overtimeHours = $totalHours;
                } else {
                    $standardExit = Carbon::parse($recordDateStr . ' ' . $exitTimeStr);
                    $clockInObj = Carbon::parse($record->clock_in);
                    $clockOutObj = Carbon::parse($checkoutStr);
                    if ($clockOutObj->gt($standardExit)) {
                        $startOfOT = $clockInObj->gt($standardExit) ? $clockInObj : $standardExit;
                        $otMinutes = $clockOutObj->diffInMinutes($startOfOT);
                        $overtimeHours = round($otMinutes / 60, 2);
                    }
                }

                $record->update([
                    'clock_out'       => Carbon::parse($checkoutStr)->format('Y-m-d H:i:s'),
                    'total_hours'     => $totalHours,
                    'overtime_hours'  => $overtimeHours,
                    'auto_logged_out' => true,
                    'work_log'        => $newLog,
                    'notes'           => $record->notes
                                          ? $record->notes . ' (System Auto-Checkout at ' . $exitTimeStr . ')'
                                          : 'System Auto-Checkout at ' . $exitTimeStr,
                ]);
            }
            $autoFixed++;
        }

        $this->info("Part 2 done. Flagged & Auto-Checked Out: {$autoFixed}, Skipped: {$autoSkipped}");

        // ─────────────────────────────────────────────────────────────────
        // PART 3: Clean up legacy records auto-logged-out with fake 23:59
        // Reset clock_out/total_hours to NULL so admin fills real data
        // ─────────────────────────────────────────────────────────────────
        $this->newLine();
        $this->info('── PART 3: Clean up legacy auto-logout records with fake 23:59 checkout ──');

        $fakeLogouts = Attendance::where('auto_logged_out', true)
            ->whereNotNull('clock_out')
            ->orderBy('date', 'asc')
            ->get()
            ->filter(function ($record) {
                $rawOut = $record->getRawOriginal('clock_out');
                return $rawOut && str_contains($rawOut, '23:59:00');
            });

        $this->info("Found {$fakeLogouts->count()} legacy auto-logged-out records to clean.");

        $cleaned = 0;

        foreach ($fakeLogouts as $record) {
            $rawOut = $record->getRawOriginal('clock_out');
            $this->line("  CLEAN ID:{$record->id} [{$record->date->toDateString()}] Removing legacy fake checkout: {$rawOut}");

            if (!$isDryRun) {
                $record->update([
                    'clock_out'      => null,
                    'total_hours'    => null,
                    'overtime_hours' => null,
                    'work_log'       => "• Logout not recorded. Admin kindly fill details.",
                    'notes'          => 'Auto-flagged: No checkout',
                ]);
            }
            $cleaned++;
        }

        $this->info("Part 3 done. Cleaned: {$cleaned}");

        $this->newLine();
        if ($isDryRun) {
            $this->warn('DRY RUN complete — no data was changed. Run without --dry-run to apply.');
        } else {
            $this->info('✓ All done! Database updated successfully.');
        }

        return Command::SUCCESS;
    }
}
