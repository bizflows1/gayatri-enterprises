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
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->dateTime('clock_in');
                $table->dateTime('clock_out')->nullable();
                $table->decimal('total_hours', 5, 2)->nullable();
                $table->string('status'); // 'present', 'late', 'half_day', 'absent'
                $table->string('clock_in_ip')->nullable();
                $table->string('clock_out_ip')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('selfie_photo')->nullable(); // Saved selfie filename
                $table->text('notes')->nullable();
                $table->text('admin_remarks')->nullable();
                $table->timestamps();

                // Enforce single attendance record per user per day
                $table->unique(['user_id', 'date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
