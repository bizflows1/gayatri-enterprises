<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Calendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMeetingReminders extends Command
{
    protected $signature = 'app:send-meeting-reminders';
    protected $description = 'Checks Google Calendar for upcoming meetings and sends a 30-min reminder email';

    public function handle()
    {
        $credPath = storage_path('app/google-credentials.json');
        if (!file_exists($credPath) || !env('MAIL_USERNAME')) {
            Log::info("[Cron] Reminders skipped: missing credentials or mail config.");
            return;
        }

        try {
            $client = new Client();
            $client->setAuthConfig($credPath);
            $client->addScope(Calendar::CALENDAR);
            
            $service = new Calendar($client);
            $calendarId = env('GOOGLE_CALENDAR_ID', 'primary');

            $now = Carbon::now('Asia/Kolkata');
            $timeMin = $now->copy()->addMinutes(25)->toRfc3339String();
            $timeMax = $now->copy()->addMinutes(35)->toRfc3339String();

            $optParams = [
                'timeMin' => $timeMin,
                'timeMax' => $timeMax,
                'singleEvents' => true,
                'orderBy' => 'startTime',
            ];

            $results = $service->events->listEvents($calendarId, $optParams);
            $events = $results->getItems();

            foreach ($events as $event) {
                // Check if reminder was already sent
                $props = $event->getExtendedProperties();
                if ($props && $props->getPrivate() && isset($props->getPrivate()['reminderSent'])) {
                    if ($props->getPrivate()['reminderSent'] === 'true') {
                        continue;
                    }
                }

                $desc = $event->getDescription();
                if (!$desc) continue;

                $clientEmails = [];
                if (preg_match('/Client Email:\s*([^\n]+)/', $desc, $matches)) {
                    $mailStr = trim($matches[1]);
                    if (strtolower($mailStr) !== strtolower(env('MAIL_USERNAME'))) {
                        $clientEmails[] = $mailStr;
                    }
                }

                $meetLink = 'No link attached';
                if (preg_match('/Join Meeting:\s*([^\s]+)/', $desc, $linkMatch)) {
                    $meetLink = trim($linkMatch[1]);
                }

                $clientName = 'Client';
                if (preg_match('/Client Name:\s*([^\n]+)/', $desc, $nameMatch)) {
                    $clientName = trim($nameMatch[1]);
                }

                foreach ($clientEmails as $email) {
                    try {
                        Mail::raw("Hello {$clientName},\n\nThis is a friendly reminder that your upcoming consultation begins in 30 minutes.\n\nMeeting Link: {$meetLink}\n\nThanks,\nGayatri Enterprises", function ($message) use ($email, $clientName) {
                            $message->to($email)
                                    ->subject("Reminder: Consultation with {$clientName} in 30 minutes");
                        });
                        Log::info("[Cron] Sent 30-min reminder to {$email}");
                    } catch (\Exception $e) {
                        Log::error("[Cron] Failed emailing {$email}: " . $e->getMessage());
                    }
                }

                // Mark event as sent
                $patchEvent = new \Google\Service\Calendar\Event([
                    'extendedProperties' => [
                        'private' => ['reminderSent' => 'true']
                    ]
                ]);
                
                try {
                    $service->events->patch($calendarId, $event->getId(), $patchEvent);
                } catch (\Exception $ex) {
                    Log::error("[Cron] Failed to patch event: " . $ex->getMessage());
                }
            }

        } catch (\Exception $err) {
            Log::error("[Cron] Error processing reminders: " . $err->getMessage());
        }
    }
}
