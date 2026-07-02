<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Shared hosting can't run a persistent `queue:work` daemon, so the queue is
// driven by cron instead: process whatever's waiting, then exit, every
// minute. Without this, anything dispatched to a queue (broadcasts, bulk
// imports, PDF generation) would sit in the jobs table forever and silently
// never run — the single Hostinger cron entry (`schedule:run` every minute)
// covers this automatically once it's set up.
Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()->withoutOverlapping();

// Schedule the AI Blog Generator to run daily
Schedule::command('app:generate-daily-blog')->dailyAt('00:00');
Schedule::command('app:send-meeting-reminders')->everyFiveMinutes();
Schedule::command('chat:cleanup-storage')->dailyAt('01:00');
Schedule::command('app:generate-weekly-efficiency')->saturdays()->at('08:00');

// Nightly auto-checkout for staff who forgot to clock out
Schedule::command('attendance:recalculate-hours')->dailyAt('00:05');

// Send occasion emails (birthday, anniversary)
Schedule::command('emails:send-occasions')->dailyAt('08:00');

// DSC Expiry Mailer — runs every hour so the command's internal hour-tier logic fires appropriately:
// ≤15d = 10:00 | ≤10d = 10:00,16:00 | ≤5d = 9:00,13:00,18:00 | ≤1d = 8,10,12,14,16 | expired = 10:00
Schedule::command('dsc:send-expiry-mails')->hourly();

