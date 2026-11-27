<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingService;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_name'      => ['required', 'string', 'max:150'],
            'phone'               => ['required', 'string', 'max:30'],
            'address'             => ['required', 'string'],
            'shipping_service_id' => ['required', 'integer', 'exists:shipping_services,id'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.product_id'  => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity'    => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($request, $validated) {
            $shipping = ShippingService::findOrFail($validated['shipping_service_id']);
            $subtotal = 0;
            $preparedItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::whereKey($item['product_id'])->lockForUpdate()->firstOrFail();

                if (! $product->is_active || $product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["Stok produk {$product->name} tidak mencukupi (tersedia: {$product->stock})."],
                    ]);
                }

                $lineTotal = $product->price * $item['quantity'];
                $subtotal += $lineTotal;

                $preparedItems[] = [
                    'product'    => $product,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal'   => $lineTotal,
                ];
            }

            $shippingCost = $shipping->cost ?? $shipping->price ?? 0;
            $grandTotal   = $subtotal + $shippingCost;
            $orderCode    = 'ORD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));

            $order = Order::create([
                'code'                => $orderCode,
                'user_id'             => $request->user()->id,
                'shipping_service_id' => $shipping->id,
                'recipient_name'      => $validated['recipient_name'],
                'phone'               => $validated['phone'],
                'address'             => $validated['address'],
                'shipping_courier'    => $shipping->courier ?? $shipping->name ?? 'Reguler',
                'shipping_service'    => $shipping->service ?? $shipping->name ?? 'Standard',
                'shipping_eta'        => $shipping->eta ?? $shipping->estimation ?? '1-2 hari',
                'subtotal'            => $subtotal,
                'shipping_cost'       => $shippingCost,
                'grand_total'         => $grandTotal,
                'status'              => 'pending',
                'ordered_at'          => now(),
            ]);

            foreach ($preparedItems as $entry) {
                $product = $entry['product'];

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'price'        => $entry['unit_price'],
                    'unit_price'   => $entry['unit_price'],
                    'quantity'     => $entry['quantity'],
                    'subtotal'     => $entry['subtotal'],
                ]);

                $product->decrement('stock', $entry['quantity']);

                StockMovement::create([
                    'product_id'     => $product->id,
                    'user_id'        => $request->user()->id,
                    'type'           => 'OUT',
                    'quantity'       => $entry['quantity'],
                    'reference_type' => Order::class,
                    'reference_id'   => $order->id,
                    'note'           => 'Checkout order ' . $order->code,
                    'movement_at'    => now(),
                ]);
            }

            return $order->load(['items.product', 'shippingService']);
        });

        return response()->json([
            'message' => 'Checkout berhasil.',
            'data'    => $order,
        ], 201);
    }
}