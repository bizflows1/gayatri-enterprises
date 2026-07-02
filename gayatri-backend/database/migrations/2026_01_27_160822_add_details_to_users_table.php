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
    Schema::table('users', function (Blueprint $table) {
        // Business Details (Optional)
        $table->string('pan_number')->nullable();
        $table->string('gst_number')->nullable();
        $table->string('business_name')->nullable();
        
        // Recently Viewed Tracking ke liye alag table banayenge ya DocumentModel me `last_viewed_at` jodenge.
        
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
