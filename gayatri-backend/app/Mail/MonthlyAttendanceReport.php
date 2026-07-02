<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class MonthlyAttendanceReport extends Mailable
{
    use Queueable, SerializesModels;

    public $admin;
    public $reportData;
    public $startDate;
    public $endDate;
    public $monthName;
    public $perfectAttendanceStaff;
    public $csvData;

    /**
     * Create a new message instance.
     */
    public function __construct($admin, $reportData, $startDate, $endDate, $perfectAttendanceStaff = [], $csvData = null)
    {
        $this->admin = $admin;
        $this->reportData = $reportData;
        $this->perfectAttendanceStaff = $perfectAttendanceStaff;
        $this->csvData = $csvData;
        
        $startParsed = Carbon::parse($startDate);
        $endParsed = Carbon::parse($endDate);
        
        $this->startDate = $startParsed->format('d M Y');
        $this->endDate = $endParsed->format('d M Y');
        
        // If the date range is within the same month, show e.g. "June 2026". Otherwise, show range e.g. "May 2026 - Jun 2026".
        if ($startParsed->format('Y-m') === $endParsed->format('Y-m')) {
            $this->monthName = $startParsed->format('F Y');
        } else {
            $this->monthName = $startParsed->format('M Y') . ' - ' . $endParsed->format('M Y');
        }
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mail = $this->subject("Attendance Summary Report [{$this->startDate} - {$this->endDate}]")
                     ->view('emails.attendance_report');

        if ($this->csvData) {
            $mail->attachData($this->csvData, 'attendance_detailed_report.csv', [
                'mime' => 'text/csv',
            ]);
        }

        return $mail;
    }

    /**
     * Generate detailed CSV report tracing day-by-day logs for all staff members.
     */
    public static function generateCsvData($staffList, $logs, $holidaysList, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dates[] = $d->copy();
        }
        
        $handle = fopen('php://temp', 'r+');
        
        // UTF-8 BOM for Microsoft Excel compatibility
        fwrite($handle, "\xEF\xBB\xBF");
        
        // CSV Headers
        fputcsv($handle, [
            'Staff Name',
            'Date',
            'Day',
            'Clock In',
            'Clock Out',
            'Worked Hours',
            'Overtime Hours',
            'Status',
            'Admin Remarks',
            'Notes'
        ]);
        
        foreach ($staffList as $staff) {
            foreach ($dates as $date) {
                $dateStr = $date->toDateString();
                $log = $logs->where('user_id', $staff->id)
                            ->filter(function($l) use ($dateStr) {
                                $logDate = $l->date instanceof Carbon ? $l->date->toDateString() : substr($l->date, 0, 10);
                                return $logDate === $dateStr;
                            })
                            ->first();
                
                $dayName = $date->format('l');
                $isSunday = $date->isSunday();
                $holiday = $holidaysList->firstWhere('date', $dateStr);
                
                $clockIn = '-';
                $clockOut = '-';
                $workedHours = '0.00';
                $overtimeHours = '0.00';
                $status = '';
                $notes = '';
                $remarks = '';
                
                if ($log) {
                    $clockIn = $log->clock_in ? Carbon::parse($log->clock_in)->format('H:i:s') : '-';
                    $clockOut = $log->clock_out ? Carbon::parse($log->clock_out)->format('H:i:s') : '-';
                    $workedHours = $log->total_hours !== null ? number_format($log->total_hours, 2) : '0.00';
                    $overtimeHours = $log->overtime_hours !== null ? number_format($log->overtime_hours, 2) : '0.00';
                    $status = ucfirst($log->status === 'grace' ? 'grace period' : $log->status);
                    $notes = $log->notes ?? '';
                    $remarks = $log->admin_remarks ?? '';
                } else {
                    if ($isSunday) {
                        $status = 'Sunday (Weekly Off)';
                    } elseif ($holiday) {
                        $status = 'Holiday: ' . $holiday->reason;
                    } else {
                        $status = 'Absent';
                    }
                }
                
                fputcsv($handle, [
                    $staff->name,
                    $date->format('d M Y'),
                    $dayName,
                    $clockIn,
                    $clockOut,
                    $workedHours,
                    $overtimeHours,
                    $status,
                    $remarks,
                    $notes
                ]);
            }
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return $csv;
    }
}
