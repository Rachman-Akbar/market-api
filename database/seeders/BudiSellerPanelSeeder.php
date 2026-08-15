<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class BudiSellerPanelSeeder extends Seeder
{
    private const PASSWORD = '12345678';
    private const COUNT = 100;

    public function run(): void
    {
        $required = [
            'users',
            'roles',
            'user_roles',
            'stores',
            'products',
            'product_variants',
            'orders',
            'sub_orders',
            'order_items',
        ];

        $missing = array_values(array_filter($required, static fn (string $table): bool => ! Schema::hasTable($table)));

        if ($missing !== []) {
            if ($this->command) {
                $this->command->warn('BudiSellerPanelSeeder dilewati. Tabel belum tersedia: '.implode(', ', $missing));
            }

            return;
        }

        $now = now();

        DB::transaction(function () use ($now): void {
            $budiId = $this->ensureBudi($now);
            $storeId = $this->ensureBudiStore($budiId, $now);
            $this->cleanPreviousData($storeId);
            $buyers = $this->seedBuyers($budiId, $now);
            $categories = $this->categories($budiId, $now);
            $attributes = $this->attributes($now);
            $products = $this->seedProducts($budiId, $storeId, $categories, $attributes, $now);
            $this->seedBanners($budiId, $storeId, $now);
            $vouchers = $this->seedVouchers($budiId, $storeId, $now);
            $this->seedPromotions($budiId, $storeId, $products, $categories, $now);
            $orders = $this->seedOrders($budiId, $storeId, $buyers, $products, $vouchers, $now);
            $this->seedReviews($budiId, $orders, $now);
            $this->seedInventory($budiId, $storeId, $products, $orders, $now);
            $this->seedRawMaterialsAndCosting($storeId, $products, $now);
            $this->seedFinance($budiId, $storeId, $buyers, $orders, $now);
            $this->seedShowcases($budiId, $storeId, $products, $now);
            $this->seedSupport($budiId, $storeId, $buyers, $orders, $now);
            $this->seedChat($budiId, $storeId, $buyers, $orders, $now);
            $this->seedNotifications($budiId, $storeId, $orders, $now);
        });

        if ($this->command) {
            $this->command->info('Seller Panel Budi terisi data testing lengkap. Login: budi@gmail.com / 12345678');
        }
    }

    private function ensureBudi(Carbon $now): string
    {
        $budiId = SeederIds::SUPER_ADMIN;
        $password = Hash::make(self::PASSWORD);

        DB::table('users')->updateOrInsert(
            ['email' => 'budi@gmail.com'],
            [
                'id' => $budiId,
                'firebase_uid' => null,
                'password' => $password,
                'name' => 'Budi Administrator',
                'avatar' => 'https://i.pravatar.cc/300?u=budi@gmail.com',
                'is_email_verified' => true,
                'is_active' => true,
                'banned_at' => null,
                'created_by' => $budiId,
                'updated_by' => $budiId,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]
        );

        $budiId = (string) DB::table('users')->where('email', 'budi@gmail.com')->value('id');
        $roles = DB::table('roles')->whereIn('name', ['super_admin', 'admin', 'seller', 'buyer'])->pluck('id');

        foreach ($roles as $roleId) {
            DB::table('user_roles')->updateOrInsert(
                ['user_id' => $budiId, 'role_id' => $roleId],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        return $budiId;
    }

    private function ensureBudiStore(string $budiId, Carbon $now): int
    {
        DB::table('stores')->updateOrInsert(
            ['user_id' => $budiId],
            [
                'name' => 'budi marketplace lab',
                'slug' => 'budi-marketplace-lab',
                'description' => 'Toko testing lengkap untuk seluruh alur Seller Panel.',
                'short_description' => 'Seller testing Budi dengan data lengkap.',
                'phone' => '081234567800',
                'email' => 'toko.budi@gmail.com',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'address' => 'Jl. Testing Marketplace No. 1 Jakarta Pusat',
                'status' => 'approved',
                'is_active' => true,
                'logo' => 'https://picsum.photos/seed/budi-store-logo/500/500',
                'banner_url' => 'https://picsum.photos/seed/budi-store-banner/1600/500',
                'created_by' => $budiId,
                'updated_by' => $budiId,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]
        );

        $storeId = (int) DB::table('stores')->where('user_id', $budiId)->value('id');

        if (Schema::hasTable('store_details')) {
            DB::table('store_details')->updateOrInsert(
                ['store_id' => $storeId],
                [
                    'owner_name' => 'Budi Administrator',
                    'owner_phone' => '081234567800',
                    'description' => 'Toko Budi untuk pengujian produk, order, customer, finance, inventory, chat, review, voucher, promosi, dan etalase.',
                    'shipping_policy' => 'Pesanan diproses maksimal satu hari kerja untuk kebutuhan testing.',
                    'return_policy' => 'Retur testing dapat diajukan maksimal tujuh hari setelah diterima.',
                    'open_days' => 'senin-minggu',
                    'open_time' => '08:00:00',
                    'close_time' => '22:00:00',
                    'whatsapp_url' => 'https://wa.me/6281234567800',
                    'instagram_url' => 'https://instagram.com/budimarketplacelab',
                    'tiktok_url' => null,
                    'website_url' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        if (Schema::hasTable('addresses')) {
            DB::table('addresses')->updateOrInsert(
                ['store_id' => $storeId],
                [
                    'user_id' => $budiId,
                    'country' => 'Indonesia',
                    'province' => 'DKI Jakarta',
                    'city_or_regency' => 'Jakarta Pusat',
                    'district' => 'Gambir',
                    'subdistrict' => 'Gambir',
                    'postal_code' => '10110',
                    'full_address' => 'Jl. Testing Marketplace No. 1 Jakarta Pusat',
                    'notes' => 'Gudang dan toko utama Budi',
                    'label' => 'Toko Utama',
                    'recipient_name' => 'Budi Marketplace Lab',
                    'phone_number' => '081234567800',
                    'latitude' => -6.17539240,
                    'longitude' => 106.82715280,
                    'komerce_destination_id' => null,
                    'is_primary' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        if (Schema::hasTable('shipping_settings')) {
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

        return $storeId;
    }

    private function cleanPreviousData(int $storeId): void
    {
        $orderIds = DB::table('orders')->where('order_number', 'like', 'BUDI-TEST-%')->pluck('id');

        if ($orderIds->isNotEmpty()) {
            DB::table('orders')->whereIn('id', $orderIds)->delete();
        }

        if (Schema::hasTable('conversations')) {
            DB::table('conversations')->where('subject', 'like', 'BUDI-TEST-CHAT-%')->delete();
        }

        if (Schema::hasTable('support_tickets')) {
            DB::table('support_tickets')->where('ticket_number', 'like', 'BUDI-TEST-TICKET-%')->delete();
        }

        if (Schema::hasTable('financial_transactions')) {
            DB::table('financial_transactions')->where('reference_number', 'like', 'BUDI-TEST-FIN-%')->delete();
        }

        if (Schema::hasTable('admin_notifications')) {
            DB::table('admin_notifications')->where('type', 'like', 'budi_test_%')->delete();
        }

        if (Schema::hasTable('promotion_payments')) {
            DB::table('promotion_payments')->where('payment_number', 'like', 'BUDI-TEST-PROMO-PAY-%')->delete();
        }

        if (Schema::hasTable('promotions')) {
            DB::table('promotions')->where('name', 'like', 'Budi Test Promotion %')->delete();
        }

        if (Schema::hasTable('vouchers')) {
            DB::table('vouchers')->where('code', 'like', 'BUDITEST%')->delete();
        }

        if (Schema::hasTable('banners')) {
            DB::table('banners')->where('store_id', $storeId)->where('name', 'like', 'Budi Test Banner %')->delete();
        }

        if (Schema::hasTable('showcases')) {
            DB::table('showcases')->where('store_id', $storeId)->where('slug', 'like', 'budi-test-showcase-%')->delete();
        }

        if (Schema::hasTable('stock_movements')) {
            DB::table('stock_movements')->where('movement_key', 'like', 'budi-test-stock-%')->delete();
        }

        $productIds = DB::table('products')->where('store_id', $storeId)->where('slug', 'like', 'budi-test-product-%')->pluck('id');

        if ($productIds->isNotEmpty()) {
            DB::table('products')->whereIn('id', $productIds)->delete();
        }

        if (Schema::hasTable('raw_materials')) {
            DB::table('raw_materials')->where('store_id', $storeId)->where('code', 'like', 'BUDI-RM-%')->delete();
        }

        DB::table('users')->where('email', 'like', 'budi.test.buyer.%@marketku.test')->delete();
    }

    private function seedBuyers(string $budiId, Carbon $now): array
    {
        $roleId = DB::table('roles')->where('name', 'buyer')->value('id');
        $buyers = [];
        $password = Hash::make(self::PASSWORD);

        for ($i = 1; $i <= self::COUNT; $i++) {
            $id = (string) Str::uuid();
            $email = sprintf('budi.test.buyer.%03d@marketku.test', $i);
            $createdAt = $now->copy()->subDays(100 - $i)->subMinutes($i * 3);

            DB::table('users')->insert([
                'id' => $id,
                'firebase_uid' => null,
                'email' => $email,
                'password' => $password,
                'name' => sprintf('Buyer Budi Test %03d', $i),
                'avatar' => sprintf('https://i.pravatar.cc/300?u=%s', urlencode($email)),
                'is_email_verified' => true,
                'is_active' => true,
                'banned_at' => null,
                'remember_token' => null,
                'created_by' => $budiId,
                'updated_by' => $budiId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);

            if ($roleId) {
                DB::table('user_roles')->insert([
                    'user_id' => $id,
                    'role_id' => $roleId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            if (Schema::hasTable('addresses')) {
                DB::table('addresses')->insert([
                    'user_id' => $id,
                    'store_id' => null,
                    'country' => 'Indonesia',
                    'province' => ['DKI Jakarta', 'Jawa Barat', 'Jawa Timur', 'Jawa Tengah', 'Banten'][($i - 1) % 5],
                    'city_or_regency' => ['Jakarta', 'Bandung', 'Surabaya', 'Semarang', 'Tangerang'][($i - 1) % 5],
                    'district' => 'Kecamatan Test '.(($i % 10) + 1),
                    'subdistrict' => 'Kelurahan Test '.(($i % 15) + 1),
                    'postal_code' => (string) (10100 + $i),
                    'full_address' => sprintf('Jl. Buyer Testing Budi No. %d', $i),
                    'notes' => null,
                    'label' => 'Rumah',
                    'recipient_name' => sprintf('Buyer Budi Test %03d', $i),
                    'phone_number' => '0813'.str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                    'latitude' => -6.2 + (($i % 20) * 0.001),
                    'longitude' => 106.8 + (($i % 20) * 0.001),
                    'komerce_destination_id' => null,
                    'is_primary' => true,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $buyers[] = ['id' => $id, 'name' => sprintf('Buyer Budi Test %03d', $i), 'email' => $email, 'index' => $i];
        }

        return $buyers;
    }

    private function categories(string $budiId, Carbon $now): array
    {
        $categories = DB::table('categories')->where('is_active', true)->whereNull('deleted_at')->orderBy('id')->limit(20)->pluck('id')->all();

        if ($categories !== []) {
            return $categories;
        }

        $groupId = DB::table('catalog_groups')->insertGetId([
            'name' => 'Budi Testing Catalog',
            'slug' => 'budi-testing-catalog',
            'is_active' => true,
            'created_by' => $budiId,
            'updated_by' => $budiId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        for ($i = 1; $i <= 10; $i++) {
            $slug = sprintf('budi-testing-category-%02d', $i);
            $categories[] = (int) DB::table('categories')->insertGetId([
                'catalog_group_id' => $groupId,
                'parent_id' => null,
                'parent_scope_id' => 0,
                'level' => 1,
                'sort_order' => $i,
                'is_active' => true,
                'is_visible_in_menu' => true,
                'name' => sprintf('Budi Testing Category %02d', $i),
                'slug' => $slug,
                'full_slug' => $slug,
                'image_url' => null,
                'icon_url' => null,
                'created_by' => $budiId,
                'updated_by' => $budiId,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }

        return $categories;
    }

    private function attributes(Carbon $now): array
    {
        $attributes = [
            ['name' => 'warna', 'slug' => 'warna', 'type' => 'select'],
            ['name' => 'ukuran', 'slug' => 'ukuran', 'type' => 'select'],
            ['name' => 'bahan', 'slug' => 'bahan', 'type' => 'select'],
        ];

        foreach ($attributes as $attribute) {
            DB::table('product_attributes')->updateOrInsert(
                ['name' => $attribute['name']],
                [...$attribute, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        return DB::table('product_attributes')->whereIn('slug', ['warna', 'ukuran', 'bahan'])->pluck('id', 'slug')->all();
    }

    private function seedProducts(string $budiId, int $storeId, array $categories, array $attributes, Carbon $now): array
    {
        $products = [];
        $colors = ['Hitam', 'Putih', 'Merah', 'Biru', 'Hijau'];
        $sizes = ['S', 'M', 'L', 'XL'];

        for ($i = 1; $i <= self::COUNT; $i++) {
            $categoryId = $categories[($i - 1) % count($categories)];
            $slug = sprintf('budi-test-product-%03d', $i);
            $createdAt = $now->copy()->subDays(60 - min(59, $i % 60))->subMinutes($i * 2);
            $status = $i % 17 === 0 ? 'draft' : ($i % 29 === 0 ? 'archived' : 'published');
            $productId = (int) DB::table('products')->insertGetId([
                'store_id' => $storeId,
                'primary_category_id' => $categoryId,
                'name' => sprintf('Produk Budi Testing %03d', $i),
                'slug' => $slug,
                'description' => sprintf('Produk testing Seller Panel Budi nomor %03d dengan data variant, stok, HPP, review, dan histori transaksi.', $i),
                'brand' => sprintf('Budi Brand %02d', (($i - 1) % 10) + 1),
                'thumbnail' => sprintf('https://picsum.photos/seed/%s/900/900', $slug),
                'status' => $status,
                'is_active' => $i % 23 !== 0,
                'created_by' => $budiId,
                'updated_by' => $budiId,
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
                    'value' => ['Katun', 'Polyester', 'Kayu', 'Logam', 'Plastik'][($i - 1) % 5],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $variantCount = $i % 4 === 0 ? 3 : ($i % 3 === 0 ? 2 : 1);
            $variants = [];

            for ($v = 1; $v <= $variantCount; $v++) {
                $price = 50000 + (($i * 37000 + $v * 7000) % 1950000);
                $stock = $v === 1 && $i % 20 === 0 ? 0 : ($v === 1 && $i % 13 === 0 ? 5 : 160 + (($i * 13 + $v * 7) % 240));
                $variantName = $variantCount === 1 ? 'Default' : $colors[($i + $v) % count($colors)].' '.$sizes[($i + $v) % count($sizes)];
                $variantId = (int) DB::table('product_variants')->insertGetId([
                    'product_id' => $productId,
                    'store_id' => $storeId,
                    'sku' => sprintf('BUDI-%03d-%02d', $i, $v),
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
                            'value' => $colors[($i + $v) % count($colors)],
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);
                    }
                    if (isset($attributes['ukuran'])) {
                        DB::table('product_variant_values')->insert([
                            'variant_id' => $variantId,
                            'attribute_id' => $attributes['ukuran'],
                            'value' => $sizes[($i + $v) % count($sizes)],
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);
                    }
                }

                $variants[] = ['id' => $variantId, 'sku' => sprintf('BUDI-%03d-%02d', $i, $v), 'price' => $price, 'stock' => $stock];
            }

            if (Schema::hasTable('product_images')) {
                for ($img = 1; $img <= 3; $img++) {
                    DB::table('product_images')->insert([
                        'product_id' => $productId,
                        'url' => sprintf('https://picsum.photos/seed/%s-%02d/900/900', $slug, $img),
                        'alt_text' => sprintf('Produk Budi Testing %03d gambar %d', $i, $img),
                        'is_primary' => $img === 1,
                        'sort_order' => $img - 1,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }

            $products[] = [
                'id' => $productId,
                'name' => sprintf('Produk Budi Testing %03d', $i),
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

    private function seedBanners(string $budiId, int $storeId, Carbon $now): void
    {
        if (! Schema::hasTable('banners')) {
            return;
        }

        for ($i = 1; $i <= self::COUNT; $i++) {
            $createdAt = $now->copy()->subHours(self::COUNT - $i);
            DB::table('banners')->insert([
                'store_id' => $storeId,
                'name' => sprintf('Budi Test Banner %03d', $i),
                'image_url' => sprintf('https://picsum.photos/seed/budi-test-banner-%03d/1600/500', $i),
                'sort_order' => $i,
                'is_active' => $i % 10 !== 0,
                'created_by' => $budiId,
                'updated_by' => $budiId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
        }
    }

    private function seedVouchers(string $budiId, int $storeId, Carbon $now): array
    {
        if (! Schema::hasTable('vouchers')) {
            return [];
        }

        $ids = [];

        for ($i = 1; $i <= self::COUNT; $i++) {
            $percentage = $i % 3 !== 0;
            $createdAt = $now->copy()->subDays(20)->addHours($i);
            $ids[] = (int) DB::table('vouchers')->insertGetId([
                'store_id' => $storeId,
                'voucher_scope' => 'store',
                'code' => sprintf('BUDITEST%03d', $i),
                'name' => sprintf('Voucher Budi Testing %03d', $i),
                'image' => sprintf('https://picsum.photos/seed/budi-voucher-%03d/800/500', $i),
                'discount_target' => $i % 4 === 0 ? 'shipping' : 'product',
                'discount_type' => $percentage ? 'percentage' : 'fixed',
                'discount_value' => $percentage ? 5 + ($i % 26) : 5000 + (($i % 10) * 5000),
                'min_spend' => ($i % 5) * 50000,
                'max_discount' => $percentage ? 25000 + (($i % 4) * 25000) : null,
                'starts_at' => $now->copy()->subDays(7),
                'ends_at' => $now->copy()->addDays(30 + ($i % 60)),
                'usage_limit' => 100 + $i,
                'used_count' => $i % 40,
                'is_active' => $i % 13 !== 0,
                'created_by' => $budiId,
                'updated_by' => $budiId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
        }

        return $ids;
    }

    private function seedPromotions(string $budiId, int $storeId, array $products, array $categories, Carbon $now): void
    {
        if (! Schema::hasTable('promotions')) {
            return;
        }

        for ($i = 1; $i <= self::COUNT; $i++) {
            $createdAt = $now->copy()->subHours(self::COUNT - $i);
            $paymentId = null;

            if (Schema::hasTable('promotion_payments')) {
                $status = $i % 10 === 0 ? 'rejected' : ($i % 4 === 0 ? 'pending' : 'approved');
                $paymentId = (int) DB::table('promotion_payments')->insertGetId([
                    'store_id' => $storeId,
                    'user_id' => $budiId,
                    'payment_number' => sprintf('BUDI-TEST-PROMO-PAY-%04d', $i),
                    'package_name' => $i % 2 === 0 ? 'Paket Promosi 30 Hari' : 'Paket Promosi 7 Hari',
                    'amount' => $i % 2 === 0 ? 450000 : 150000,
                    'payment_method' => $i % 5 === 0 ? 'qris' : 'bank_transfer',
                    'proof_url' => sprintf('/storage/promotion-payments/budi-test-%03d.jpg', $i),
                    'status' => $status,
                    'rejection_reason' => $status === 'rejected' ? 'Bukti pembayaran testing ditolak.' : null,
                    'paid_at' => $createdAt->copy()->addMinutes(2),
                    'reviewed_at' => $status === 'pending' ? null : $createdAt->copy()->addMinutes(20),
                    'reviewed_by' => $status === 'pending' ? null : $budiId,
                    'is_active' => true,
                    'created_by' => $budiId,
                    'updated_by' => $budiId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'deleted_at' => null,
                ]);
            }

            $approval = $i % 10 === 0 ? 'rejected' : ($i % 4 === 0 ? 'pending' : 'approved');
            $clickAction = $i % 2 === 0 ? 'product' : 'category';
            $row = [
                'store_id' => $storeId,
                'name' => sprintf('Budi Test Promotion %03d', $i),
                'image_url' => sprintf('https://picsum.photos/seed/budi-promotion-%03d/1600/500', $i),
                'mobile_image_url' => sprintf('https://picsum.photos/seed/budi-promotion-mobile-%03d/900/900', $i),
                'click_action' => $clickAction,
                'target_id' => $clickAction === 'product' ? $products[($i - 1) % count($products)]['id'] : $categories[($i - 1) % count($categories)],
                'target_url' => null,
                'sort_order' => $i,
                'is_active' => $i % 15 !== 0,
                'approval_status' => $approval,
                'rejection_reason' => $approval === 'rejected' ? 'Promosi testing ditolak untuk simulasi.' : null,
                'submitted_at' => $createdAt,
                'approved_at' => $approval === 'approved' ? $createdAt->copy()->addMinutes(30) : null,
                'approved_by' => $approval === 'approved' ? $budiId : null,
                'created_by' => $budiId,
                'updated_by' => $budiId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ];

            if (Schema::hasColumn('promotions', 'promotion_payment_id')) {
                $row['promotion_payment_id'] = $paymentId;
            }

            DB::table('promotions')->insert($row);
        }
    }

    private function seedOrders(string $budiId, int $storeId, array $buyers, array $products, array $vouchers, Carbon $now): array
    {
        $orders = [];
        $totalOrders = 140;

        for ($i = 1; $i <= $totalOrders; $i++) {
            $buyer = $buyers[($i - 1) % count($buyers)];
            $product = $products[($i - 1) % count($products)];
            $quantity = 1 + ($i % 3);
            $shipping = [10000, 15000, 20000, 25000][($i - 1) % 4];
            $subtotal = $product['price'] * $quantity;
            $isReviewOrder = $i <= 100;
            $status = $isReviewOrder ? 'completed' : ['pending', 'processing', 'shipped', 'cancelled'][($i - 101) % 4];
            $paymentStatus = $isReviewOrder || in_array($status, ['processing', 'shipped'], true) ? 'paid' : 'unpaid';
            $createdAt = $now->copy()->subDays((int) floor(($totalOrders - $i) / 5))->subMinutes(($totalOrders - $i) * 17);
            $discount = $i % 4 === 0 ? min(15000, $subtotal * 0.1) : 0;
            $voucherId = $discount > 0 && $vouchers !== [] ? $vouchers[($i - 1) % count($vouchers)] : null;
            $orderNumber = sprintf('BUDI-TEST-%06d', $i);
            $orderRow = [
                'order_number' => $orderNumber,
                'user_id' => $buyer['id'],
                'voucher_id' => $voucherId,
                'total_amount' => $subtotal + $shipping - $discount,
                'discount_amount' => $discount,
                'shipping_discount_amount' => 0,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_method' => $i % 5 === 0 ? 'qris' : 'bank_transfer',
                'midtrans_snap_token' => null,
                'shipping_address' => json_encode([
                    'recipient' => $buyer['name'],
                    'phone' => '0813'.str_pad((string) $buyer['index'], 8, '0', STR_PAD_LEFT),
                    'address' => sprintf('Jl. Buyer Testing Budi No. %d', $buyer['index']),
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            if (Schema::hasColumn('orders', 'order_type')) {
                $orderRow['order_type'] = $i % 12 === 0 ? 'preorder' : ($i % 17 === 0 ? 'booking' : 'normal');
                $orderRow['preorder_release_at'] = $i % 12 === 0 ? $createdAt->copy()->addDays(3) : null;
                $orderRow['booking_expires_at'] = $i % 17 === 0 ? $createdAt->copy()->addHours(12) : null;
                $orderRow['received_at'] = $status === 'completed' ? $createdAt->copy()->addDays(3) : null;
            }

            $orderId = (int) DB::table('orders')->insertGetId($orderRow);
            $subOrderId = (int) DB::table('sub_orders')->insertGetId([
                'order_id' => $orderId,
                'store_id' => $storeId,
                'sub_order_number' => $orderNumber.'-01',
                'total_items_price' => $subtotal,
                'shipping_cost' => $shipping,
                'courier' => ['jne', 'jnt', 'sicepat', 'anteraja'][($i - 1) % 4],
                'service' => $i % 3 === 0 ? 'express' : 'reg',
                'destination_id' => 'BUDI-DEST-'.$buyer['index'],
                'status' => $status,
                'tracking_number' => in_array($status, ['shipped', 'completed'], true) ? 'BUDIRESI'.str_pad((string) $i, 8, '0', STR_PAD_LEFT) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            $orderItemId = (int) DB::table('order_items')->insertGetId([
                'sub_order_id' => $subOrderId,
                'product_id' => $product['id'],
                'variant_id' => $product['variant_id'],
                'product_name' => $product['name'],
                'sku' => $product['sku'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            if ($paymentStatus === 'paid' && Schema::hasTable('payments')) {
                DB::table('payments')->insert([
                    'order_number' => $orderNumber,
                    'transaction_id' => sprintf('BUDI-TRX-%06d', $i),
                    'payment_method' => $i % 5 === 0 ? 'qris' : 'bank_transfer',
                    'amount' => $subtotal + $shipping - $discount,
                    'status' => 'settlement',
                    'payload' => json_encode(['seeded' => true, 'seller' => 'budi', 'sequence' => $i], JSON_UNESCAPED_UNICODE),
                    'created_at' => $createdAt->copy()->addMinutes(10),
                    'updated_at' => $createdAt->copy()->addMinutes(10),
                ]);
            }

            $orders[] = [
                'id' => $orderId,
                'item_id' => $orderItemId,
                'number' => $orderNumber,
                'buyer_id' => $buyer['id'],
                'product_id' => $product['id'],
                'variant_id' => $product['variant_id'],
                'quantity' => $quantity,
                'status' => $status,
                'created_at' => $createdAt,
                'total' => $subtotal + $shipping - $discount,
            ];
        }

        return $orders;
    }

    private function seedReviews(string $budiId, array $orders, Carbon $now): void
    {
        if (! Schema::hasTable('product_reviews')) {
            return;
        }

        $texts = [
            'Produk sesuai deskripsi, kualitas bagus, dan pengiriman cepat.',
            'Barang diterima dalam kondisi baik dan kemasan rapi.',
            'Harga sesuai kualitas. Seller responsif dan proses pesanan cepat.',
            'Produk berfungsi dengan baik dan tampilannya sesuai foto.',
            'Sangat puas dengan produk dan pelayanan toko ini.',
            'Barang sesuai pesanan dan pengiriman aman.',
            'Kualitas produk bagus, akan membeli lagi.',
            'Produk cukup baik dan komunikasi seller jelas.',
            'Secara keseluruhan memuaskan, kemasan bisa ditingkatkan.',
            'Produk berfungsi baik walaupun pengiriman sedikit lebih lama.',
        ];
        $ratings = [5, 5, 5, 4, 5, 4, 5, 4, 3, 2];

        foreach (array_slice($orders, 0, 100) as $index => $order) {
            $i = $index + 1;
            $createdAt = $now->copy()->subMinutes((self::COUNT - $i) * 37);
            DB::table('product_reviews')->insert([
                'product_id' => $order['product_id'],
                'order_id' => $order['id'],
                'order_item_id' => $order['item_id'],
                'user_id' => $order['buyer_id'],
                'rating' => $ratings[$index % count($ratings)],
                'review' => $texts[$index % count($texts)],
                'media' => $i % 8 === 0 ? json_encode([sprintf('https://picsum.photos/seed/budi-review-%03d/900/900', $i)], JSON_UNESCAPED_UNICODE) : json_encode([], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_by' => $order['buyer_id'],
                'updated_by' => $budiId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
        }
    }

    private function seedInventory(string $budiId, int $storeId, array $products, array $orders, Carbon $now): void
    {
        if (! Schema::hasTable('stock_movements')) {
            return;
        }

        foreach ($products as $index => $product) {
            $i = $index + 1;
            $createdAt = $now->copy()->subDays(45)->addHours($i);
            DB::table('stock_movements')->insert([
                'store_id' => $storeId,
                'product_id' => $product['id'],
                'variant_id' => $product['variant_id'],
                'order_id' => null,
                'order_item_id' => null,
                'movement_key' => sprintf('budi-test-stock-in-%03d', $i),
                'type' => 'restock',
                'quantity_delta' => $product['stock'],
                'balance_after' => $product['stock'],
                'reference_type' => 'budi_test_seed',
                'reference_id' => sprintf('BUDI-RESTOCK-%03d', $i),
                'notes' => 'Restock awal produk Budi untuk testing.',
                'occurred_at' => $createdAt,
                'created_by' => $budiId,
                'updated_by' => $budiId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
        }

        $balances = [];

        foreach ($products as $product) {
            $balances[$product['variant_id']] = $product['stock'];
        }

        foreach ($orders as $index => $order) {
            if ($order['status'] === 'cancelled') {
                continue;
            }

            $variantId = $order['variant_id'];
            $balances[$variantId] = max(0, ($balances[$variantId] ?? 0) - $order['quantity']);
            DB::table('product_variants')->where('id', $variantId)->update(['stock' => $balances[$variantId], 'updated_at' => $now]);
            DB::table('stock_movements')->insert([
                'store_id' => $storeId,
                'product_id' => $order['product_id'],
                'variant_id' => $variantId,
                'order_id' => $order['id'],
                'order_item_id' => $order['item_id'],
                'movement_key' => sprintf('budi-test-stock-sale-%03d', $index + 1),
                'type' => 'sale',
                'quantity_delta' => -$order['quantity'],
                'balance_after' => $balances[$variantId],
                'reference_type' => 'order',
                'reference_id' => $order['number'],
                'notes' => 'Pengurangan stok dari order testing Budi.',
                'occurred_at' => $order['created_at']->copy()->addMinutes(15),
                'created_by' => $budiId,
                'updated_by' => $budiId,
                'created_at' => $order['created_at']->copy()->addMinutes(15),
                'updated_at' => $order['created_at']->copy()->addMinutes(15),
                'deleted_at' => null,
            ]);
        }
    }

    private function seedRawMaterialsAndCosting(int $storeId, array $products, Carbon $now): void
    {
        if (! Schema::hasTable('raw_materials')) {
            return;
        }

        $materials = [];

        for ($i = 1; $i <= self::COUNT; $i++) {
            $stock = $i % 20 === 0 ? 0 : ($i % 13 === 0 ? 8 : 100 + (($i * 19) % 900));
            $cost = 500 + (($i * 775) % 30000);
            $createdAt = $now->copy()->subDays(40)->addHours($i);
            $materialId = (int) DB::table('raw_materials')->insertGetId([
                'store_id' => $storeId,
                'code' => sprintf('BUDI-RM-%03d', $i),
                'name' => sprintf('Bahan Baku Budi %03d', $i),
                'unit' => ['pcs', 'kg', 'meter', 'liter'][($i - 1) % 4],
                'stock' => $stock,
                'minimum_stock' => 20 + ($i % 30),
                'average_cost' => $cost,
                'is_active' => true,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
            $materials[] = ['id' => $materialId, 'cost' => $cost];

            if (Schema::hasTable('raw_material_stock_movements')) {
                DB::table('raw_material_stock_movements')->insert([
                    'store_id' => $storeId,
                    'raw_material_id' => $materialId,
                    'type' => 'restock',
                    'quantity_delta' => $stock,
                    'balance_after' => $stock,
                    'unit_cost' => $cost,
                    'total_cost' => $stock * $cost,
                    'reference_type' => 'budi_test_seed',
                    'reference_number' => sprintf('BUDI-RM-MOVE-%03d', $i),
                    'notes' => 'Restock bahan baku Budi untuk testing.',
                    'occurred_at' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }

        foreach ($products as $index => $product) {
            $i = $index + 1;
            $materialA = $materials[$index % count($materials)];
            $materialB = $materials[($index + 17) % count($materials)];
            $materialCost = 0.0;

            if (Schema::hasTable('product_materials')) {
                foreach ([[$materialA, 1.0], [$materialB, 0.5]] as [$material, $quantity]) {
                    $total = $material['cost'] * $quantity;
                    $materialCost += $total;
                    DB::table('product_materials')->insert([
                        'product_id' => $product['id'],
                        'raw_material_id' => $material['id'],
                        'quantity' => $quantity,
                        'unit_cost' => $material['cost'],
                        'total_cost' => $total,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            if (Schema::hasTable('product_costings')) {
                $labor = 5000 + (($i % 10) * 1000);
                $overhead = 2500 + (($i % 8) * 750);
                $other = ($i % 5) * 500;
                $hpp = $materialCost + $labor + $overhead + $other;
                $margin = 20 + ($i % 31);
                $suggested = round($hpp * (1 + ($margin / 100)), 2);
                DB::table('product_costings')->insert([
                    'product_id' => $product['id'],
                    'store_id' => $storeId,
                    'material_cost' => $materialCost,
                    'labor_cost' => $labor,
                    'overhead_cost' => $overhead,
                    'other_cost' => $other,
                    'hpp' => $hpp,
                    'margin_percent' => $margin,
                    'suggested_price' => $suggested,
                    'selling_price' => $product['price'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if (Schema::hasTable('raw_material_cost_histories') && Schema::hasTable('product_costing_impacts')) {
                    $oldUnitCost = round($materialA['cost'] * (0.78 + (($i % 5) * 0.03)), 4);
                    $newUnitCost = (float) $materialA['cost'];
                    $changeAmount = $newUnitCost - $oldUnitCost;
                    $changePercent = $oldUnitCost > 0 ? ($changeAmount / $oldUnitCost) * 100 : 100;
                    $occurredAt = $now->copy()->subDays((self::COUNT - $i) % 30)->subMinutes((self::COUNT - $i) * 9);
                    $historyId = (int) DB::table('raw_material_cost_histories')->insertGetId([
                        'store_id' => $storeId,
                        'raw_material_id' => $materialA['id'],
                        'raw_material_stock_movement_id' => null,
                        'old_average_cost' => $oldUnitCost,
                        'new_average_cost' => $newUnitCost,
                        'change_amount' => $changeAmount,
                        'change_percent' => $changePercent,
                        'direction' => 'increase',
                        'reference_type' => 'budi_test_purchase',
                        'reference_number' => sprintf('BUDI-COST-%03d', $i),
                        'occurred_at' => $occurredAt,
                        'created_at' => $occurredAt,
                        'updated_at' => $occurredAt,
                    ]);
                    $oldMaterialCost = $materialCost - $changeAmount;
                    $oldHpp = $oldMaterialCost + $labor + $overhead + $other;
                    $oldSuggested = round($oldHpp * (1 + ($margin / 100)), 2);
                    DB::table('product_costing_impacts')->insert([
                        'store_id' => $storeId,
                        'product_id' => $product['id'],
                        'raw_material_id' => $materialA['id'],
                        'raw_material_cost_history_id' => $historyId,
                        'old_material_cost' => $oldMaterialCost,
                        'new_material_cost' => $materialCost,
                        'old_hpp' => $oldHpp,
                        'new_hpp' => $hpp,
                        'hpp_change_amount' => $hpp - $oldHpp,
                        'hpp_change_percent' => $oldHpp > 0 ? (($hpp - $oldHpp) / $oldHpp) * 100 : 100,
                        'old_suggested_price' => $oldSuggested,
                        'new_suggested_price' => $suggested,
                        'trigger_type' => 'raw_material_cost_change',
                        'occurred_at' => $occurredAt,
                        'created_at' => $occurredAt,
                        'updated_at' => $occurredAt,
                    ]);
                }
            }
        }
    }

    private function seedFinance(string $budiId, int $storeId, array $buyers, array $orders, Carbon $now): void
    {
        if (! Schema::hasTable('financial_transactions')) {
            return;
        }

        $types = ['income', 'expense', 'receivable', 'payable'];

        for ($i = 1; $i <= self::COUNT; $i++) {
            $type = $types[($i - 1) % count($types)];
            $amount = 100000 + (($i * 173000) % 4900000);
            $isDebt = in_array($type, ['receivable', 'payable'], true);
            $paidAmount = $isDebt ? round($amount * (($i % 3) + 1) / 4, 2) : $amount;
            $status = $isDebt ? ($paidAmount >= $amount ? 'paid' : 'partial') : 'paid';
            $occurredAt = $now->copy()->subDays((int) floor((self::COUNT - $i) / 4))->subMinutes((self::COUNT - $i) * 11);
            $buyer = $buyers[($i - 1) % count($buyers)];
            $order = $orders[($i - 1) % count($orders)];
            $transactionId = (int) DB::table('financial_transactions')->insertGetId([
                'store_id' => $storeId,
                'order_id' => $type === 'income' ? $order['id'] : null,
                'user_id' => in_array($type, ['income', 'receivable'], true) ? $buyer['id'] : null,
                'reference_number' => sprintf('BUDI-TEST-FIN-%04d', $i),
                'type' => $type,
                'title' => sprintf('%s Budi Testing %03d', ucfirst($type), $i),
                'description' => 'Transaksi finance khusus pengujian Seller Panel Budi.',
                'amount' => $amount,
                'paid_amount' => $paidAmount,
                'status' => $status,
                'due_date' => $isDebt ? $now->copy()->addDays(7 + ($i % 30))->toDateString() : null,
                'occurred_at' => $occurredAt,
                'settled_at' => $status === 'paid' ? $occurredAt->copy()->addHours(2) : null,
                'is_active' => true,
                'metadata' => json_encode(['seeded' => true, 'seller' => 'budi', 'sequence' => $i], JSON_UNESCAPED_UNICODE),
                'created_by' => $budiId,
                'updated_by' => $budiId,
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
                'deleted_at' => null,
            ]);

            if ($isDebt && Schema::hasTable('financial_payment_histories') && $paidAmount > 0) {
                $first = round($paidAmount / 2, 2);
                $second = $paidAmount - $first;
                $balanceAfterFirst = $amount - $first;
                DB::table('financial_payment_histories')->insert([
                    [
                        'financial_transaction_id' => $transactionId,
                        'store_id' => $storeId,
                        'recorded_by' => $budiId,
                        'amount' => $first,
                        'balance_before' => $amount,
                        'balance_after' => $balanceAfterFirst,
                        'payment_method' => 'bank_transfer',
                        'reference_number' => sprintf('BUDI-TEST-INSTALL-%04d-A', $i),
                        'notes' => 'Cicilan pertama testing Budi.',
                        'paid_at' => $occurredAt->copy()->addHours(4),
                        'created_at' => $occurredAt->copy()->addHours(4),
                        'updated_at' => $occurredAt->copy()->addHours(4),
                    ],
                    [
                        'financial_transaction_id' => $transactionId,
                        'store_id' => $storeId,
                        'recorded_by' => $budiId,
                        'amount' => $second,
                        'balance_before' => $balanceAfterFirst,
                        'balance_after' => $amount - $paidAmount,
                        'payment_method' => $i % 3 === 0 ? 'cash' : 'bank_transfer',
                        'reference_number' => sprintf('BUDI-TEST-INSTALL-%04d-B', $i),
                        'notes' => 'Cicilan kedua testing Budi.',
                        'paid_at' => $occurredAt->copy()->addHours(8),
                        'created_at' => $occurredAt->copy()->addHours(8),
                        'updated_at' => $occurredAt->copy()->addHours(8),
                    ],
                ]);
            }
        }
    }

    private function seedShowcases(string $budiId, int $storeId, array $products, Carbon $now): void
    {
        if (! Schema::hasTable('showcases')) {
            return;
        }

        for ($i = 1; $i <= self::COUNT; $i++) {
            $createdAt = $now->copy()->subHours(self::COUNT - $i);
            $showcaseId = (int) DB::table('showcases')->insertGetId([
                'store_id' => $storeId,
                'name' => sprintf('Etalase Budi Testing %03d', $i),
                'slug' => sprintf('budi-test-showcase-%03d', $i),
                'description' => sprintf('Pengelompokan produk testing Budi nomor %03d.', $i),
                'sort_order' => $i,
                'is_active' => $i % 12 !== 0,
                'created_by' => $budiId,
                'updated_by' => $budiId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);

            if (Schema::hasTable('showcase_products')) {
                for ($j = 0; $j < 5; $j++) {
                    $product = $products[($i - 1 + $j) % count($products)];
                    DB::table('showcase_products')->insert([
                        'showcase_id' => $showcaseId,
                        'product_id' => $product['id'],
                        'sort_order' => $j + 1,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
        }
    }

    private function seedSupport(string $budiId, int $storeId, array $buyers, array $orders, Carbon $now): void
    {
        if (! Schema::hasTable('support_tickets')) {
            return;
        }

        for ($i = 1; $i <= self::COUNT; $i++) {
            $buyer = $buyers[($i - 1) % count($buyers)];
            $order = $orders[($i - 1) % count($orders)];
            $createdAt = $now->copy()->subMinutes((self::COUNT - $i) * 29);
            $status = $i % 8 === 0 ? 'resolved' : ($i % 3 === 0 ? 'pending' : 'open');
            $ticketId = (int) DB::table('support_tickets')->insertGetId([
                'ticket_number' => sprintf('BUDI-TEST-TICKET-%05d', $i),
                'user_id' => $buyer['id'],
                'store_id' => $storeId,
                'order_id' => $order['id'],
                'category' => ['order', 'payment', 'shipping', 'product'][($i - 1) % 4],
                'subject' => sprintf('Bantuan Seller Budi Testing %03d', $i),
                'description' => 'Tiket testing yang terkait langsung dengan toko dan order Budi.',
                'priority' => $i % 11 === 0 ? 'high' : 'normal',
                'status' => $status,
                'assigned_to' => $budiId,
                'last_replied_at' => $createdAt->copy()->addMinutes(20),
                'resolved_at' => $status === 'resolved' ? $createdAt->copy()->addHours(2) : null,
                'is_active' => true,
                'created_by' => $buyer['id'],
                'updated_by' => $budiId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);

            if (Schema::hasTable('support_ticket_messages')) {
                DB::table('support_ticket_messages')->insert([
                    [
                        'ticket_id' => $ticketId,
                        'user_id' => $buyer['id'],
                        'message' => sprintf('Halo seller Budi, saya membutuhkan bantuan untuk order %s.', $order['number']),
                        'attachments' => null,
                        'is_internal' => false,
                        'read_at' => $createdAt->copy()->addMinutes(5),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                        'deleted_at' => null,
                    ],
                    [
                        'ticket_id' => $ticketId,
                        'user_id' => $budiId,
                        'message' => 'Permintaan sudah kami cek dan akan ditindaklanjuti melalui status pesanan.',
                        'attachments' => null,
                        'is_internal' => false,
                        'read_at' => $i % 5 === 0 ? null : $createdAt->copy()->addMinutes(25),
                        'created_at' => $createdAt->copy()->addMinutes(20),
                        'updated_at' => $createdAt->copy()->addMinutes(20),
                        'deleted_at' => null,
                    ],
                ]);
            }
        }
    }

    private function seedChat(string $budiId, int $storeId, array $buyers, array $orders, Carbon $now): void
    {
        if (! Schema::hasTable('conversations')) {
            return;
        }

        for ($i = 1; $i <= self::COUNT; $i++) {
            $buyer = $buyers[($i - 1) % count($buyers)];
            $order = $orders[($i - 1) % count($orders)];
            $createdAt = $now->copy()->subMinutes((self::COUNT - $i) * 19);
            $conversationId = (int) DB::table('conversations')->insertGetId([
                'type' => 'order',
                'store_id' => $storeId,
                'order_id' => $order['id'],
                'subject' => sprintf('BUDI-TEST-CHAT-%03d', $i),
                'target_role' => null,
                'is_active' => true,
                'created_by' => $buyer['id'],
                'updated_by' => $budiId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addMinutes(7),
                'deleted_at' => null,
            ]);

            if (Schema::hasTable('conversation_participants')) {
                DB::table('conversation_participants')->insert([
                    [
                        'conversation_id' => $conversationId,
                        'user_id' => $buyer['id'],
                        'last_read_at' => $createdAt->copy()->addMinutes(6),
                        'joined_at' => $createdAt,
                        'left_at' => null,
                        'is_muted' => false,
                    ],
                    [
                        'conversation_id' => $conversationId,
                        'user_id' => $budiId,
                        'last_read_at' => $i % 6 === 0 ? null : $createdAt->copy()->addMinutes(7),
                        'joined_at' => $createdAt,
                        'left_at' => null,
                        'is_muted' => false,
                    ],
                ]);
            }

            if (Schema::hasTable('chat_messages')) {
                $buyerMessageId = (int) DB::table('chat_messages')->insertGetId([
                    'conversation_id' => $conversationId,
                    'sender_id' => $buyer['id'],
                    'message_type' => 'text',
                    'message' => sprintf('Halo, saya ingin menanyakan order %s.', $order['number']),
                    'attachments' => null,
                    'edited_at' => null,
                    'created_at' => $createdAt->copy()->addMinute(),
                    'updated_at' => $createdAt->copy()->addMinute(),
                    'deleted_at' => null,
                ]);
                DB::table('chat_messages')->insert([
                    'conversation_id' => $conversationId,
                    'sender_id' => $budiId,
                    'message_type' => 'text',
                    'message' => 'Halo, pesanan sudah kami cek. Detail terbaru tersedia pada halaman pesanan.',
                    'attachments' => null,
                    'edited_at' => null,
                    'created_at' => $createdAt->copy()->addMinutes(4),
                    'updated_at' => $createdAt->copy()->addMinutes(4),
                    'deleted_at' => null,
                ]);
                DB::table('chat_messages')->insert([
                    'conversation_id' => $conversationId,
                    'sender_id' => $buyer['id'],
                    'message_type' => 'text',
                    'message' => 'Baik, terima kasih. Saya akan memantau statusnya.',
                    'attachments' => null,
                    'edited_at' => null,
                    'created_at' => $createdAt->copy()->addMinutes(7),
                    'updated_at' => $createdAt->copy()->addMinutes(7),
                    'deleted_at' => null,
                ]);

                if (Schema::hasTable('chat_message_reads') && $i % 6 !== 0) {
                    DB::table('chat_message_reads')->insert([
                        'message_id' => $buyerMessageId,
                        'user_id' => $budiId,
                        'read_at' => $createdAt->copy()->addMinutes(3),
                    ]);
                }
            }
        }
    }

    private function seedNotifications(string $budiId, int $storeId, array $orders, Carbon $now): void
    {
        if (! Schema::hasTable('admin_notifications')) {
            return;
        }

        for ($i = 1; $i <= self::COUNT; $i++) {
            $order = $orders[($i - 1) % count($orders)];
            $createdAt = $now->copy()->subMinutes((self::COUNT - $i) * 13);
            DB::table('admin_notifications')->insert([
                'user_id' => $budiId,
                'actor_id' => $order['buyer_id'],
                'store_id' => $storeId,
                'module' => ['orders', 'reviews', 'finance', 'chat'][($i - 1) % 4],
                'type' => sprintf('budi_test_%03d', $i),
                'title' => sprintf('Aktivitas Seller Budi %03d', $i),
                'message' => sprintf('Aktivitas testing terbaru terkait order %s.', $order['number']),
                'reference_type' => 'order',
                'reference_id' => (string) $order['id'],
                'url' => '/seller/orders',
                'meta' => json_encode(['seeded' => true, 'seller' => 'budi', 'sequence' => $i], JSON_UNESCAPED_UNICODE),
                'read_at' => $i % 4 === 0 ? null : $createdAt->copy()->addMinutes(5),
                'is_active' => true,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
        }
    }
}
