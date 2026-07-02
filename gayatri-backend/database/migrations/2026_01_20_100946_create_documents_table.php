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
    Schema::create('documents', function (Blueprint $table) {
        $table->id();
        // Kis Client ki file hai (User delete hua to file bhi gayi)
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); 

        $table->string('filename');   // Asli naam (e.g. ITR 2024.pdf)
        $table->string('file_path');  // System ka path (documents/xyz.pdf)
        $table->string('category')->default('General'); // Folder Name (ITR, GST, etc.)

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
