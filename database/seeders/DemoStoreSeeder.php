<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Shared parameterized seeder for creating a complete demo store ecosystem.
 *
 * Used by BudiSellerPanelSeeder, MarketAkbarSeeder, and AkbarFahlevySellerSeeder.
 * Handles: seller user, store, products, variants, buyers, orders, payments,
 * reviews, finance, promotions, showcases, and stock movements.
 */
final class DemoStoreSeeder extends Seeder
{
    private const PASSWORD = '12345678';

    private const COUNT = 50;

    private const COLORS = ['Hitam', 'Putih', 'Merah', 'Biru', 'Hijau'];

    private const SIZES = ['S', 'M', 'L', 'XL'];

    private const MATERIALS = ['Katun', 'Polyester', 'Kayu', 'Logam', 'Plastik'];

    /**
     * @param array{
     *     user_id: string,
     *     email: string,
     *     name: string,
     *     store_name: string,
     *     store_slug: string,
     *     prefix: string,
     *     avatar_seed: string,
     *     count?: int,
     * } $config
     */
    public static function run(Seeder $seeder, array $config): int
    {
        $count = $config['count'] ?? self::COUNT;
        $now = now();
        $password = Hash::make(self::PASSWORD);
        $prefix = strtoupper($config['prefix']);
        $storeId = null;

        DB::transaction(function () use ($config, $now, $password, $prefix, $count, &$storeId): void {
            $adminId = self::getAdminId();
            $roleIds = DB::table('roles')->pluck('id', 'name');

            $userId = self::ensureUser($config, $now, $password, $adminId);
            self::syncRoles($userId, $roleIds, $now);

            $storeId = self::ensureStore($userId, $config, $now, $adminId);
            self::ensureStoreDetails($userId, $storeId, $config, $now);
            self::ensureShippingSettings($storeId, $now);

            $categoryIds = self::getCategoryIds($adminId, $now);
            $attributes = self::getAttributes($now);
            $products = self::ensureProducts($storeId, $categoryIds, $attributes, $prefix, $count, $now, $adminId);

            $buyers = self::ensureBuyers($prefix, $adminId, $roleIds, $now, $password, $count);
            $orders = self::ensureOrders($buyers, $storeId, $products, $prefix, $count, $now, $adminId);
            self::ensurePayments($orders, $now);
            self::ensureReviews($orders, $products, $buyers, $now, $adminId);

            self::ensureFinance($storeId, $orders, $prefix, $now, $adminId);
            self::ensurePromotions($storeId, $products, $prefix, $count, $now, $adminId);
            self::ensureShowcases($storeId, $products, $now, $adminId);
            self::ensureStockMovements($products, $storeId, $prefix, $now, $adminId);
        });

        if ($seeder->command) {
            $seeder->command->info("Seeder [{$config['name']}] selesai. Login: {$config['email']} / ".self::PASSWORD);
        }

        return $storeId;
    }

    private static function getAdminId(): string
    {
        return DB::table('users')->where('id', SeederIds::SUPER_ADMIN)->value('id')
            ?? DB::table('users')->orderBy('created_at')->value('id');
    }

    private static function ensureUser(array $config, Carbon $now, string $password, string $adminId): string
    {
        $existing = DB::table('users')->where('email', $config['email'])->first(['id']);

        $data = [
            'firebase_uid' => $existing?->firebase_uid ?? null,
            'email' => $config['email'],
            'password' => $password,
            'name' => $config['name'],
            'avatar' => "https://i.pravatar.cc/300?u={$config['avatar_seed']}",
            'is_email_verified' => true,
            'is_active' => true,
            'banned_at' => null,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'updated_at' => $now,
            'deleted_at' => null,
        ];

        if ($existing) {
            DB::table('users')->where('id', $existing->id)->update($data);

            return (string) $existing->id;
        }

        $data['id'] = $config['user_id'];
        $data['created_at'] = $now;

        DB::table('users')->insert($data);

        return $config['user_id'];
    }

