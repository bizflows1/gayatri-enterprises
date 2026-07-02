<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Mail\BirthdayMail;
use App\Mail\AnniversaryMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendOccasionEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send-occasions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated birthday and anniversary emails to clients and staff';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $todayMonth = Carbon::now()->month;
        $todayDay = Carbon::now()->day;

        $this->info("Checking for birthdays and anniversaries on " . Carbon::now()->format('F jS') . "...");

        // Birthdays
        $birthdayUsers = User::whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', $todayMonth)
            ->whereDay('date_of_birth', $todayDay)
            ->where('is_active', true)
            ->get();

        foreach ($birthdayUsers as $user) {
            if ($user->email) {
                Mail::to($user->email)->queue(new BirthdayMail($user));
                $this->line("Queued Birthday email for: {$user->name}");
            }
        }

        // Anniversaries
        $anniversaryUsers = User::whereNotNull('anniversary_date')
            ->whereMonth('anniversary_date', $todayMonth)
            ->whereDay('anniversary_date', $todayDay)
            ->where('is_active', true)
            ->get();

        foreach ($anniversaryUsers as $user) {
            if ($user->email) {
                Mail::to($user->email)->queue(new AnniversaryMail($user));
                $this->line("Queued Anniversary email for: {$user->name}");
            }
        }

        $this->info("Completed sending occasion emails.");
    }
}
