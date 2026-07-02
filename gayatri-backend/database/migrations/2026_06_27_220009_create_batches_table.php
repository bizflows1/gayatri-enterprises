<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('batch_no');
            $table->date('expiry_date')->index();
            $table->decimal('purchase_price', 12, 2);
            $table->decimal('qty_received', 12, 2);
            $table->decimal('qty_remaining', 12, 2);
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('grn_id')->constrained('goods_receipts')->onDelete('cascade');
            $table->enum('condition', ['good', 'quarantine', 'damaged'])->default('good');
            $table->timestamp('received_at');
            $table->timestamps();

            $table->index(['product_id', 'condition', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
