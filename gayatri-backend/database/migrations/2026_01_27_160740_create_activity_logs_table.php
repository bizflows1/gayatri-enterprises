<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('activity_logs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable(); // Jisne action kiya (Staff/Admin/Client)
        $table->string('action'); // e.g., "Uploaded Document", "Viewed File", "Deleted Client"
        $table->text('description')->nullable(); // Details e.g., "ITR 2024-25.pdf"
        $table->unsignedBigInteger('target_user_id')->nullable(); // Kiske liye kiya (Client ID)
        $table->string('ip_address')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
