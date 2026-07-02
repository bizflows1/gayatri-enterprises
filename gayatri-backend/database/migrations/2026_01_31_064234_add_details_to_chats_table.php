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
    Schema::table('chats', function (Blueprint $table) {
        $table->string('attachment')->nullable()->after('message'); // File ke liye
        $table->boolean('is_read')->default(false)->after('attachment'); // Notification ke liye
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            //
        });
    }
};
