<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingService;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $categories = [
            'Elektronik',
            'Aksesoris',
            'Peralatan Rumah',
        ];

        $categoryMap = [];
        foreach ($categories as $name) {
            $categoryMap[$name] = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }

        $products = [
            [
                'category' => 'Elektronik',
                'sku' => 'ELK-001',
                'name' => 'Mouse Wireless Ergonomis',
                'description' => 'Mouse nirkabel senyap dengan baterai tahan lama dan konektor USB.',
                'price' => 145000,
                'initial_stock' => 25,
                'unit_cost' => 95000,
            ],
            [
                'category' => 'Elektronik',
                'sku' => 'ELK-002',
                'name' => 'Lampu Meja LED Belajar',
                'description' => 'Lampu meja dengan tiga tingkat kecerahan dan leher fleksibel.',
                'price' => 120000,
                'initial_stock' => 20,
                'unit_cost' => 78000,
            ],
            [
                'category' => 'Aksesoris',
                'sku' => 'AKS-001',
                'name' => 'Kabel Data USB-C 2 Meter',
                'description' => 'Kabel nilon braided tahan tekuk mendukung pengisian cepat.',
                'price' => 45000,
                'initial_stock' => 50,
                'unit_cost' => 22000,
            ],
            [
                'category' => 'Aksesoris',
                'sku' => 'AKS-002',
                'name' => 'Sleeve Laptop 14 Inci',
                'description' => 'Sarung pelindung laptop berlapis busa lembut tahan percikan air.',
                'price' => 85000,
                'initial_stock' => 30,
                'unit_cost' => 50000,
            ],
            [
                'category' => 'Peralatan Rumah',
                'sku' => 'RMH-001',
                'name' => 'Teko Listrik Stainless 1.8L',
                'description' => 'Pemanas air otomatis dengan perlindungan mati otomatis saat mendidih.',
                'price' => 165000,
                'initial_stock' => 15,
                'unit_cost' => 110000,
            ],
        ];

        foreach ($products as $item) {
            $product = Product::firstOrCreate(
                ['sku' => $item['sku']],
                [
                    'category_id' => $categoryMap[$item['category']]->id,
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']),
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'stock' => 0,
                    'is_active' => true,
                ]
            );

            if ($product->wasRecentlyCreated && $item['initial_stock'] > 0) {
                $product->increment('stock', $item['initial_stock']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $admin?->id,
                    'type' => 'IN',
                    'quantity' => $item['initial_stock'],
                    'unit_cost' => $item['unit_cost'],
                    'reference' => 'INIT-SEED',
                    'note' => 'Stok awal dari MasterDataSeeder',
                    'movement_at' => now(),
                ]);
            }
        }

        $services = [
            ['courier' => 'JNE', 'service' => 'REG', 'eta' => '2-3 hari', 'cost' => 18000],
            ['courier' => 'JNE', 'service' => 'YES', 'eta' => '1 hari', 'cost' => 32000],
            ['courier' => 'J&T', 'service' => 'EZ', 'eta' => '2-4 hari', 'cost' => 16000],
            ['courier' => 'SiCepat', 'service' => 'BEST', 'eta' => '1-2 hari', 'cost' => 25000],
        ];

        foreach ($services as $svc) {
            ShippingService::firstOrCreate(
                ['courier' => $svc['courier'], 'service' => $svc['service']],
                [
                    'eta' => $svc['eta'],
                    'cost' => $svc['cost'],
                    'is_active' => true,
                ]
            );
        }
    }
}