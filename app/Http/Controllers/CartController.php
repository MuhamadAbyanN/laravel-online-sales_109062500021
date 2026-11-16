<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    private function ensureNotAdmin(): void
    {
        abort_if(auth()->user()?->role === 'admin', 403, 'Admin tidak memiliki keranjang.');
    }

    public function index(): View
    {
        $this->ensureNotAdmin();

        $cart = session('cart', []);
        $productIds = array_keys($cart);

        $products = Product::active()
            ->with('category')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotal = 0;

        foreach ($cart as $productId => $qty) {
            if (! isset($products[$productId])) {
                continue;
            }

            $product = $products[$productId];
            $quantity = min((int) $qty, $product->stock);

            if ($quantity < 1) {
                continue;
            }

            $lineTotal = $product->price * $quantity;
            $subtotal += $lineTotal;

            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ];
        }

        return view('cart.index', compact('items', 'subtotal'));
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $this->ensureNotAdmin();
        abort_unless($product->is_active, 404);

        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $addQty = (int) ($data['quantity'] ?? 1);
        $cart = session('cart', []);
        $currentQty = (int) ($cart[$product->id] ?? 0);
        $newQty = $currentQty + $addQty;

        if ($product->stock < 1) {
            return back()->with('error', 'Stok produk sedang habis.');
        }

        if ($newQty > $product->stock) {
            return back()->with('error', "Jumlah melebihi stok tersedia ({$product->stock} unit).");
        }

        $cart[$product->id] = $newQty;
        session(['cart' => $cart]);

        return back()->with('success', "{$product->name} ditambahkan ke keranjang.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->ensureNotAdmin();

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($data['quantity'] > $product->stock) {
            return back()->with('error', "Stok tersedia hanya {$product->stock} unit.");
        }

        $cart = session('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id] = (int) $data['quantity'];
            session(['cart' => $cart]);
        }

        return back()->with('success', 'Jumlah keranjang diperbarui.');
    }

    public function remove(Product $product): RedirectResponse
    {
        $this->ensureNotAdmin();

        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function clear(): RedirectResponse
    {
        $this->ensureNotAdmin();

        session()->forget('cart');

        return back()->with('success', 'Keranjang berhasil dikosongkan.');
    }
}