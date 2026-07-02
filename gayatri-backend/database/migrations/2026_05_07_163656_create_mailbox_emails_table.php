<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mailbox_emails', function (Blueprint $table) {
            $table->id();
            $table->string('account_key', 50);
            $table->string('folder_name', 100);
            $table->string('uid', 50);
            $table->text('subject')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_raw')->nullable();
            $table->string('reply_to')->nullable();
            $table->string('date_string')->nullable();
            $table->integer('imap_timestamp')->nullable();
            $table->boolean('seen')->default(false);
            $table->boolean('starred')->default(false);
            $table->mediumText('body')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->unique(['account_key', 'folder_name', 'uid'], 'mailbox_emails_acc_fld_uid_unique');
            $table->index(['account_key', 'folder_name', 'imap_timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailbox_emails');
    }
};
