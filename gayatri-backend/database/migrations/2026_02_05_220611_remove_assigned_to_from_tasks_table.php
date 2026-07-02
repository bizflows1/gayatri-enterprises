<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Data Migration: Copy existing assigned_to values to new pivot table
        // We use raw SQL or DB builder to ensure this runs even if Models change later
        $tasks = DB::table('tasks')->whereNotNull('assigned_to')->get();

        foreach ($tasks as $task) {
            DB::table('task_user')->insert([
                'task_id' => $task->id,
                'user_id' => $task->assigned_to,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Drop the column
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']); // Drop foreign key first if exists
            $table->dropColumn('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->constrained('users');
        });

        // Restore data (optional/best effort - picks first user)
        $pivotEntries = DB::table('task_user')->get();
        foreach ($pivotEntries as $entry) {
            DB::table('tasks')->where('id', $entry->task_id)->update(['assigned_to' => $entry->user_id]);
        }
    }
};
