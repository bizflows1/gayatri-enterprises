<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->enum('order_type', ['portal', 'admin'])->default('portal');
            $table->enum('status', [
                'draft', 'quote_requested', 'quoted', 'confirmed',
                'packed', 'dispatched', 'delivered', 'cancelled',
            ])->default('draft');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('gst', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->timestamps();

            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
