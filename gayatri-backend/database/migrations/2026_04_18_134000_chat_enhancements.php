<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('team_messages', 'is_deleted_globally')) {
                $table->boolean('is_deleted_globally')->default(false)->after('is_starred');
            }
            if (!Schema::hasColumn('team_messages', 'deleted_by')) {
                $table->json('deleted_by')->nullable()->after('is_deleted_globally');
            }
            if (!Schema::hasColumn('team_messages', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('deleted_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('team_messages', function (Blueprint $table) {
            $table->dropColumn(['is_deleted_globally', 'deleted_by', 'is_pinned']);
        });
    }
};
