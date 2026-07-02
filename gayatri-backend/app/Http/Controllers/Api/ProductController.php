<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Public catalog API for the Vite/React frontend — no auth required, this
 * is the same data the marketing site's product pages need.
 */
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['brand', 'category', 'images'])->where('is_active', true);

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cas_number', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($brandId = $request->query('brand_id')) {
            $query->where('brand_id', $brandId);
        }

        $products = $query->orderBy('name')->paginate($request->integer('per_page', 24));

        $products->getCollection()->transform(function (Product $product) {
            $product->available_qty = $product->availableQty();
            return $product;
        });

        return response()->json($products);
    }

    public function show(Product $product)
    {
        $product->load(['brand', 'category', 'images']);
        $product->available_qty = $product->availableQty();

        return response()->json($product);
    }
}
