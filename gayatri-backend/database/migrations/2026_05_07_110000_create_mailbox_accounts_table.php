<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table for custom user mail accounts (Gmail, Hostinger, etc.)
        Schema::create('mailbox_accounts', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->string('label');
            $blueprint->string('address');
            $blueprint->string('host');
            $blueprint->integer('port')->default(993);
            $blueprint->string('encryption')->default('ssl');
            $blueprint->string('username');
            $blueprint->text('password'); // Store securely (encrypted)
            $blueprint->string('smtp_host');
            $blueprint->integer('smtp_port')->default(465);
            $blueprint->string('smtp_encryption')->default('ssl');
            $blueprint->timestamps();
        });

        // Table for logging outgoing mail (enabling a real Outbox / local archive)
        Schema::create('mailbox_sent_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->string('account_key');
            $blueprint->string('to');
            $blueprint->string('subject');
            $blueprint->longText('body');
            $blueprint->string('status')->default('sent'); // 'sent', 'failed', 'pending'
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailbox_sent_logs');
        Schema::dropIfExists('mailbox_accounts');
    }
};
