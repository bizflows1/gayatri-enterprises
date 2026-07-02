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
        Schema::table('attendances', function (Blueprint $table) {
            $table->dateTime('lunch_start')->nullable()->after('clock_out');
            $table->dateTime('lunch_end')->nullable()->after('lunch_start');
            $table->decimal('lunch_duration', 5, 2)->nullable()->after('lunch_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['lunch_start', 'lunch_end', 'lunch_duration']);
        });
    }
};
