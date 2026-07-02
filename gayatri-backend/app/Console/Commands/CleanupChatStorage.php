<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use App\Models\Chat;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupChatStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:cleanup-storage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans up old standard chat messages, task chats, and their shared media attachments older than 6 months (180 days).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now('Asia/Kolkata');
        $sixMonthsAgo = $now->copy()->subMonths(6);

        Log::info("[Chat Cleanup] Starting automatic cleanup process. Current Time: " . $now->toDateTimeString() . " | Threshold: " . $sixMonthsAgo->toDateTimeString());
        $this->info("Starting automatic cleanup process...");

        // -------------------------------------------------------------
        // 1. Process Standard Chat Messages (Message Model) > 6 Months
        // -------------------------------------------------------------
        $oldMessages = Message::where('created_at', '<', $sixMonthsAgo)
            ->where('body', '!=', 'Deleted because the backup limit exceeded')
            ->get();

        $deletedStandardMessagesCount = 0;
        $deletedStandardAttachmentsCount = 0;

        foreach ($oldMessages as $message) {
            // Delete attachment if exists and is strictly a chat attachment
            if ($message->attachment && str_starts_with($message->attachment, 'chat_attachments/')) {
                try {
                    if (Storage::disk('public')->exists($message->attachment)) {
                        Storage::disk('public')->delete($message->attachment);
                        $deletedStandardAttachmentsCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("[Chat Cleanup] Failed deleting standard chat attachment for Message ID {$message->id}: " . $e->getMessage());
                }
            }

            // Update message contents to the backup limit message
            $message->update([
                'body' => 'Deleted because the backup limit exceeded',
                'attachment' => null
            ]);
            $deletedStandardMessagesCount++;
        }

        // -------------------------------------------------------------
        // 2. Process Task Chats (Chat Model) > 6 Months
        // -------------------------------------------------------------
        $oldTaskChats = Chat::where('created_at', '<', $sixMonthsAgo)
            ->where('message', '!=', 'Deleted because the backup limit exceeded')
            ->get();

        $deletedTaskChatsCount = 0;
        $deletedTaskAttachmentsCount = 0;

        foreach ($oldTaskChats as $chat) {
            // Delete attachment if exists and is strictly a task chat file
            if ($chat->attachment && str_starts_with($chat->attachment, 'chat_files/')) {
                try {
                    if (Storage::disk('public')->exists($chat->attachment)) {
                        Storage::disk('public')->delete($chat->attachment);
                        $deletedTaskAttachmentsCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("[Chat Cleanup] Failed deleting task chat attachment for Chat ID {$chat->id}: " . $e->getMessage());
                }
            }

            // Update chat contents to the backup limit message
            $chat->update([
                'message' => 'Deleted because the backup limit exceeded',
                'attachment' => null
            ]);
            $deletedTaskChatsCount++;
        }

        $logMsg = "[Chat Cleanup] Completed. " .
                  "Standard Chats (>6 months) cleaned: {$deletedStandardMessagesCount} (deleted attachments: {$deletedStandardAttachmentsCount}). " .
                  "Task Chats (>6 months) cleaned: {$deletedTaskChatsCount} (deleted attachments: {$deletedTaskAttachmentsCount}).";
        
        Log::info($logMsg);
        $this->info($logMsg);
    }
}
