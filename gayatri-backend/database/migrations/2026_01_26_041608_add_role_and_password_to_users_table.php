<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if 'role' column doesn't exist, then add it
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['client', 'admin', 'staff'])->default('client')->after('phone');
            }
            
            // Password already exists, so we skip it
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};