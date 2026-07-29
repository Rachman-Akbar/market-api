<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $storeIds = DB::table('stores')->pluck('id', 'slug');
        $categoryIds = DB::table('categories')->pluck('id', 'slug');
        $attributes = [
            ['name' => 'warna', 'slug' => 'warna', 'type' => 'select'],
            ['name' => 'ukuran', 'slug' => 'ukuran', 'type' => 'select'],
        ];

        DB::table('product_attributes')->upsert(
            array_map(fn (array $attribute): array => [
                ...$attribute,
                'created_at' => $now,
                'updated_at' => $now,
            ], $attributes),
            ['name'],
            ['slug', 'type', 'updated_at']
        );

        $attributeIds = DB::table('product_attributes')->pluck('id', 'slug');
        $products = [
            ['store' => 'sari-nusantara', 'category' => 'sate', 'name' => 'sate ayam madura', 'slug' => 'sate-ayam-madura', 'brand' => 'sari nusantara', 'price' => 35000, 'stock' => 60, 'sku' => 'SARI-SATE-001'],
            ['store' => 'sari-nusantara', 'category' => 'nasi-goreng', 'name' => 'nasi goreng kampung', 'slug' => 'nasi-goreng-kampung', 'brand' => 'sari nusantara', 'price' => 28000, 'stock' => 45, 'sku' => 'SARI-NASGOR-001'],
            ['store' => 'sari-nusantara', 'category' => 'selimut', 'name' => 'selimut katun premium', 'slug' => 'selimut-katun-premium', 'brand' => 'sari nusantara', 'price' => 279000, 'stock' => 25, 'sku' => 'SARI-SLM-001'],
            ['store' => 'sari-nusantara', 'category' => 'lampu-meja', 'name' => 'lampu meja rotan', 'slug' => 'lampu-meja-rotan', 'brand' => 'sari nusantara', 'price' => 329000, 'stock' => 20, 'sku' => 'SARI-LMR-001'],
            ['store' => 'raka-teknologi', 'category' => 'speaker', 'name' => 'speaker bluetooth mini', 'slug' => 'speaker-bluetooth-mini', 'brand' => 'raka teknologi', 'price' => 239000, 'stock' => 55, 'sku' => 'RAKA-SPK-001'],
            ['store' => 'raka-teknologi', 'category' => 'headphone', 'name' => 'headphone wireless pro', 'slug' => 'headphone-wireless-pro', 'brand' => 'raka teknologi', 'price' => 499000, 'stock' => 32, 'sku' => 'RAKA-HP-001'],
            ['store' => 'raka-teknologi', 'category' => 'charger', 'name' => 'charger cepat 45 watt', 'slug' => 'charger-cepat-45-watt', 'brand' => 'raka teknologi', 'price' => 189000, 'stock' => 70, 'sku' => 'RAKA-CHG-001'],
            ['store' => 'raka-teknologi', 'category' => 'kabel-data', 'name' => 'kabel data usb c', 'slug' => 'kabel-data-usb-c', 'brand' => 'raka teknologi', 'price' => 79000, 'stock' => 100, 'sku' => 'RAKA-KBL-001'],
        ];

        foreach ($products as $product) {
            $storeId = $storeIds[$product['store']] ?? null;
            $categoryId = $categoryIds[$product['category']] ?? null;

            if (! $storeId || ! $categoryId) {
                continue;
            }

            DB::table('products')->updateOrInsert(
                ['store_id' => $storeId, 'name' => $product['name']],
                [
                    'primary_category_id' => $categoryId,
                    'slug' => $product['slug'],
                    'description' => 'Produk aktif untuk pengujian marketplace dan halaman detail toko.',
                    'brand' => $product['brand'],
                    'thumbnail' => 'https://picsum.photos/seed/'.$product['slug'].'/900/900',
                    'status' => 'published',
                    'is_active' => true,
                    'created_by' => $product['store'] === 'sari-nusantara' ? SeederIds::SELLER_ONE : SeederIds::SELLER_TWO,
                    'updated_by' => $product['store'] === 'sari-nusantara' ? SeederIds::SELLER_ONE : SeederIds::SELLER_TWO,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );

            $productId = (int) DB::table('products')->where('slug', $product['slug'])->value('id');

            DB::table('product_categories')->updateOrInsert(
                ['product_id' => $productId, 'category_id' => $categoryId],
                ['is_primary' => true, 'created_at' => $now, 'updated_at' => $now]
            );

            DB::table('product_variants')->updateOrInsert(
                ['store_id' => $storeId, 'sku' => $product['sku']],
                [
                    'product_id' => $productId,
                    'name' => 'default',
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'is_default' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('product_images')->updateOrInsert(
                ['product_id' => $productId, 'sort_order' => 0],
                [
                    'url' => 'https://picsum.photos/seed/'.$product['slug'].'-main/900/900',
                    'alt_text' => $product['name'],
                    'is_primary' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            if (isset($attributeIds['warna'])) {
                DB::table('product_attribute_values')->updateOrInsert(
                    ['product_id' => $productId, 'attribute_id' => $attributeIds['warna']],
                    ['value' => 'default', 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }
}
