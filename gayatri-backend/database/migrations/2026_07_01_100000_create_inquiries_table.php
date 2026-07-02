<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('contact'); // contact | bulk_order
            $table->string('name');
            $table->string('email');
            $table->string('institution')->nullable();
            $table->string('type')->nullable();         // contact: requirement type
            $table->text('message')->nullable();        // contact: free-text message

            // bulk order specific
            $table->string('company')->nullable();
            $table->string('industry')->nullable();
            $table->string('contact_person')->nullable();
            $table->text('requirements')->nullable();
            $table->boolean('needs_msds_coa')->default(false);

            $table->enum('status', ['new', 'in_progress', 'closed'])->default('new');
            $table->text('notes')->nullable();          // internal staff notes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
