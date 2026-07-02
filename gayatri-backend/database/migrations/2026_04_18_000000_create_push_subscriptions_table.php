<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('push_subscriptions', function (Blueprint $row) {
            $row->increments('id');
            $row->unsignedBigInteger('user_id')->index();
            $row->string('endpoint', 500)->unique();
            $row->string('public_key')->nullable();
            $row->string('auth_token')->nullable();
            $row->string('content_encoding')->nullable();
            $row->timestamps();

            $row->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
