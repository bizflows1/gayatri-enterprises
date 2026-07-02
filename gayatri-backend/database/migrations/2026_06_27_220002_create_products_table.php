<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('cas_number')->nullable()->index();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('hsn_code')->nullable();
            $table->string('grade')->nullable();
            $table->string('pack_size');
            $table->string('unit');
            $table->decimal('sales_price', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('sds_file')->nullable();
            $table->string('coa_file')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // FULLTEXT isn't supported on sqlite (used for tests) — MySQL only.
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->fullText(['name', 'cas_number']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