    private static function syncRoles(string $userId, $roleIds, Carbon $now): void
    {
        foreach (['seller', 'buyer'] as $roleName) {
            if (isset($roleIds[$roleName])) {
                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $userId, 'role_id' => $roleIds[$roleName]],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    private static function ensureStore(string $userId, array $config, Carbon $now, string $adminId): int
    {
        DB::table('stores')->updateOrInsert(
            ['user_id' => $userId],
            [
                'name' => $config['store_name'],
                'slug' => $config['store_slug'],
                'description' => "Toko testing lengkap untuk {$config['name']}.",
                'short_description' => "Toko {$config['name']} untuk testing seller panel.",
                'phone' => '081234567800',
                'email' => $config['email'],
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'address' => 'Jl. Testing Marketplace No. 1, Jakarta Pusat',
                'status' => 'approved',
                'is_active' => true,
                'logo' => "https://picsum.photos/seed/{$config['avatar_seed']}-logo/500/500",
                'banner_url' => "https://picsum.photos/seed/{$config['avatar_seed']}-banner/1600/500",
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]
        );

        return (int) DB::table('stores')->where('user_id', $userId)->value('id');
    }

    private static function ensureStoreDetails(string $userId, int $storeId, array $config, Carbon $now): void
    {
        if (! Schema::hasTable('store_details')) {
            return;
        }

        DB::table('store_details')->updateOrInsert(
            ['store_id' => $storeId],
            [
                'owner_name' => $config['name'],
                'owner_phone' => '081234567800',
                'description' => "Toko {$config['name']} untuk pengujian seluruh alur seller panel.",
                'shipping_policy' => 'Pesanan diproses maksimal satu hari kerja.',
                'return_policy' => 'Retur dapat diajukan maksimal tujuh hari setelah diterima.',
                'open_days' => 'senin-minggu',
                'open_time' => '08:00:00',
                'close_time' => '22:00:00',
                'whatsapp_url' => 'https://wa.me/6281234567800',
                'instagram_url' => null,
                'tiktok_url' => null,
                'website_url' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private static function ensureShippingSettings(int $storeId, Carbon $now): void
    {
        if (! Schema::hasTable('shipping_settings')) {
            return;
        }

        DB::table('shipping_settings')->updateOrInsert(
            ['store_id' => $storeId],
            [
                'store_latitude' => -6.17539240,
                'store_longitude' => 106.82715280,
                'free_shipping_max_distance' => 5,
                'default_flat_rate' => 15000,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private static function getCategoryIds(string $adminId, Carbon $now): array
    {
        $existing = DB::table('categories')->where('is_active', true)->whereNull('deleted_at')
            ->orderBy('id')->limit(20)->pluck('id')->all();

        if ($existing !== []) {
            return $existing;
        }

        $groupId = DB::table('catalog_groups')->insertGetId([
            'name' => 'Demo Catalog',
            'slug' => 'demo-catalog',
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        $categories = [];

        for ($i = 1; $i <= 10; $i++) {
            $slug = sprintf('demo-category-%02d', $i);
            $categories[] = (int) DB::table('categories')->insertGetId([
                'catalog_group_id' => $groupId,
                'parent_id' => null,
                'parent_scope_id' => 0,
                'level' => 1,
                'sort_order' => $i,
                'is_active' => true,
                'is_visible_in_menu' => true,
                'name' => sprintf('Demo Category %02d', $i),
                'slug' => $slug,
                'full_slug' => $slug,
                'image_url' => null,
                'icon_url' => null,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }

        return $categories;
    }

    private static function getAttributes(Carbon $now): array
    {
        foreach (['warna', 'ukuran', 'bahan'] as $attr) {
            DB::table('product_attributes')->updateOrInsert(
                ['name' => $attr],
                ['name' => $attr, 'slug' => $attr, 'type' => 'select', 'created_at' => $now, 'updated_at' => $now]
            );
        }

        return DB::table('product_attributes')
            ->whereIn('slug', ['warna', 'ukuran', 'bahan'])
            ->pluck('id', 'slug')
            ->all();
    }

    private static function ensureProducts(int $storeId, array $categoryIds, array $attributes, string $prefix, int $count, Carbon $now, string $adminId): array
    {
        $products = [];

        for ($i = 1; $i <= $count; $i++) {
            $categoryId = $categoryIds[($i - 1) % count($categoryIds)];
            $slug = sprintf('%s-product-%03d', strtolower($prefix), $i);
            $createdAt = $now->copy()->subDays(60 - min(59, $i % 60))->subMinutes($i * 2);
            $status = $i % 17 === 0 ? 'draft' : ($i % 29 === 0 ? 'archived' : 'published');

            $productId = (int) DB::table('products')->insertGetId([
                'store_id' => $storeId,
                'primary_category_id' => $categoryId,
                'name' => sprintf('Produk %s Testing %03d', $prefix, $i),
                'slug' => $slug,
                'description' => sprintf('Produk testing nomor %03d dengan data variant, stok, dan review.', $i),
                'brand' => sprintf('%s Brand %02d', $prefix, (($i - 1) % 10) + 1),
                'thumbnail' => "https://picsum.photos/seed/{$slug}/900/900",
                'status' => $status,
                'is_active' => $i % 23 !== 0,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);

            if (Schema::hasTable('product_categories')) {
                DB::table('product_categories')->insert([
                    'product_id' => $productId,
                    'category_id' => $categoryId,
                    'is_primary' => true,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            if (isset($attributes['bahan']) && Schema::hasTable('product_attribute_values')) {
                DB::table('product_attribute_values')->insert([
                    'product_id' => $productId,
                    'attribute_id' => $attributes['bahan'],
                    'value' => self::MATERIALS[($i - 1) % count(self::MATERIALS)],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $variantCount = $i % 4 === 0 ? 3 : ($i % 3 === 0 ? 2 : 1);
            $variants = [];

            for ($v = 1; $v <= $variantCount; $v++) {
                $price = 50000 + (($i * 37000 + $v * 7000) % 1950000);
                $stock = $v === 1 && $i % 20 === 0 ? 0 : ($v === 1 && $i % 13 === 0 ? 5 : 160 + (($i * 13 + $v * 7) % 240));
                $variantName = $variantCount === 1
                    ? 'Default'
                    : self::COLORS[($i + $v) % count(self::COLORS)].' '.self::SIZES[($i + $v) % count(self::SIZES)];

                $variantId = (int) DB::table('product_variants')->insertGetId([
                    'product_id' => $productId,
                    'store_id' => $storeId,
                    'sku' => sprintf('%s-%03d-%02d', $prefix, $i, $v),
                    'name' => $variantName,
                    'price' => $price,
                    'stock' => $stock,
                    'is_default' => $v === 1,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if ($variantCount > 1 && Schema::hasTable('product_variant_values')) {
                    if (isset($attributes['warna'])) {
                        DB::table('product_variant_values')->insert([
                            'variant_id' => $variantId,
                            'attribute_id' => $attributes['warna'],
                            'value' => self::COLORS[($i + $v) % count(self::COLORS)],
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);
                    }
                    if (isset($attributes['ukuran'])) {
                        DB::table('product_variant_values')->insert([
                            'variant_id' => $variantId,
                            'attribute_id' => $attributes['ukuran'],
                            'value' => self::SIZES[($i + $v) % count(self::SIZES)],
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);
                    }
                }

                $variants[] = ['id' => $variantId, 'sku' => sprintf('%s-%03d-%02d', $prefix, $i, $v), 'price' => $price, 'stock' => $stock];
            }

            if (Schema::hasTable('product_images')) {
                for ($img = 1; $img <= 3; $img++) {
                    DB::table('product_images')->insert([
                        'product_id' => $productId,
                        'url' => "https://picsum.photos/seed/{$slug}-{$img}/900/900",
                        'alt_text' => sprintf('Produk %s Testing %03d gambar %d', $prefix, $i, $img),
                        'is_primary' => $img === 1,
                        'sort_order' => $img - 1,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }

            $products[] = [
                'id' => $productId,
                'name' => sprintf('Produk %s Testing %03d', $prefix, $i),
                'slug' => $slug,
                'variant_id' => $variants[0]['id'],
                'sku' => $variants[0]['sku'],
                'price' => $variants[0]['price'],
                'stock' => $variants[0]['stock'],
                'index' => $i,
            ];
        }

        return $products;
    }

    private static function ensureBuyers(string $prefix, string $adminId, $roleIds, Carbon $now, string $password, int $count): array
    {
        $roleId = $roleIds['buyer'] ?? null;
        $buyers = [];

        for ($i = 1; $i <= min($count, 30); $i++) {
            $id = (string) Str::uuid();
            $email = sprintf('%s.buyer.%03d@marketku.test', strtolower($prefix), $i);
            $createdAt = $now->copy()->subDays(100 - $i)->subMinutes($i * 3);

            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'id' => $id,
                    'firebase_uid' => null,
                    'password' => $password,
                    'name' => sprintf('Buyer %s Test %03d', $prefix, $i),
                    'avatar' => 'https://i.pravatar.cc/300?u='.urlencode($email),
                    'is_email_verified' => true,
                    'is_active' => true,
                    'banned_at' => null,
                    'remember_token' => null,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'deleted_at' => null,
                ]
            );

            if ($roleId) {
                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $id, 'role_id' => $roleId],
                    ['created_at' => $createdAt, 'updated_at' => $createdAt]
                );
            }

            if (Schema::hasTable('addresses')) {
                DB::table('addresses')->updateOrInsert(
                    ['user_id' => $id, 'label' => 'Rumah'],
                    [
                        'store_id' => null,
                        'country' => 'Indonesia',
                        'province' => ['DKI Jakarta', 'Jawa Barat', 'Jawa Timur', 'Jawa Tengah', 'Banten'][($i - 1) % 5],
                        'city_or_regency' => ['Jakarta', 'Bandung', 'Surabaya', 'Semarang', 'Tangerang'][($i - 1) % 5],
                        'district' => 'Kecamatan Test '.(($i % 10) + 1),
                        'subdistrict' => 'Kelurahan Test '.(($i % 15) + 1),
                        'postal_code' => (string) (10100 + $i),
                        'full_address' => sprintf('Jl. Buyer Testing No. %d', $i),
                        'notes' => null,
                        'label' => 'Rumah',
                        'recipient_name' => sprintf('Buyer %s Test %03d', $prefix, $i),
                        'phone_number' => '0813'.str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                        'latitude' => -6.2 + (($i % 20) * 0.001),
                        'longitude' => 106.8 + (($i % 20) * 0.001),
                        'komerce_destination_id' => null,
                        'is_primary' => true,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]
                );
            }

            $buyers[] = ['id' => $id, 'name' => sprintf('Buyer %s Test %03d', $prefix, $i), 'email' => $email, 'index' => $i];
        }

        return $buyers;
    }

    private static function ensureOrders(array $buyers, int $storeId, array $products, string $prefix, int $count, Carbon $now, string $adminId): array
    {
        if (! Schema::hasTable('orders') || $buyers === [] || $products === []) {
            return [];
        }

        $orders = [];

        for ($i = 1; $i <= min($count, 30); $i++) {
            $buyer = $buyers[($i - 1) % count($buyers)];
            $product = $products[($i - 1) % count($products)];
            $createdAt = $now->copy()->subDays(30 - min(29, $i % 30))->addHours($i);
            $status = $i % 5 === 0 ? 'cancelled' : ($i % 3 === 0 ? 'completed' : 'processing');
            $subtotal = $product['price'] * max(1, $i % 4);
            $shippingCost = 15000;
            $total = $subtotal + $shippingCost;

            $orderId = (int) DB::table('orders')->insertGetId([
                'order_number' => self::generateOrderNumber($prefix, $i),
                'user_id' => $buyer['id'],
                'store_id' => $storeId,
                'status' => $status,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'payment_method' => $i % 2 === 0 ? 'midtrans' : 'cod',
                'payment_status' => $status === 'cancelled' ? 'failed' : 'paid',
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);

            if (Schema::hasTable('sub_orders')) {
                DB::table('sub_orders')->insert([
                    'order_id' => $orderId,
                    'store_id' => $storeId,
                    'status' => $status,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'total' => $total,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            if (Schema::hasTable('order_items')) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $product['id'],
                    'variant_id' => $product['variant_id'],
                    'quantity' => max(1, $i % 4),
                    'price' => $product['price'],
                    'subtotal' => $subtotal,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $orders[] = ['id' => $orderId, 'number' => self::generateOrderNumber($prefix, $i), 'buyer' => $buyer, 'product' => $product, 'total' => $total, 'status' => $status, 'index' => $i];
        }

        return $orders;
    }

    private static function ensurePayments(array $orders, Carbon $now): void
    {
        if (! Schema::hasTable('payments') || $orders === []) {
            return;
        }

        foreach ($orders as $order) {
            DB::table('payments')->updateOrInsert(
                ['order_number' => $order['number']],
                [
                    'transaction_id' => sprintf('TXN-%s-%04d', strtoupper(substr($order['number'], -8)), $order['index']),
                    'payment_method' => $order['index'] % 2 === 0 ? 'midtrans' : 'cod',
                    'amount' => $order['total'],
                    'status' => $order['status'] === 'cancelled' ? 'failed' : 'success',
                    'created_at' => $now->copy()->subDays(30 - $order['index']),
                    'updated_at' => $now,
                ]
            );
        }
    }

    private static function ensureReviews(array $orders, array $products, array $buyers, Carbon $now, string $adminId): void
    {
        if (! Schema::hasTable('product_reviews')) {
            return;
        }

        foreach ($orders as $order) {
            if ($order['status'] !== 'completed') {
                continue;
            }

            DB::table('product_reviews')->updateOrInsert(
                ['order_id' => $order['id'], 'product_id' => $order['product']['id']],
                [
                    'user_id' => $order['buyer']['id'],
                    'rating' => 3 + ($order['index'] % 3),
                    'comment' => "Review untuk order {$order['number']}.",
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now->copy()->subDays(29 - $order['index']),
                    'updated_at' => $now,
                ]
            );
        }
    }

    private static function ensureFinance(int $storeId, array $orders, string $prefix, Carbon $now, string $adminId): void
    {
        if (! Schema::hasTable('financial_transactions')) {
            return;
        }

        foreach ($orders as $order) {
            $fee = (int) ($order['total'] * 0.05);

            DB::table('financial_transactions')->updateOrInsert(
                ['reference_number' => sprintf('%s-FIN-%04d', $prefix, $order['index'])],
                [
                    'store_id' => $storeId,
                    'type' => 'income',
                    'category' => 'order_payment',
                    'amount' => $order['total'],
                    'fee' => $fee,
                    'net_amount' => $order['total'] - $fee,
                    'description' => "Pembayaran order {$order['number']}",
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now->copy()->subDays(29 - $order['index']),
                    'updated_at' => $now,
                ]
            );
        }
    }

    private static function ensurePromotions(int $storeId, array $products, string $prefix, int $count, Carbon $now, string $adminId): void
    {
        if (! Schema::hasTable('promotions') || $products === []) {
            return;
        }

        for ($i = 1; $i <= min($count, 10); $i++) {
            $product = $products[($i - 1) % count($products)];
            $createdAt = $now->copy()->subHours($count - $i);
            $approval = $i % 4 === 0 ? 'pending' : 'approved';

            DB::table('promotions')->updateOrInsert(
                ['name' => sprintf('%s Test Promotion %03d', $prefix, $i)],
                [
                    'store_id' => $storeId,
                    'name' => sprintf('%s Test Promotion %03d', $prefix, $i),
                    'image_url' => "https://picsum.photos/seed/{$prefix}-promo-{$i}/1600/500",
                    'mobile_image_url' => "https://picsum.photos/seed/{$prefix}-promo-mobile-{$i}/900/900",
                    'click_action' => 'product',
                    'target_id' => $product['id'],
                    'target_url' => null,
                    'sort_order' => $i,
                    'is_active' => $i % 7 !== 0,
                    'approval_status' => $approval,
                    'approved_at' => $approval === 'approved' ? $createdAt->copy()->addMinutes(10) : null,
                    'approved_by' => $approval === 'approved' ? $adminId : null,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'deleted_at' => null,
                ]
            );
        }
    }

    private static function ensureShowcases(int $storeId, array $products, Carbon $now, string $adminId): void
    {
        if (! Schema::hasTable('showcases') || $products === []) {
            return;
        }

        $showcaseId = (int) DB::table('showcases')->insertGetId([
            'store_id' => $storeId,
            'name' => 'Etalase Utama',
            'slug' => 'etalase-utama',
            'description' => 'Etalase produk unggulan.',
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        for ($i = 0; $i < min(10, count($products)); $i++) {
            if (Schema::hasTable('showcase_products')) {
                DB::table('showcase_products')->insert([
                    'showcase_id' => $showcaseId,
                    'product_id' => $products[$i]['id'],
                    'sort_order' => $i + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private static function ensureStockMovements(array $products, int $storeId, string $prefix, Carbon $now, string $adminId): void
    {
        if (! Schema::hasTable('stock_movements') || $products === []) {
            return;
        }

        foreach ($products as $product) {
            $qty = $product['stock'] + 50;

            DB::table('stock_movements')->insert([
                'variant_id' => $product['variant_id'],
                'store_id' => $storeId,
                'type' => 'adjustment',
                'quantity' => $qty,
                'balance_after' => $qty,
                'note' => 'Stok awal dari seeder',
                'movement_key' => sprintf('%s-stock-%03d', strtolower($prefix), $product['index']),
                'created_by' => $adminId,
                'created_at' => $now->copy()->subDays(60),
                'updated_at' => $now,
            ]);
        }
    }

    private static function generateOrderNumber(string $prefix, int $index): string
    {
        return sprintf('%s-ORD-%04d', $prefix, $index);
    }
}
