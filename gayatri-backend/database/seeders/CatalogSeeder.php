<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Minimal catalog data to exercise the stock engine locally — a handful of
 * real Gayatri products, not the full 1-2k SKU import (that's the chunked
 * importer, built separately).
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $rankem = Brand::firstOrCreate(['name' => 'Rankem']);
        $merck = Brand::firstOrCreate(['name' => 'Merck']);
        $himedia = Brand::firstOrCreate(['name' => 'Himedia']);

        $solvents = Category::firstOrCreate(['name' => 'Solvents']);
        $acids = Category::firstOrCreate(['name' => 'Acids']);
        $stains = Category::firstOrCreate(['name' => 'Stains']);

        Supplier::firstOrCreate(['name' => 'Rankem Distribution Pvt Ltd'], ['gstin' => '06AAACR1234F1Z5', 'terms' => 'Net 30']);
        Supplier::firstOrCreate(['name' => 'Merck Life Sciences Pvt Ltd'], ['gstin' => '27AAAMM5678G1Z3', 'terms' => 'Net 45']);

        $products = [
            ['name' => 'Acetone AR/ACS', 'cas_number' => '67-64-1', 'brand_id' => $rankem->id, 'category_id' => $solvents->id, 'grade' => 'AR Grade', 'pack_size' => '500ml', 'unit' => 'bottle', 'sales_price' => 320],
            ['name' => 'Acetone AR/ACS', 'cas_number' => '67-64-1', 'brand_id' => $rankem->id, 'category_id' => $solvents->id, 'grade' => 'AR Grade', 'pack_size' => '2.5L', 'unit' => 'can', 'sales_price' => 1180],
            ['name' => 'Hydrochloric Acid 37%', 'cas_number' => '7647-01-0', 'brand_id' => $merck->id, 'category_id' => $acids->id, 'grade' => 'AR Grade', 'pack_size' => '500ml', 'unit' => 'bottle', 'sales_price' => 280],
            ['name' => 'Methylene Blue', 'cas_number' => '61-73-4', 'brand_id' => $himedia->id, 'category_id' => $stains->id, 'grade' => 'Microscopy Grade', 'pack_size' => '25g', 'unit' => 'jar', 'sales_price' => 450],
        ];

        foreach ($products as $p) {
            $slug = Str::slug($p['name'] . '-' . $p['pack_size']);
            Product::firstOrCreate(['slug' => $slug], $p + ['slug' => $slug, 'is_active' => true]);
        }
    }
}
