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
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('name');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('icon');
        });

        Schema::table('team_messages', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('team_messages')->nullOnDelete()->after('id'); // For Reply-to quote
            $table->boolean('is_starred')->default(false)->after('attachment'); // Starred message
        });

        // Explicit read receipts tracking (double ticks)
        Schema::create('message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('team_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Who read it
            $table->timestamps();

            $table->unique(['message_id', 'user_id']); // Can only read once
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_reads');

        Schema::table('team_messages', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'is_starred']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['icon', 'created_by']);
        });
    }
};
