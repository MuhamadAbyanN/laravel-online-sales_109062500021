<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')
            ->where('is_active', true);

        // 1. Pencarian kata kunci (q)
        if ($request->filled('q')) {
            $keyword = $request->string('q')->toString();
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('sku', 'like', "%{$keyword}%");
            });
        }

        // 2. Filter kategori (ID atau slug)
        if ($request->filled('category')) {
            $category = $request->input('category');
            $query->where(function ($q) use ($category) {
                $q->where('category_id', $category)
                  ->orWhereHas('category', fn ($cat) => $cat->where('slug', $category));
            });
        }

        // 3. Filter rentang harga (min_price & max_price)
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (int) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (int) $request->input('max_price'));
        }

        // 4. Filter ketersediaan stok (ready=1 atau ready=0)
        if ($request->filled('ready')) {
            $request->boolean('ready')
                ? $query->where('stock', '>', 0)
                : $query->where('stock', '<=', 0);
        }

        // 5. Pengurutan (sort)
        match ($request->input('sort')) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc'   => $query->orderBy('name', 'asc'),
            'name_desc'  => $query->orderBy('name', 'desc'),
            default      => $query->latest(),
        };

        // 6. Pagination (per_page) -> menghasilkan HTTP 200 dengan data, links, dan meta
        $perPage = min(max((int) $request->input('per_page', 10), 1), 100);
        $products = $query->paginate($perPage)->withQueryString();

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        return new ProductResource($product->load('category'));
    }

    public function categories()
    {
        return response()->json([
            'data' => Category::orderBy('name')->get(),
        ]);
    }

    public function shippingServices()
    {
        return response()->json([
            'data' => ShippingService::where('is_active', true)->get(),
        ]);
    }
}