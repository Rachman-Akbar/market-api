<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seed realtime database content for the seller panel of
 * akbarfahlevy39@gmail.com (password: 123).
 *
 * This seeder is IDEMPOTENT (uses updateOrInsert / insert only when missing)
 * and strictly ADDITIVE. Re-running it never duplicates or deletes existing
 * data, and it never touches other stores/users.
 */
final class AkbarFahlevySellerSeeder extends Seeder
{
    private const EMAIL = 'akbarfahlevy39@gmail.com';
    private const PASSWORD = '123';
    private const STORE_NAME = 'Akbar Fahlevy Store';
    private const STORE_SLUG = 'akbar-fahlevy-store';

    public function run(): void
    {
        $now = now();
        $password = Hash::make(self::PASSWORD);
        $adminId = $this->getAdminId();

        DB::transaction(function () use ($now, $password, $adminId): void {
            $userId = $this->ensureUser($now, $password, $adminId);
            $this->syncRoles($userId, $now);

            $storeId = $this->ensureStore($userId, $now, $adminId);
            $this->ensureStoreDetails($storeId, $now);
            $this->ensureShippingSettings($storeId, $now);

            $categoryIds = $this->getCategoryIds();
            $products = $this->ensureProducts($storeId, $categoryIds, $now, $adminId);
            $variants = $this->ensureVariants($products, $now);

            $this->ensureOpeningStockMovements($variants, $storeId, $now, $adminId);

            $buyers = $this->ensureBuyers($now, $password, $adminId);
            $orders = $this->ensureOrders($buyers, $storeId, $variants, $now, $adminId);
            $this->ensurePayments($orders, $now);
            $this->ensureReviews($orders, $products, $buyers, $now, $adminId);

            $this->ensureFinance($storeId, $orders, $buyers, $now, $adminId);
            $this->ensureSettlements($storeId, $orders, $now, $adminId);
            $this->ensureWithdrawals($storeId, $userId, $now, $adminId);
            $this->ensureSchedules($userId, $storeId, $now);

            $this->ensurePromotions($storeId, $products, $now, $adminId);
            $this->ensureShowcases($storeId, $products, $now, $adminId);
        });

        $this->command->info('Akbar Fahlevy seller seeder completed!');
    }

    private function getAdminId(): string
    {
        return DB::table('users')->where('id', SeederIds::SUPER_ADMIN)->value('id')
            ?? DB::table('users')->orderBy('created_at')->value('id');
    }

    private function ensureUser(Carbon $now, string $password, string $adminId): string
    {
        $user = DB::table('users')->where('email', self::EMAIL)->first(['id']);

        $data = [
            'firebase_uid' => DB::table('users')->where('email', self::EMAIL)->value('firebase_uid'),
            'email' => self::EMAIL,
            'password' => $password,
            'name' => 'Mochammad Rachman Akbar Fahlevy',
            'avatar' => 'https://i.pravatar.cc/300?u=akbarfahlevy39@gmail.com',
            'is_email_verified' => true,
            'is_active' => true,
            'banned_at' => null,
            'updated_at' => $now,
            'deleted_at' => null,
        ];

        if ($user) {
            $data['email'] = self::EMAIL;
            DB::table('users')->where('id', $user->id)->update($data);

            return (string) $user->id;
        }

        $data['id'] = (string) Str::uuid();
        $data['created_at'] = $now;
        $data['created_by'] = $adminId;
        $data['updated_by'] = $adminId;
        DB::table('users')->insert($data);

        return $data['id'];
    }

