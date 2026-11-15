<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::active()
            ->with('category')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->string('q')->toString();
                $query->where(fn ($q) => $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('sku', 'like', "%{$keyword}%"));
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($request->filled('min_price'), fn ($q) => $q->where('price', '>=', $request->input('min_price')))
            ->when($request->filled('max_price'), fn ($q) => $q->where('price', '<=', $request->input('max_price')))
            ->when($request->boolean('ready'), fn ($q) => $q->where('stock', '>', 0))
            ->when($request->input('sort') === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when($request->input('sort') === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->when($request->input('sort') === 'name', fn ($q) => $q->orderBy('name'))
            ->when(! in_array($request->input('sort'), ['price_asc', 'price_desc', 'name'], true), fn ($q) => $q->latest())
            ->paginate(4)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('shop.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load('category');

        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->latest()
            ->take(4)
            ->get();

        return view('shop.show', compact('product', 'relatedProducts'));
    }
}