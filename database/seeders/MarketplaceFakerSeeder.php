<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class MarketplaceFakerSeeder extends Seeder
{
    private const PASSWORD = '12345678';

    public function run(): void
    {
        $count = max(1, (int) env('MARKETPLACE_FAKER_COUNT', 100));
        $faker = Factory::create('id_ID');
        $faker->seed(20260727);
        $now = now();
        $password = Hash::make(self::PASSWORD);

        DB::transaction(function () use ($count, $faker, $now, $password): void {
            $this->cleanPreviousData();

            $roleIds = DB::table('roles')->pluck('id', 'name');
            $adminId = SeederIds::SUPER_ADMIN;
            $sellers = $this->createUsers($count, 'seller', $faker, $password, $adminId, $roleIds, $now);
            $buyers = $this->createUsers($count, 'buyer', $faker, $password, $adminId, $roleIds, $now);
            $stores = $this->createStores($sellers, $faker, $adminId, $now);
            $categories = $this->createCatalog($count, $faker, $adminId, $now);
            $attributeIds = $this->createAttributes($now);
            $products = $this->createProducts($count, $stores, $categories, $attributeIds, $faker, $now);
            $vouchers = $this->createVouchers($count, $stores, $adminId, $faker, $now);
            $this->createBanners($count, $stores, $faker, $now);
            $this->createPromotions($count, $stores, $products, $categories, $adminId, $faker, $now);
            $this->createBuyerData($count, $buyers, $products, $vouchers, $faker, $now);
        });
    }

    private function cleanPreviousData(): void
    {
        DB::table('payments')->where('order_number', 'like', 'FKR-%')->delete();
        DB::table('orders')->where('order_number', 'like', 'FKR-%')->delete();
        DB::table('vouchers')->where('code', 'like', 'fkr%')->delete();
        DB::table('promotions')->where('name', 'like', 'promotion faker %')->delete();
        DB::table('products')->where('slug', 'like', 'produk-faker-%')->delete();
        DB::table('categories')->where('full_slug', 'like', '%kategori-faker-%')->delete();
        DB::table('catalog_groups')->where('slug', 'like', 'faker-group-%')->delete();
        DB::table('stores')->where('slug', 'like', 'toko-faker-%')->delete();
        DB::table('users')->where('email', 'like', 'faker.%@marketku.test')->delete();
    }

    private function createUsers(
        int $count,
        string $type,
        Generator $faker,
        string $password,
        string $adminId,
        $roleIds,
        Carbon $now
    ): array {
        $users = [];

        for ($index = 1; $index <= $count; $index++) {
            $id = (string) Str::uuid();
            $email = sprintf('faker.%s.%03d@marketku.test', $type, $index);
            $name = sprintf('%s Faker %03d %s', ucfirst($type), $index, $faker->firstName());

            DB::table('users')->insert([
                'id' => $id,
                'firebase_uid' => null,
                'email' => $email,
                'password' => $password,
                'name' => $name,
                'avatar' => 'https://i.pravatar.cc/300?u='.urlencode($email),
                'is_email_verified' => true,
                'is_active' => $index % 17 !== 0,
                'banned_at' => null,
                'remember_token' => null,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);

            $assignedRoles = $type === 'seller' ? ['seller', 'buyer'] : ['buyer'];

            foreach ($assignedRoles as $roleName) {
                if (! isset($roleIds[$roleName])) {
                    continue;
                }

                DB::table('user_roles')->insert([
                    'user_id' => $id,
                    'role_id' => $roleIds[$roleName],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $users[] = ['id' => $id, 'email' => $email, 'name' => $name, 'index' => $index];
        }

        return $users;
    }

    private function createStores(array $sellers, Generator $faker, string $adminId, Carbon $now): array
    {
        $stores = [];

        foreach ($sellers as $seller) {
            $index = $seller['index'];
            $slug = sprintf('toko-faker-%03d', $index);
            $status = $index % 19 === 0 ? 'suspended' : ($index % 11 === 0 ? 'pending' : 'approved');
            $isActive = $status !== 'suspended' && $index % 13 !== 0;
            $latitude = $faker->randomFloat(8, -7.5, -5.5);
            $longitude = $faker->randomFloat(8, 106, 112);

            $storeId = (int) DB::table('stores')->insertGetId([
                'user_id' => $seller['id'],
                'name' => sprintf('toko faker %03d %s', $index, Str::lower($faker->company())),
                'slug' => $slug,
                'description' => Str::lower($faker->paragraph(3)),
                'short_description' => Str::lower($faker->sentence(8)),
                'phone' => '08'.str_pad((string) $index, 10, '0', STR_PAD_LEFT),
                'email' => sprintf('store.%03d@marketku.test', $index),
                'city' => $faker->city(),
                'province' => $faker->randomElement(['DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'Jawa Timur', 'Banten', 'DI Yogyakarta']),
                'address' => $faker->address(),
                'status' => $status,
                'is_active' => $isActive,
                'logo' => sprintf('https://picsum.photos/seed/%s-logo/500/500', $slug),
                'banner_url' => sprintf('https://picsum.photos/seed/%s-banner/1600/500', $slug),
                'created_by' => $seller['id'],
                'updated_by' => $seller['id'],
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);

            DB::table('store_details')->insert([
                'store_id' => $storeId,
                'owner_name' => $seller['name'],
                'owner_phone' => '08'.str_pad((string) $index, 10, '0', STR_PAD_LEFT),
                'description' => 'Detail operasional toko faker untuk pengujian.',
                'shipping_policy' => 'Pesanan diproses maksimal dua hari kerja setelah pembayaran diterima.',
                'return_policy' => 'Pengembalian dapat diajukan maksimal tujuh hari setelah barang diterima.',
                'open_days' => 'senin-sabtu',
                'open_time' => '09:00:00',
                'close_time' => '18:00:00',
                'whatsapp_url' => null,
                'instagram_url' => null,
                'tiktok_url' => null,
                'website_url' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('addresses')->insert([
                'user_id' => $seller['id'],
                'store_id' => $storeId,
                'country' => 'Indonesia',
                'province' => $faker->randomElement(['DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'Jawa Timur', 'Banten', 'DI Yogyakarta']),
                'city_or_regency' => $faker->city(),
                'district' => 'Kecamatan Faker',
                'subdistrict' => 'Kelurahan Faker',
                'postal_code' => (string) $faker->numberBetween(10000, 99999),
                'full_address' => $faker->address(),
                'notes' => 'Alamat toko faker',
                'label' => 'Toko',
                'recipient_name' => $seller['name'],
                'phone_number' => '08'.str_pad((string) $index, 10, '0', STR_PAD_LEFT),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'komerce_destination_id' => null,
                'is_primary' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('shipping_settings')->insert([
                'store_id' => $storeId,
                'store_latitude' => $latitude,
                'store_longitude' => $longitude,
                'free_shipping_max_distance' => $faker->randomFloat(2, 0, 20),
                'default_flat_rate' => $faker->randomElement([10000, 15000, 20000, 25000]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $stores[] = [
                'id' => $storeId,
                'user_id' => $seller['id'],
                'slug' => $slug,
                'status' => $status,
                'is_active' => $isActive,
                'index' => $index,
            ];
        }

        return $stores;
    }

    private function createCatalog(int $count, Generator $faker, string $adminId, Carbon $now): array
    {
        $groupCount = 5;
        $groups = [];

        for ($index = 1; $index <= $groupCount; $index++) {
            $slug = sprintf('faker-group-%02d', $index);
            $groups[] = [
                'id' => (int) DB::table('catalog_groups')->insertGetId([
                    'name' => sprintf('grup katalog faker %02d', $index),
                    'slug' => $slug,
                    'is_active' => true,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]),
                'slug' => $slug,
            ];
        }

        $rootCount = max($groupCount, (int) round($count * 0.1));
        $levelTwoCount = max($groupCount, (int) round($count * 0.3));
        $levelThreeCount = max(0, $count - $rootCount - $levelTwoCount);
        $roots = [];
        $levelTwos = [];
        $levelThrees = [];
        $serial = 1;

        for ($index = 0; $index < $rootCount; $index++, $serial++) {
            $group = $groups[$index % count($groups)];
            $slug = sprintf('kategori-faker-%03d', $serial);
            $id = $this->insertCategory($group['id'], null, 1, $slug, $slug, null, $serial, $adminId, $now);
            $roots[] = ['id' => $id, 'group_id' => $group['id'], 'full_slug' => $slug, 'level' => 1];
        }

        for ($index = 0; $index < $levelTwoCount; $index++, $serial++) {
            $parent = $roots[$index % count($roots)];
            $slug = sprintf('kategori-faker-%03d', $serial);
            $fullSlug = $parent['full_slug'].'/'.$slug;
            $id = $this->insertCategory($parent['group_id'], $parent['id'], 2, $slug, $fullSlug, null, $serial, $adminId, $now);
            $levelTwos[] = ['id' => $id, 'group_id' => $parent['group_id'], 'full_slug' => $fullSlug, 'level' => 2];
        }

        for ($index = 0; $index < $levelThreeCount; $index++, $serial++) {
            $parent = $levelTwos[$index % count($levelTwos)];
            $slug = sprintf('kategori-faker-%03d', $serial);
            $fullSlug = $parent['full_slug'].'/'.$slug;
            $image = sprintf('https://picsum.photos/seed/%s/800/800', $slug);
            $id = $this->insertCategory($parent['group_id'], $parent['id'], 3, $slug, $fullSlug, $image, $serial, $adminId, $now);
            $levelThrees[] = ['id' => $id, 'group_id' => $parent['group_id'], 'full_slug' => $fullSlug, 'level' => 3];
        }

        return $levelThrees !== [] ? $levelThrees : $levelTwos;
    }

    private function insertCategory(
        int $groupId,
        ?int $parentId,
        int $level,
        string $slug,
        string $fullSlug,
        ?string $image,
        int $serial,
        string $adminId,
        Carbon $now
    ): int {
        return (int) DB::table('categories')->insertGetId([
            'catalog_group_id' => $groupId,
            'parent_id' => $parentId,
            'parent_scope_id' => $parentId ?? 0,
            'level' => $level,
            'sort_order' => $serial * 10,
            'is_active' => $serial % 17 !== 0,
            'is_visible_in_menu' => $serial % 13 !== 0,
            'name' => sprintf('kategori faker %03d', $serial),
            'slug' => $slug,
            'full_slug' => $fullSlug,
            'image_url' => $level === 3 ? $image : null,
            'icon_url' => null,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);
    }

    private function createAttributes(Carbon $now): array
    {
        $attributes = [
            ['name' => 'warna', 'slug' => 'warna', 'type' => 'select'],
            ['name' => 'ukuran', 'slug' => 'ukuran', 'type' => 'select'],
            ['name' => 'bahan', 'slug' => 'bahan', 'type' => 'select'],
        ];

        DB::table('product_attributes')->upsert(
            array_map(fn (array $attribute): array => [...$attribute, 'created_at' => $now, 'updated_at' => $now], $attributes),
            ['name'],
            ['slug', 'type', 'updated_at']
        );

        return DB::table('product_attributes')->whereIn('slug', array_column($attributes, 'slug'))->pluck('id', 'slug')->all();
    }

    private function createProducts(
        int $count,
        array $stores,
        array $categories,
        array $attributeIds,
        Generator $faker,
        Carbon $now
    ): array {
        $products = [];
        $colors = ['merah', 'biru', 'hitam', 'putih', 'hijau', 'cokelat'];
        $sizes = ['s', 'm', 'l', 'xl'];

        for ($index = 1; $index <= $count; $index++) {
            $store = $stores[($index - 1) % count($stores)];
            $category = $categories[($index - 1) % count($categories)];
            $slug = sprintf('produk-faker-%03d', $index);
            $status = $index % 14 === 0 ? 'archived' : ($index % 9 === 0 ? 'draft' : 'published');
            $isActive = $store['status'] !== 'suspended' && $index % 12 !== 0;

            $productId = (int) DB::table('products')->insertGetId([
                'store_id' => $store['id'],
                'primary_category_id' => $category['id'],
                'name' => sprintf('produk faker %03d %s', $index, Str::lower($faker->words(2, true))),
                'slug' => $slug,
                'description' => Str::lower($faker->paragraph(4)),
                'brand' => sprintf('merek faker %02d', (($index - 1) % 10) + 1),
                'thumbnail' => sprintf('https://picsum.photos/seed/%s/900/900', $slug),
                'status' => $status,
                'is_active' => $isActive,
                'created_by' => $store['user_id'],
                'updated_by' => $store['user_id'],
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);

            DB::table('product_categories')->insert([
                'product_id' => $productId,
                'category_id' => $category['id'],
                'is_primary' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (isset($attributeIds['bahan'])) {
                DB::table('product_attribute_values')->insert([
                    'product_id' => $productId,
                    'attribute_id' => $attributeIds['bahan'],
                    'value' => $faker->randomElement(['katun', 'kayu', 'plastik', 'logam']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $variantCount = $index % 4 === 0 ? 3 : ($index % 3 === 0 ? 2 : 1);
            $variants = [];

            for ($variantIndex = 1; $variantIndex <= $variantCount; $variantIndex++) {
                $color = $colors[($index + $variantIndex) % count($colors)];
                $size = $sizes[($index + $variantIndex) % count($sizes)];
                $variantId = (int) DB::table('product_variants')->insertGetId([
                    'product_id' => $productId,
                    'store_id' => $store['id'],
                    'sku' => sprintf('FKR-%03d-%02d', $index, $variantIndex),
                    'name' => $variantCount === 1 ? 'default' : $color.' '.$size,
                    'price' => $faker->numberBetween(25000, 2500000),
                    'stock' => $index % 15 === 0 ? 0 : $faker->numberBetween(1, 250),
                    'is_default' => $variantIndex === 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($variantCount > 1) {
                    DB::table('product_variant_values')->insert([
                        ['variant_id' => $variantId, 'attribute_id' => $attributeIds['warna'], 'value' => $color, 'created_at' => $now, 'updated_at' => $now],
                        ['variant_id' => $variantId, 'attribute_id' => $attributeIds['ukuran'], 'value' => $size, 'created_at' => $now, 'updated_at' => $now],
                    ]);
                }

                $variants[] = ['id' => $variantId, 'price' => (float) DB::table('product_variants')->where('id', $variantId)->value('price')];
            }

            for ($imageIndex = 1; $imageIndex <= 3; $imageIndex++) {
                DB::table('product_images')->insert([
                    'product_id' => $productId,
                    'url' => sprintf('https://picsum.photos/seed/%s-%02d/900/900', $slug, $imageIndex),
                    'alt_text' => sprintf('gambar produk faker %03d', $index),
                    'is_primary' => $imageIndex === 1,
                    'sort_order' => $imageIndex - 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $products[] = [
                'id' => $productId,
                'store_id' => $store['id'],
                'store_user_id' => $store['user_id'],
                'variant_id' => $variants[0]['id'],
                'price' => $variants[0]['price'],
                'slug' => $slug,
                'index' => $index,
            ];
        }

        return $products;
    }

    private function createBanners(int $count, array $stores, Generator $faker, Carbon $now): void
    {
        for ($index = 1; $index <= $count; $index++) {
            $store = $stores[($index - 1) % count($stores)];
            DB::table('banners')->insert([
                'store_id' => $store['id'],
                'name' => sprintf('banner faker %03d', $index),
                'image_url' => sprintf('https://picsum.photos/seed/banner-faker-%03d/1600/500', $index),
                'sort_order' => $index,
                'is_active' => $index % 10 !== 0,
                'created_by' => $store['user_id'],
                'updated_by' => $store['user_id'],
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }
    }

    private function createVouchers(int $count, array $stores, string $adminId, Generator $faker, Carbon $now): array
    {
        $vouchers = [];

        for ($index = 1; $index <= $count; $index++) {
            $platform = $index % 5 === 0;
            $store = $stores[($index - 1) % count($stores)];
            $shipping = $index % 4 === 0;
            $percentage = $index % 3 !== 0;
            $code = sprintf('fkr%03d', $index);
            $storeId = $platform ? null : $store['id'];
            $creator = $platform ? $adminId : $store['user_id'];

            $voucherId = (int) DB::table('vouchers')->insertGetId([
                'store_id' => $storeId,
                'voucher_scope' => $platform ? 'platform' : 'store',
                'code' => $code,
                'name' => sprintf('voucher faker %03d', $index),
                'image' => sprintf('https://picsum.photos/seed/voucher-faker-%03d/800/500', $index),
                'discount_target' => $shipping ? 'shipping' : 'product',
                'discount_type' => $percentage ? 'percentage' : 'fixed',
                'discount_value' => $percentage ? $faker->numberBetween(5, 100) : $faker->numberBetween(5000, 50000),
                'min_spend' => $faker->randomElement([0, 50000, 100000, 200000]),
                'max_discount' => $percentage ? $faker->randomElement([25000, 50000, 100000]) : null,
                'starts_at' => $now->copy()->subDays($faker->numberBetween(1, 10)),
                'ends_at' => $now->copy()->addDays($faker->numberBetween(10, 180)),
                'usage_limit' => $faker->numberBetween(50, 1000),
                'used_count' => $faker->numberBetween(0, 40),
                'is_active' => $index % 13 !== 0,
                'created_by' => $creator,
                'updated_by' => $creator,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);

            $vouchers[] = ['id' => $voucherId, 'store_id' => $storeId, 'scope' => $platform ? 'platform' : 'store'];
        }

        return $vouchers;
    }

    private function createPromotions(
        int $count,
        array $stores,
        array $products,
        array $categories,
        string $adminId,
        Generator $faker,
        Carbon $now
    ): void {
        for ($index = 1; $index <= $count; $index++) {
            $platform = $index % 5 === 0;
            $store = $stores[($index - 1) % count($stores)];
            $product = $products[($index - 1) % count($products)];
            $category = $categories[($index - 1) % count($categories)];
            $approval = $platform ? 'approved' : ($index % 7 === 0 ? 'rejected' : ($index % 4 === 0 ? 'pending' : 'approved'));
            $creator = $platform ? $adminId : $store['user_id'];
            $action = $index % 2 === 0 ? 'product' : 'category';

            DB::table('promotions')->insert([
                'store_id' => $platform ? null : $store['id'],
                'name' => sprintf('promotion faker %03d', $index),
                'image_url' => sprintf('https://picsum.photos/seed/promotion-faker-%03d/1600/500', $index),
                'mobile_image_url' => sprintf('https://picsum.photos/seed/promotion-faker-mobile-%03d/900/900', $index),
                'click_action' => $action,
                'target_id' => $action === 'product' ? $product['id'] : $category['id'],
                'target_url' => null,
                'sort_order' => $index,
                'is_active' => $index % 12 !== 0,
                'approval_status' => $approval,
                'rejection_reason' => $approval === 'rejected' ? 'Data promotion faker ditolak untuk pengujian.' : null,
                'submitted_at' => $now->copy()->subHours($index),
                'approved_at' => $approval === 'approved' ? $now : null,
                'approved_by' => $approval === 'approved' ? $adminId : null,
                'created_by' => $creator,
                'updated_by' => $creator,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }
    }

    private function createBuyerData(
        int $count,
        array $buyers,
        array $products,
        array $vouchers,
        Generator $faker,
        Carbon $now
    ): void {
        foreach ($buyers as $buyer) {
            DB::table('addresses')->insert([
                'user_id' => $buyer['id'],
                'store_id' => null,
                'country' => 'Indonesia',
                'province' => $faker->randomElement(['DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'Jawa Timur', 'Banten', 'DI Yogyakarta']),
                'city_or_regency' => $faker->city(),
                'district' => 'Kecamatan Buyer',
                'subdistrict' => 'Kelurahan Buyer',
                'postal_code' => (string) $faker->numberBetween(10000, 99999),
                'full_address' => $faker->address(),
                'notes' => null,
                'label' => 'Rumah',
                'recipient_name' => $buyer['name'],
                'phone_number' => '08'.str_pad((string) $buyer['index'], 10, '0', STR_PAD_LEFT),
                'latitude' => $faker->randomFloat(8, -7.5, -5.5),
                'longitude' => $faker->randomFloat(8, 106, 112),
                'komerce_destination_id' => null,
                'is_primary' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $cartId = (int) DB::table('carts')->insertGetId([
                'user_id' => $buyer['id'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $product = $products[($buyer['index'] - 1) % count($products)];
            DB::table('cart_items')->insert([
                'cart_id' => $cartId,
                'product_variant_id' => $product['variant_id'],
                'quantity' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $wishlistId = (string) Str::uuid();
            DB::table('wishlists')->insert([
                'id' => $wishlistId,
                'user_id' => $buyer['id'],
                'name' => 'utama',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('wishlist_items')->insert([
                'wishlist_id' => $wishlistId,
                'product_id' => $product['id'],
                'added_at' => $now,
            ]);
        }

        for ($index = 1; $index <= $count; $index++) {
            $buyer = $buyers[($index - 1) % count($buyers)];
            $product = $products[($index - 1) % count($products)];
            $voucher = $vouchers[($index - 1) % count($vouchers)];
            $orderNumber = sprintf('FKR-%06d', $index);
            $shipping = $faker->randomElement([10000, 15000, 20000, 25000]);
            $subtotal = $product['price'] * $faker->numberBetween(1, 3);
            $discount = $voucher['scope'] === 'platform' || $voucher['store_id'] === $product['store_id'] ? min(10000, $subtotal) : 0;

            $orderId = (int) DB::table('orders')->insertGetId([
                'order_number' => $orderNumber,
                'user_id' => $buyer['id'],
                'voucher_id' => $discount > 0 ? $voucher['id'] : null,
                'total_amount' => $subtotal + $shipping,
                'discount_amount' => $discount,
                'shipping_discount_amount' => 0,
                'status' => $index % 8 === 0 ? 'completed' : 'pending',
                'payment_status' => $index % 4 === 0 ? 'paid' : 'unpaid',
                'payment_method' => 'bank_transfer',
                'midtrans_snap_token' => null,
                'shipping_address' => json_encode(['recipient' => $buyer['name'], 'address' => $faker->address()], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $subOrderId = (int) DB::table('sub_orders')->insertGetId([
                'order_id' => $orderId,
                'store_id' => $product['store_id'],
                'sub_order_number' => $orderNumber.'-01',
                'total_items_price' => $subtotal,
                'shipping_cost' => $shipping,
                'courier' => 'jne',
                'service' => 'reg',
                'destination_id' => 'FAKER-'.$index,
                'status' => $index % 8 === 0 ? 'completed' : 'pending',
                'tracking_number' => $index % 4 === 0 ? 'RESI'.str_pad((string) $index, 8, '0', STR_PAD_LEFT) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('order_items')->insert([
                'sub_order_id' => $subOrderId,
                'product_id' => $product['id'],
                'variant_id' => $product['variant_id'],
                'product_name' => sprintf('produk faker %03d', $product['index']),
                'sku' => sprintf('FKR-%03d-01', $product['index']),
                'price' => $product['price'],
                'quantity' => max(1, (int) round($subtotal / max(1, $product['price']))),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($index % 4 === 0) {
                DB::table('payments')->insert([
                    'order_number' => $orderNumber,
                    'transaction_id' => 'TRX-'.str_pad((string) $index, 8, '0', STR_PAD_LEFT),
                    'payment_method' => 'bank_transfer',
                    'amount' => max(0, $subtotal + $shipping - $discount),
                    'status' => 'settlement',
                    'payload' => json_encode(['faker' => true]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