    private function syncRoles(string $userId, Carbon $now): void
    {
        $roleIds = DB::table('roles')->pluck('id', 'name');

        foreach (['seller', 'buyer'] as $role) {
            if (isset($roleIds[$role])) {
                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $userId, 'role_id' => $roleIds[$role]],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    private function ensureStore(string $sellerId, Carbon $now, string $adminId): int
    {
        $existing = DB::table('stores')->where('user_id', $sellerId)->whereNull('deleted_at')->first(['id']);
        if ($existing) {
            return (int) $existing->id;
        }

        return (int) DB::table('stores')->insertGetId([
            'user_id' => $sellerId,
            'name' => self::STORE_NAME,
            'slug' => self::STORE_SLUG,
            'description' => 'Toko resmi Akbar Fahlevy - menyediakan produk berkualitas dengan harga terbaik.',
            'short_description' => 'Produk berkualitas, harga terbaik.',
            'phone' => '081553769480',
            'email' => self::EMAIL,
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'address' => 'Jl. Senopati No. 45, Kebayoran Baru, Jakarta Selatan',
            'status' => 'approved',
            'is_active' => true,
            'logo' => 'https://picsum.photos/seed/akbar-fahlevy-logo/300/300',
            'banner_url' => 'https://picsum.photos/seed/akbar-fahlevy-banner/1600/500',
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);
    }

    private function ensureStoreDetails(int $storeId, Carbon $now): void
    {
        DB::table('store_details')->updateOrInsert(
            ['store_id' => $storeId],
            [
                'owner_name' => 'Mochammad Rachman Akbar Fahlevy',
                'owner_phone' => '081553769480',
                'description' => 'Toko resmi Akbar Fahlevy.',
                'shipping_policy' => 'Pesanan diproses dalam 1-2 hari kerja.',
                'return_policy' => 'Pengembalian diterima dalam 7 hari.',
                'open_days' => 'senin-minggu',
                'open_time' => '08:00:00',
                'close_time' => '22:00:00',
                'whatsapp_url' => 'https://wa.me/6281553769480',
                'instagram_url' => 'https://instagram.com/akbarfahlevy',
                'tiktok_url' => null,
                'website_url' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private function ensureShippingSettings(int $storeId, Carbon $now): void
    {
        DB::table('shipping_settings')->updateOrInsert(
            ['store_id' => $storeId],
            [
                'store_latitude' => -6.22700000,
                'store_longitude' => 106.80100000,
                'free_shipping_max_distance' => 10,
                'default_flat_rate' => 15000,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private function getCategoryIds(): array
    {
        return DB::table('categories')
            ->whereIn('slug', ['makanan', 'minuman', 'elektronik', 'dekorasi-rumah', 'fashion', 'kecantikan'])
            ->pluck('id', 'slug')
            ->all();
    }

    private function ensureProducts(int $storeId, array $categoryIds, Carbon $now, string $adminId): array
    {
        $items = [
            ['name' => 'Nasi Goreng Spesial', 'slug' => 'nasi-goreng-spesial', 'price' => 35000, 'category' => 'makanan', 'stock' => 150],
            ['name' => 'Ayam Geprek Sambal Matah', 'slug' => 'ayam-geprek-sambal-matah', 'price' => 38000, 'category' => 'makanan', 'stock' => 130],
            ['name' => 'Bakso Urat Special', 'slug' => 'bakso-urat-special', 'price' => 38000, 'category' => 'makanan', 'stock' => 120],
            ['name' => 'Sate Ayam Madura', 'slug' => 'sate-ayam-madura', 'price' => 45000, 'category' => 'makanan', 'stock' => 80],
            ['name' => 'Kopi Robusta Premium', 'slug' => 'kopi-robusta-premium', 'price' => 85000, 'category' => 'minuman', 'stock' => 200],
            ['name' => 'Teh Hijau Organic', 'slug' => 'teh-hijau-organic', 'price' => 65000, 'category' => 'minuman', 'stock' => 180],
            ['name' => 'Jus Jeruk Segar', 'slug' => 'jus-jeruk-segar', 'price' => 25000, 'category' => 'minuman', 'stock' => 150],
            ['name' => 'Speaker Bluetooth Portable', 'slug' => 'speaker-bluetooth-portable', 'price' => 250000, 'category' => 'elektronik', 'stock' => 50],
            ['name' => 'Headphone Wireless Pro', 'slug' => 'headphone-wireless-pro', 'price' => 450000, 'category' => 'elektronik', 'stock' => 35],
            ['name' => 'Powerbank 20000mAh', 'slug' => 'powerbank-20000mah', 'price' => 299000, 'category' => 'elektronik', 'stock' => 60],
            ['name' => 'Charger Fast Charging 65W', 'slug' => 'charger-fast-charging-65w', 'price' => 199000, 'category' => 'elektronik', 'stock' => 75],
            ['name' => 'Lampu Meja LED Minimalis', 'slug' => 'lampu-meja-led-minimalis', 'price' => 175000, 'category' => 'dekorasi-rumah', 'stock' => 90],
            ['name' => 'Selimut Bulu Premium', 'slug' => 'selimut-bulu-premium', 'price' => 289000, 'category' => 'dekorasi-rumah', 'stock' => 45],
            ['name' => 'Sarung Bantal Velvet', 'slug' => 'sarung-bantal-velvet', 'price' => 125000, 'category' => 'dekorasi-rumah', 'stock' => 100],
            ['name' => 'Kaos Polos Cotton Combed', 'slug' => 'kaos-polos-cotton-combed', 'price' => 95000, 'category' => 'fashion', 'stock' => 200],
            ['name' => 'Hoodie Oversize Premium', 'slug' => 'hoodie-oversize-premium', 'price' => 195000, 'category' => 'fashion', 'stock' => 85],
        ];

        $created = [];
        foreach ($items as $index => $item) {
            $categoryId = $categoryIds[$item['category']] ?? null;
            $slug = 'af-' . $item['slug'];
            $createdAt = $now->copy()->subDays(30 - $index);

            $existing = DB::table('products')->where('slug', $slug)->whereNull('deleted_at')->first(['id']);
            if ($existing) {
                $productId = (int) $existing->id;
            } else {
                $productId = (int) DB::table('products')->insertGetId([
                    'store_id' => $storeId,
                    'primary_category_id' => $categoryId,
                    'name' => $item['name'],
                    'slug' => $slug,
                    'description' => 'Deskripsi lengkap untuk ' . $item['name'] . '.',
                    'brand' => 'Akbar Fahlevy',
                    'thumbnail' => 'https://picsum.photos/seed/' . $slug . '/800/800',
                    'status' => 'published',
                    'is_active' => true,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'deleted_at' => null,
                ]);
            }

            if ($categoryId) {
                DB::table('product_categories')->updateOrInsert(
                    ['product_id' => $productId, 'category_id' => $categoryId],
                    ['is_primary' => true, 'created_at' => $createdAt, 'updated_at' => $createdAt]
                );
            }

            $created[] = [
                'id' => $productId,
                'store_id' => $storeId,
                'price' => $item['price'],
                'stock' => $item['stock'],
                'created_at' => $createdAt,
            ];
        }

        return $created;
    }

    private function ensureVariants(array $products, Carbon $now): array
    {
        $variants = [];
        foreach ($products as $product) {
            $existing = DB::table('product_variants')
                ->where('product_id', $product['id'])->where('name', 'Default')
                ->first(['id']);
            if ($existing) {
                $variantId = (int) $existing->id;
            } else {
                $variantId = (int) DB::table('product_variants')->insertGetId([
                    'product_id' => $product['id'],
                    'store_id' => $product['store_id'],
                    'name' => 'Default',
                    'sku' => 'AF-' . strtoupper(Str::random(8)),
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'is_default' => true,
                    'created_at' => $product['created_at'],
                    'updated_at' => $product['created_at'],
                ]);
            }

            $variants[] = [
                'id' => $variantId,
                'product_id' => $product['id'],
                'store_id' => $product['store_id'],
                'price' => $product['price'],
                'stock' => $product['stock'],
                'created_at' => $product['created_at'],
            ];
        }

        return $variants;
    }

    private function ensureOpeningStockMovements(array $variants, int $storeId, Carbon $now, string $adminId): void
    {
        foreach ($variants as $index => $variant) {
            $key = 'af-opening-' . ($index + 1);
            DB::table('stock_movements')->updateOrInsert(
                ['store_id' => $storeId, 'variant_id' => $variant['id'], 'movement_key' => $key],
                [
                    'product_id' => $variant['product_id'],
                    'order_id' => null,
                    'order_item_id' => null,
                    'movement_key' => $key,
                    'type' => 'opening_balance',
                    'quantity_delta' => $variant['stock'],
                    'balance_after' => $variant['stock'],
                    'reference_type' => 'seed',
                    'reference_id' => null,
                    'notes' => 'Stock awal dari seeder Akbar Fahlevy',
                    'occurred_at' => $variant['created_at'],
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $variant['created_at'],
                    'updated_at' => $variant['created_at'],
                    'deleted_at' => null,
                ]
            );
        }
    }

    private function ensureBuyers(Carbon $now, string $password, string $adminId): array
    {
        $buyersData = [
            ['id' => '00000000-0000-4000-8000-000000000a01', 'name' => 'Rina Susanti', 'email' => 'rina.af@gmail.com'],
            ['id' => '00000000-0000-4000-8000-000000000a02', 'name' => 'Dedi Kurniawan', 'email' => 'dedi.af@gmail.com'],
            ['id' => '00000000-0000-4000-8000-000000000a03', 'name' => 'Maya Putri', 'email' => 'maya.af@gmail.com'],
            ['id' => '00000000-0000-4000-8000-000000000a04', 'name' => 'Andi Wijaya', 'email' => 'andi.af@gmail.com'],
            ['id' => '00000000-0000-4000-8000-000000000a05', 'name' => 'Sinta Dewi', 'email' => 'sinta.af@gmail.com'],
        ];

        $roleId = DB::table('roles')->where('name', 'buyer')->value('id');
        $created = [];

        foreach ($buyersData as $buyer) {
            $exists = DB::table('users')->where('id', $buyer['id'])->exists();
            if (!$exists) {
                DB::table('users')->insert([
                    'id' => $buyer['id'],
                    'firebase_uid' => null,
                    'email' => $buyer['email'],
                    'password' => $password,
                    'name' => $buyer['name'],
                    'avatar' => 'https://i.pravatar.cc/300?u=' . $buyer['email'],
                    'is_email_verified' => true,
                    'is_active' => true,
                    'banned_at' => null,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);
            }

            if ($roleId) {
                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $buyer['id'], 'role_id' => $roleId],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }

            DB::table('addresses')->updateOrInsert(
                ['user_id' => $buyer['id'], 'label' => 'Rumah'],
                [
                    'country' => 'Indonesia',
                    'province' => 'DKI Jakarta',
                    'city_or_regency' => 'Jakarta Selatan',
                    'district' => 'Kebayoran Baru',
                    'subdistrict' => 'Melawai',
                    'postal_code' => '12160',
                    'full_address' => 'Jl. Blok M No. ' . rand(1, 100) . ', Jakarta Selatan',
                    'notes' => 'Rumah warna cat biru',
                    'label' => 'Rumah',
                    'recipient_name' => $buyer['name'],
                    'phone_number' => '0812' . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'latitude' => -6.24000000,
                    'longitude' => 106.79000000,
                    'komerce_destination_id' => null,
                    'is_primary' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $created[] = ['id' => $buyer['id'], 'name' => $buyer['name']];
        }

        return $created;
    }

    private function ensureOrders(array $buyers, int $storeId, array $variants, Carbon $now, string $adminId): array
    {
        $existingOrders = DB::table('orders')->join('sub_orders', 'sub_orders.order_id', '=', 'orders.id')
            ->where('sub_orders.store_id', $storeId)->pluck('orders.id')->all();
        $orders = [];
        $statuses = ['completed', 'completed', 'completed', 'shipped', 'processing', 'pending', 'cancelled'];
        $orderNumber = count($existingOrders) + 1;

        foreach ($buyers as $buyerIndex => $buyer) {
            for ($orderIndex = 0; $orderIndex < 3; $orderIndex++) {
                $variant = $variants[($buyerIndex * 3 + $orderIndex) % count($variants)];
                $status = $statuses[($buyerIndex + $orderIndex) % count($statuses)];
                $quantity = rand(1, 3);
                $itemTotal = $variant['price'] * $quantity;
                $shippingCost = rand(12000, 25000);
                $adminFee = round($itemTotal * 0.05, 2);
                $sellerNet = $itemTotal - $adminFee;
                $totalAmount = $itemTotal + $shippingCost;
                $createdAt = $now->copy()->subDays(rand(1, 30))->subHours(rand(0, 23));

                $orderNumberStr = 'AF-' . $createdAt->format('Ymd') . '-' . str_pad((string) $orderNumber, 4, '0', STR_PAD_LEFT);
                $orderNumber++;

                $existingByNum = DB::table('orders')->where('order_number', $orderNumberStr)->first(['id']);
                if ($existingByNum) {
                    $orderId = (int) $existingByNum->id;
                } else {
                    $orderId = (int) DB::table('orders')->insertGetId([
                        'order_number' => $orderNumberStr,
                        'order_type' => 'normal',
                        'preorder_release_at' => null,
                        'booking_expires_at' => null,
                        'received_at' => $status === 'completed' ? $createdAt->copy()->addDays(2) : null,
                        'user_id' => $buyer['id'],
                        'voucher_id' => null,
                        'total_amount' => $totalAmount,
                        'discount_amount' => 0,
                        'shipping_discount_amount' => 0,
                        'admin_fee' => $adminFee,
                        'seller_net' => $sellerNet,
                        'status' => $status,
                        'payment_status' => in_array($status, ['completed', 'shipped', 'processing']) ? 'paid' : ($status === 'cancelled' ? 'refunded' : 'pending'),
                        'payment_method' => 'bank_transfer',
                        'midtrans_snap_token' => null,
                        'shipping_address' => json_encode(['name' => $buyer['name'], 'address' => 'Jl. Blok M No. ' . rand(1, 100), 'city' => 'Jakarta Selatan', 'province' => 'DKI Jakarta'], JSON_UNESCAPED_UNICODE),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }

                $existingSub = DB::table('sub_orders')->where('order_id', $orderId)->where('store_id', $storeId)->first(['id']);
                if (!$existingSub) {
                    $soAdminFee = round($adminFee * 0.8, 2);
                    $soSellerNet = $itemTotal - $soAdminFee;
                    $subOrderId = (int) DB::table('sub_orders')->insertGetId([
                        'order_id' => $orderId,
                        'store_id' => $storeId,
                        'sub_order_number' => $orderNumberStr . '-SO1',
                        'total_items_price' => $itemTotal,
                        'shipping_cost' => $shippingCost,
                        'admin_fee' => $soAdminFee,
                        'seller_net' => $soSellerNet,
                        'courier' => 'jne',
                        'service' => 'REG',
                        'destination_id' => '3171',
                        'status' => $status,
                        'tracking_number' => $status === 'shipped' ? 'JNE' . strtoupper(Str::random(10)) : null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                } else {
                    $subOrderId = (int) $existingSub->id;
                }

                $hasItem = DB::table('order_items')->where('sub_order_id', $subOrderId)->where('variant_id', $variant['id'])->exists();
                if (!$hasItem) {
                    DB::table('order_items')->insert([
                        'sub_order_id' => $subOrderId,
                        'product_id' => $variant['product_id'],
                        'variant_id' => $variant['id'],
                        'product_name' => DB::table('products')->where('id', $variant['product_id'])->value('name'),
                        'sku' => 'AF-' . strtoupper(Str::random(8)),
                        'price' => $variant['price'],
                        'quantity' => $quantity,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }

                if ($status !== 'cancelled') {
                    DB::table('stock_movements')->updateOrInsert(
                        ['store_id' => $storeId, 'variant_id' => $variant['id'], 'order_id' => $orderId, 'movement_key' => 'af-order-mov-' . $orderId . '-' . $variant['id']],
                        [
                            'product_id' => $variant['product_id'],
                            'order_item_id' => null,
                            'type' => 'sale',
                            'quantity_delta' => -$quantity,
                            'balance_after' => max(0, $variant['stock'] - $quantity),
                            'reference_type' => 'order',
                            'reference_id' => (string) $orderId,
                            'notes' => 'Penjualan dari order #' . $orderNumberStr,
                            'occurred_at' => $createdAt,
                            'created_by' => $adminId,
                            'updated_by' => $adminId,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                            'deleted_at' => null,
                        ]
                    );
                }

                $orders[] = [
                    'id' => $orderId,
                    'sub_order_id' => $subOrderId,
                    'user_id' => $buyer['id'],
                    'total_amount' => $totalAmount,
                    'item_total' => $itemTotal,
                    'shipping_cost' => $shippingCost,
                    'status' => $status,
                    'created_at' => $createdAt,
                ];
            }
        }

        return $orders;
    }

    private function ensurePayments(array $orders, Carbon $now): void
    {
        foreach ($orders as $order) {
            if ($order['status'] === 'cancelled') {
                continue;
            }

            $paidAt = $order['created_at']->copy()->addMinutes(rand(5, 60));
            $orderNumber = DB::table('orders')->where('id', $order['id'])->value('order_number');

            DB::table('payments')->updateOrInsert(
                ['order_number' => $orderNumber],
                [
                    'transaction_id' => 'AF-PAY-' . strtoupper(Str::random(8)),
                    'payment_method' => 'bank_transfer',
                    'amount' => $order['total_amount'],
                    'status' => 'settlement',
                    'payload' => json_encode(['seeded' => true, 'akbar_fahlevy' => true], JSON_UNESCAPED_UNICODE),
                    'created_at' => $paidAt,
                    'updated_at' => $paidAt,
                ]
            );
        }
    }

    private function ensureReviews(array $orders, array $products, array $buyers, Carbon $now, string $adminId): void
    {
        $texts = [
            'Produk sangat berkualitas! Pengiriman juga cepat.',
            'Barang sesuai deskripsi, kemasan rapi. Puas!',
            'Harga terjangkau untuk kualitas sebaik ini.',
            'Pelayanan seller sangat baik, responsif.',
        ];
        $ratings = [5, 5, 4, 5];

        foreach ($orders as $index => $order) {
            if ($order['status'] !== 'completed') {
                continue;
            }

            $orderItem = DB::table('order_items')
                ->where('sub_order_id', $order['sub_order_id'])
                ->first();

            if (!$orderItem) {
                continue;
            }

            $product = $products[$index % count($products)];
            $buyer = $buyers[$index % count($buyers)];
            $createdAt = $order['created_at']->copy()->addDays(3);

            DB::table('product_reviews')->updateOrInsert(
                ['order_item_id' => $orderItem->id],
                [
                    'product_id' => $product['id'],
                    'order_id' => $order['id'],
                    'user_id' => $buyer['id'],
                    'rating' => $ratings[$index % count($ratings)],
                    'review' => $texts[$index % count($texts)],
                    'media' => null,
                    'is_active' => true,
                    'created_by' => $buyer['id'],
                    'updated_by' => $adminId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'deleted_at' => null,
                ]
            );
        }
    }

    private function ensureFinance(int $storeId, array $orders, array $buyers, Carbon $now, string $adminId): void
    {
        $incomeCount = 0;
        $expenseCount = 0;
        $receivableCount = 0;
        $payableCount = 0;

        foreach ($orders as $index => $order) {
            $createdAt = $order['created_at'];
            $occurredAt = $createdAt->copy()->addHour();

            if ($order['status'] === 'completed') {
                DB::table('financial_transactions')->updateOrInsert(
                    ['reference_number' => 'FIN-AF-INC-' . str_pad((string) (++$incomeCount), 4, '0', STR_PAD_LEFT)],
                    [
                        'store_id' => $storeId,
                        'order_id' => $order['id'],
                        'user_id' => $order['user_id'],
                        'type' => 'income',
                        'title' => 'Pendapatan dari order',
                        'description' => 'Pendapatan penjualan produk',
                        'amount' => $order['item_total'],
                        'paid_amount' => $order['item_total'],
                        'status' => 'paid',
                        'due_date' => null,
                        'occurred_at' => $occurredAt,
                        'settled_at' => $occurredAt,
                        'is_active' => true,
                        'metadata' => json_encode(['seeded' => true, 'akbar_fahlevy' => true], JSON_UNESCAPED_UNICODE),
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                        'created_at' => $occurredAt,
                        'updated_at' => $occurredAt,
                        'deleted_at' => null,
                    ]
                );
            }

            if ($index % 4 === 0) {
                $expenseAmount = rand(50000, 500000);
                $expenseAt = $createdAt->copy()->subDay();
                DB::table('financial_transactions')->updateOrInsert(
                    ['reference_number' => 'FIN-AF-EXP-' . str_pad((string) (++$expenseCount), 4, '0', STR_PAD_LEFT)],
                    [
                        'store_id' => $storeId,
                        'order_id' => null,
                        'user_id' => null,
                        'type' => 'expense',
                        'title' => 'Pengeluaran operasional',
                        'description' => 'Biaya operasional toko',
                        'amount' => $expenseAmount,
                        'paid_amount' => $expenseAmount,
                        'status' => 'paid',
                        'due_date' => null,
                        'occurred_at' => $expenseAt,
                        'settled_at' => $expenseAt,
                        'is_active' => true,
                        'metadata' => json_encode(['seeded' => true, 'akbar_fahlevy' => true], JSON_UNESCAPED_UNICODE),
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                        'created_at' => $expenseAt,
                        'updated_at' => $expenseAt,
                        'deleted_at' => null,
                    ]
                );
            }

            if ($index % 5 === 0) {
                $receivableAmount = rand(100000, 1000000);
                $dueDate = $now->copy()->addDays(rand(-15, 30));
                DB::table('financial_transactions')->updateOrInsert(
                    ['reference_number' => 'FIN-AF-RCV-' . str_pad((string) (++$receivableCount), 4, '0', STR_PAD_LEFT)],
                    [
                        'store_id' => $storeId,
                        'order_id' => null,
                        'user_id' => $order['user_id'],
                        'type' => 'receivable',
                        'title' => 'Piutang dari pelanggan',
                        'description' => 'Piutang penjualan',
                        'amount' => $receivableAmount,
                        'paid_amount' => round($receivableAmount * 0.4),
                        'status' => 'partial',
                        'due_date' => $dueDate->toDateString(),
                        'occurred_at' => $createdAt,
                        'settled_at' => null,
                        'is_active' => true,
                        'metadata' => json_encode(['seeded' => true, 'akbar_fahlevy' => true], JSON_UNESCAPED_UNICODE),
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                        'deleted_at' => null,
                    ]
                );
            }

            if ($index % 6 === 0) {
                $payableAmount = rand(100000, 800000);
                $dueDate = $now->copy()->addDays(rand(-10, 20));
                DB::table('financial_transactions')->updateOrInsert(
                    ['reference_number' => 'FIN-AF-PAY-' . str_pad((string) (++$payableCount), 4, '0', STR_PAD_LEFT)],
                    [
                        'store_id' => $storeId,
                        'order_id' => null,
                        'user_id' => null,
                        'type' => 'payable',
                        'title' => 'Hutang ke supplier',
                        'description' => 'Pembayaran ke supplier',
                        'amount' => $payableAmount,
                        'paid_amount' => round($payableAmount * 0.3),
                        'status' => 'partial',
                        'due_date' => $dueDate->toDateString(),
                        'occurred_at' => $createdAt,
                        'settled_at' => null,
                        'is_active' => true,
                        'metadata' => json_encode(['seeded' => true, 'akbar_fahlevy' => true], JSON_UNESCAPED_UNICODE),
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                        'deleted_at' => null,
                    ]
                );
            }
        }
    }

    private function ensureSettlements(int $storeId, array $orders, Carbon $now, string $adminId): void
    {
        $count = 0;
        foreach ($orders as $order) {
            if ($order['status'] !== 'completed') {
                continue;
            }

            $adminFee = round($order['item_total'] * 0.05, 2);
            $netAmount = $order['item_total'] - $adminFee;
            $settlementNumber = 'STL-AF-' . str_pad((string) (++$count), 4, '0', STR_PAD_LEFT);

            DB::table('seller_settlements')->updateOrInsert(
                ['store_id' => $storeId, 'order_id' => $order['id']],
                [
                    'sub_order_id' => $order['sub_order_id'],
                    'settlement_number' => $settlementNumber,
                    'gross_amount' => $order['item_total'],
                    'admin_fee' => $adminFee,
                    'shipping_fee' => 0,
                    'net_amount' => $netAmount,
                    'status' => $count % 3 === 0 ? 'pending' : 'settled',
                    'settled_at' => $count % 3 !== 0 ? $order['created_at']->copy()->addDay() : null,
                    'notes' => null,
                    'metadata' => json_encode(['seeded' => true, 'akbar_fahlevy' => true], JSON_UNESCAPED_UNICODE),
                    'is_active' => true,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $order['created_at'],
                    'updated_at' => $order['created_at'],
                    'deleted_at' => null,
                ]
            );
        }
    }

    private function ensureWithdrawals(int $storeId, string $sellerId, Carbon $now, string $adminId): void
    {
        $withdrawals = [
            ['amount' => 500000, 'method' => 'bank_transfer', 'status' => 'completed', 'daysAgo' => 25],
            ['amount' => 1000000, 'method' => 'bank_transfer', 'status' => 'completed', 'daysAgo' => 18],
            ['amount' => 750000, 'method' => 'e_wallet', 'status' => 'approved', 'daysAgo' => 10],
            ['amount' => 300000, 'method' => 'bank_transfer', 'status' => 'pending', 'daysAgo' => 3],
        ];

        foreach ($withdrawals as $index => $withdrawal) {
            $createdAt = $now->copy()->subDays($withdrawal['daysAgo']);
            $number = 'WD-AF-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
            $bankDetails = $withdrawal['method'] === 'bank_transfer'
                ? json_encode(['bank_name' => 'Bank BCA', 'account_number' => '1234567890', 'account_name' => 'Akbar Fahlevy'], JSON_UNESCAPED_UNICODE)
                : null;

            DB::table('seller_withdrawals')->updateOrInsert(
                ['withdrawal_number' => $number],
                [
                    'store_id' => $storeId,
                    'user_id' => $sellerId,
                    'amount' => $withdrawal['amount'],
                    'method' => $withdrawal['method'],
                    'bank_details' => $bankDetails,
                    'status' => $withdrawal['status'],
                    'rejection_reason' => null,
                    'processed_at' => $withdrawal['status'] !== 'pending' ? $createdAt->copy()->addDay() : null,
                    'processed_by' => $withdrawal['status'] !== 'pending' ? $adminId : null,
                    'is_active' => true,
                    'created_by' => $sellerId,
                    'updated_by' => $adminId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'deleted_at' => null,
                ]
            );
        }
    }

    private function ensureSchedules(string $sellerId, int $storeId, Carbon $now): void
    {
        $schedules = [
            ['title' => 'Cek Stok Gudang', 'type' => 'task', 'priority' => 'high', 'date' => $now->toDateString(), 'start_time' => '09:00:00', 'end_time' => '11:00:00'],
            ['title' => 'Meeting Supplier', 'type' => 'meeting', 'priority' => 'high', 'date' => $now->copy()->addDay()->toDateString(), 'start_time' => '14:00:00', 'end_time' => '15:30:00'],
            ['title' => 'Kirim Pesanan', 'type' => 'task', 'priority' => 'normal', 'date' => $now->copy()->addDay()->toDateString(), 'start_time' => '10:00:00', 'end_time' => '12:00:00'],
            ['title' => 'Restock Kopi Robusta', 'type' => 'task', 'priority' => 'normal', 'date' => $now->copy()->addDays(2)->toDateString(), 'start_time' => '08:00:00', 'end_time' => '10:00:00'],
            ['title' => 'Evaluasi Penjualan Mingguan', 'type' => 'task', 'priority' => 'high', 'date' => $now->copy()->addDays(3)->toDateString(), 'start_time' => '13:00:00', 'end_time' => '14:00:00'],
            ['title' => 'Promo Flash Sale', 'type' => 'reminder', 'priority' => 'urgent', 'date' => $now->copy()->addDays(5)->toDateString(), 'start_time' => '00:00:00', 'end_time' => '23:59:00'],
        ];

        foreach ($schedules as $schedule) {
            $createdAt = $now->copy()->subDays(rand(0, 5));
            $exists = DB::table('schedules')
                ->where('user_id', $sellerId)->where('store_id', $storeId)
                ->where('title', $schedule['title'])->where('date', $schedule['date'])
                ->exists();
            if ($exists) {
                continue;
            }

            DB::table('schedules')->insert([
                'user_id' => $sellerId,
                'store_id' => $storeId,
                'title' => $schedule['title'],
                'description' => 'Jadwal untuk ' . $schedule['title'],
                'type' => $schedule['type'],
                'priority' => $schedule['priority'],
                'color' => match ($schedule['priority']) {
                    'urgent' => '#EF4444',
                    'high' => '#F59E0B',
                    'normal' => '#10B981',
                    'low' => '#6B7280',
                },
                'date' => $schedule['date'],
                'start_time' => $schedule['start_time'] ?? null,
                'end_time' => $schedule['end_time'] ?? null,
                'is_all_day' => false,
                'is_completed' => false,
                'completed_at' => null,
                'metadata' => json_encode(['seeded' => true, 'akbar_fahlevy' => true], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_by' => $sellerId,
                'updated_by' => $sellerId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
        }
    }

    private function ensurePromotions(int $storeId, array $products, Carbon $now, string $adminId): void
    {
        $promotions = [
            ['name' => 'AF Flash Sale Nasi Goreng', 'target_product_index' => 0],
            ['name' => 'AF Promo Kopi Premium', 'target_product_index' => 4],
            ['name' => 'AF Diskon Speaker', 'target_product_index' => 7],
        ];

        foreach ($promotions as $promo) {
            $product = $products[$promo['target_product_index'] % count($products)];
            $createdAt = $now->copy()->subDays(5);

            DB::table('promotions')->updateOrInsert(
                ['name' => $promo['name']],
                [
                    'promotion_payment_id' => null,
                    'image_url' => 'https://picsum.photos/seed/promo-' . Str::slug($promo['name']) . '/800/400',
                    'mobile_image_url' => null,
                    'click_action' => 'product',
                    'target_id' => $product['id'],
                    'target_url' => null,
                    'sort_order' => 1,
                    'is_active' => true,
                    'approval_status' => 'approved',
                    'rejection_reason' => null,
                    'submitted_at' => $createdAt,
                    'approved_at' => $createdAt,
                    'approved_by' => $adminId,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'deleted_at' => null,
                ]
            );
        }
    }

    private function ensureShowcases(int $storeId, array $products, Carbon $now, string $adminId): void
    {
        $showcases = [
            ['name' => 'Produk Terlaris', 'slug' => 'produk-terlaris-akbar-fahlevy', 'description' => 'Koleksi produk terlaris'],
            ['name' => 'Promo Hari Ini', 'slug' => 'promo-hari-ini-akbar-fahlevy', 'description' => 'Promo spesial hari ini'],
            ['name' => 'Produk Baru', 'slug' => 'produk-baru-akbar-fahlevy', 'description' => 'Produk terbaru kami'],
        ];

        foreach ($showcases as $index => $showcase) {
            $createdAt = $now->copy()->subDays(10 + $index);
            $existing = DB::table('showcases')->where('slug', $showcase['slug'])->where('store_id', $storeId)->whereNull('deleted_at')->first(['id']);
            if ($existing) {
                continue;
            }

            $showcaseId = (int) DB::table('showcases')->insertGetId([
                'store_id' => $storeId,
                'name' => $showcase['name'],
                'slug' => $showcase['slug'],
                'description' => $showcase['description'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);

            $productIndex = $index * 3;
            for ($i = 0; $i < 3 && ($productIndex + $i) < count($products); $i++) {
                $product = $products[$productIndex + $i];
                DB::table('showcase_products')->updateOrInsert(
                    ['showcase_id' => $showcaseId, 'product_id' => $product['id']],
                    ['sort_order' => $i + 1, 'created_at' => $createdAt, 'updated_at' => $createdAt]
                );
            }
        }
    }
}
