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
        Schema::table('team_messages', function (Blueprint $table) {
            $table->dropColumn(['is_starred', 'is_pinned']);
            $table->json('starred_by')->nullable()->after('attachment');
            $table->json('pinned_by')->nullable()->after('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_messages', function (Blueprint $table) {
            $table->dropColumn(['starred_by', 'pinned_by']);
            $table->boolean('is_starred')->default(false)->after('attachment');
            $table->boolean('is_pinned')->default(false)->after('deleted_by');
        });
    }
};
