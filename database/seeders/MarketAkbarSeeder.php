<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class MarketAkbarSeeder extends Seeder
{
    private const PASSWORD = '12345678';
    private const MARKET_AKBAR_USER_ID = '00000000-0000-4000-8000-000000000301';
    private const MARKET_AKBAR_BUYER_1 = '00000000-0000-4000-8000-000000000401';
    private const MARKET_AKBAR_BUYER_2 = '00000000-0000-4000-8000-000000000402';
    private const MARKET_AKBAR_BUYER_3 = '00000000-0000-4000-8000-000000000403';
    private const MARKET_AKBAR_BUYER_4 = '00000000-0000-4000-8000-000000000404';
    private const MARKET_AKBAR_BUYER_5 = '00000000-0000-4000-8000-000000000405';

    public function run(): void
    {
        $now = now();
        $password = Hash::make(self::PASSWORD);

        DB::transaction(function () use ($now, $password): void {
            $adminId = $this->getAdminId();
            $roleIds = DB::table('roles')->pluck('id', 'name');

            $sellerId = $this->createSeller($now, $password, $adminId, $roleIds);
            $storeId = $this->createStore($sellerId, $now, $adminId);
            $this->createStoreDetails($storeId, $now);
            $this->createShippingSettings($storeId, $now);

            $categoryIds = $this->getCategoryIds();
            $products = $this->createProducts($storeId, $categoryIds, $now, $adminId);
            $variants = $this->createVariants($products, $now);
            $this->createStockMovements($variants, $storeId, $now, $adminId);

            $buyers = $this->createBuyers($now, $password, $adminId, $roleIds);
            $orders = $this->createOrders($buyers, $storeId, $variants, $now, $adminId);
            $this->createPayments($orders, $now);
            $this->createReviews($orders, $products, $buyers, $now, $adminId);

            $this->createFinanceData($storeId, $orders, $buyers, $now, $adminId);
            $this->createSettlements($storeId, $orders, $now, $adminId);
            $this->createWithdrawals($storeId, $sellerId, $now, $adminId);

            $this->createSchedules($sellerId, $storeId, $now, $adminId);
            $this->createAdminFeeConfigs($now, $adminId);
            $this->createPromotions($storeId, $products, $now, $adminId);
            $this->createShowcases($storeId, $products, $now, $adminId);
            $this->createAdminNotifications($adminId, $buyers, $storeId, $now);
        });

        $this->command->info('Market Akbar seeder completed!');
    }

    private function getAdminId(): string
    {
        return DB::table('users')->where('id', SeederIds::SUPER_ADMIN)->value('id')
            ?? DB::table('users')->orderBy('created_at')->value('id');
    }

    private function createSeller(Carbon $now, string $password, string $adminId, $roleIds): string
    {
        $userId = self::MARKET_AKBAR_USER_ID;

        DB::table('users')->updateOrInsert(
            ['id' => $userId],
            [
                'firebase_uid' => null,
                'email' => 'market.akbar@gmail.com',
                'password' => $password,
                'name' => 'Ahmad Market Akbar',
                'avatar' => 'https://i.pravatar.cc/300?u=market.akbar@gmail.com',
                'is_email_verified' => true,
                'is_active' => true,
                'banned_at' => null,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]
        );

        if (isset($roleIds['seller'])) {
            DB::table('user_roles')->updateOrInsert(
                ['user_id' => $userId, 'role_id' => $roleIds['seller']],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
        if (isset($roleIds['buyer'])) {
            DB::table('user_roles')->updateOrInsert(
                ['user_id' => $userId, 'role_id' => $roleIds['buyer']],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        return $userId;
    }

    private function createStore(string $sellerId, Carbon $now, string $adminId): int
    {
        $existing = DB::table('stores')->where('user_id', $sellerId)->whereNull('deleted_at')->first(['id']);
        if ($existing) {
            return (int) $existing->id;
        }

        return (int) DB::table('stores')->insertGetId([
            'user_id' => $sellerId,
            'name' => 'Market Akbar',
            'slug' => 'market-akbar',
            'description' => 'Marketplace terlengkap dengan ribuan produk berkualitas.',
            'short_description' => 'Marketplace terlengkap Indonesia',
            'phone' => '081234567890',
            'email' => 'info@marketakbar.co.id',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'address' => 'Jl. Pintu Besar Utama No. 1, Mangga Dua, Jakarta Pusat',
            'status' => 'approved',
            'is_active' => true,
            'logo' => 'https://picsum.photos/seed/market-akbar-logo/300/300',
            'banner_url' => 'https://picsum.photos/seed/market-akbar-banner/1600/500',
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);
    }

    private function createStoreDetails(int $storeId, Carbon $now): void
    {
        DB::table('store_details')->updateOrInsert(
            ['store_id' => $storeId],
            [
                'owner_name' => 'Ahmad Market Akbar',
                'owner_phone' => '081234567890',
                'description' => 'Market Akbar adalah marketplace terlengkap.',
                'shipping_policy' => 'Pesanan diproses dalam 1-2 hari kerja.',
                'return_policy' => 'Pengembalian diterima dalam 7 hari.',
                'open_days' => 'senin-minggu',
                'open_time' => '00:00:00',
                'close_time' => '23:59:59',
                'whatsapp_url' => 'https://wa.me/6281234567890',
                'instagram_url' => 'https://instagram.com/marketakbar',
                'tiktok_url' => 'https://tiktok.com/@marketakbar',
                'website_url' => 'https://marketakbar.co.id',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private function createShippingSettings(int $storeId, Carbon $now): void
    {
        DB::table('shipping_settings')->updateOrInsert(
            ['store_id' => $storeId],
            [
                'store_latitude' => -6.15000000,
                'store_longitude' => 106.83000000,
                'free_shipping_max_distance' => 10,
                'default_flat_rate' => 12000,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private function getCategoryIds(): array
    {
        return DB::table('categories')
            ->whereIn('slug', ['makanan', 'minuman', 'elektronik', 'dekorasi-rumah'])
            ->pluck('id', 'slug')
            ->all();
    }

    private function createProducts(int $storeId, array $categoryIds, Carbon $now, string $adminId): array
    {
        $items = [
            ['name' => 'Nasi Goreng Spesial', 'slug' => 'nasi-goreng-spesial', 'price' => 35000, 'category' => 'makanan', 'stock' => 150],
            ['name' => 'Sate Ayam Madura', 'slug' => 'sate-ayam-madura', 'price' => 45000, 'category' => 'makanan', 'stock' => 80],
            ['name' => 'Bakso Urat Special', 'slug' => 'bakso-urat-special', 'price' => 38000, 'category' => 'makanan', 'stock' => 120],
            ['name' => 'Kopi Robusta Premium', 'slug' => 'kopi-robusta-premium', 'price' => 85000, 'category' => 'minuman', 'stock' => 200],
            ['name' => 'Teh Hijau Organic', 'slug' => 'teh-hijau-organic', 'price' => 65000, 'category' => 'minuman', 'stock' => 180],
            ['name' => 'Speaker Bluetooth Portable', 'slug' => 'speaker-bluetooth-portable', 'price' => 250000, 'category' => 'elektronik', 'stock' => 50],
            ['name' => 'Headphone Wireless Pro', 'slug' => 'headphone-wireless-pro', 'price' => 450000, 'category' => 'elektronik', 'stock' => 35],
            ['name' => 'Lampu Meja LED Minimalis', 'slug' => 'lampu-meja-led-minimalis', 'price' => 175000, 'category' => 'dekorasi-rumah', 'stock' => 90],
            ['name' => 'Sarung Bantal Velvet', 'slug' => 'sarung-bantal-velvet', 'price' => 125000, 'category' => 'dekorasi-rumah', 'stock' => 100],
            ['name' => 'Nasi Kuning Komplit', 'slug' => 'nasi-kuning-komplit', 'price' => 30000, 'category' => 'makanan', 'stock' => 200],
            ['name' => 'Jus Jeruk Segar', 'slug' => 'jus-jeruk-segar', 'price' => 25000, 'category' => 'minuman', 'stock' => 150],
            ['name' => 'Charger Fast Charging 65W', 'slug' => 'charger-fast-charging-65w', 'price' => 199000, 'category' => 'elektronik', 'stock' => 75],
            ['name' => 'Selimut Bulu Premium', 'slug' => 'selimut-bulu-premium', 'price' => 289000, 'category' => 'dekorasi-rumah', 'stock' => 45],
            ['name' => 'Mie Ayam Jamur', 'slug' => 'mie-ayam-jamur', 'price' => 32000, 'category' => 'makanan', 'stock' => 160],
            ['name' => 'Kopi Susu Kekinian', 'slug' => 'kopi-susu-kekinian', 'price' => 35000, 'category' => 'minuman', 'stock' => 220],
            ['name' => 'Kabel Data Type-C 2m', 'slug' => 'kabel-data-type-c-2m', 'price' => 49000, 'category' => 'elektronik', 'stock' => 300],
            ['name' => 'Lampu Gantung Industrial', 'slug' => 'lampu-gantung-industrial', 'price' => 350000, 'category' => 'dekorasi-rumah', 'stock' => 25],
            ['name' => 'Ayam Geprek Sambal Matah', 'slug' => 'ayam-geprek-sambal-matah', 'price' => 38000, 'category' => 'makanan', 'stock' => 130],
            ['name' => 'Smoothie Bowl Berry', 'slug' => 'smoothie-bowl-berry', 'price' => 42000, 'category' => 'minuman', 'stock' => 85],
            ['name' => 'Powerbank 20000mAh', 'slug' => 'powerbank-20000mah', 'price' => 299000, 'category' => 'elektronik', 'stock' => 60],
        ];

        $created = [];
        foreach ($items as $index => $item) {
            $categoryId = $categoryIds[$item['category']] ?? null;
            $slug = 'mab-' . $item['slug'];
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
                    'brand' => 'Market Akbar',
                    'thumbnail' => 'https://picsum.photos/seed/' . $item['slug'] . '/800/800',
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

    private function createVariants(array $products, Carbon $now): array
    {
        $variants = [];
        foreach ($products as $product) {
            $variantId = (int) DB::table('product_variants')->insertGetId([
                'product_id' => $product['id'],
                'store_id' => $product['store_id'],
                'name' => 'Default',
                'sku' => 'MA-' . strtoupper(Str::random(8)),
                'price' => $product['price'],
                'stock' => $product['stock'],
                'is_default' => true,
                'created_at' => $product['created_at'],
                'updated_at' => $product['created_at'],
            ]);

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

    private function createStockMovements(array $variants, int $storeId, Carbon $now, string $adminId): void
    {
        foreach ($variants as $index => $variant) {
            $occurredAt = $variant['created_at'];
            DB::table('stock_movements')->insert([
                'store_id' => $storeId,
                'product_id' => $variant['product_id'],
                'variant_id' => $variant['id'],
                'order_id' => null,
                'order_item_id' => null,
                'movement_key' => 'stock-akbar-' . ($index + 1),
                'type' => 'opening_balance',
                'quantity_delta' => $variant['stock'],
                'balance_after' => $variant['stock'],
                'reference_type' => 'seed',
                'reference_id' => null,
                'notes' => 'Stock awal dari seeder Market Akbar',
                'occurred_at' => $occurredAt,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
                'deleted_at' => null,
            ]);
        }
    }

    private function createBuyers(Carbon $now, string $password, string $adminId, $roleIds): array
    {
        $buyersData = [
            ['id' => self::MARKET_AKBAR_BUYER_1, 'name' => 'Rina Susanti', 'email' => 'rina.susanti@gmail.com'],
            ['id' => self::MARKET_AKBAR_BUYER_2, 'name' => 'Dedi Kurniawan', 'email' => 'dedi.kurniawan@gmail.com'],
            ['id' => self::MARKET_AKBAR_BUYER_3, 'name' => 'Maya Putri', 'email' => 'maya.putri@gmail.com'],
            ['id' => self::MARKET_AKBAR_BUYER_4, 'name' => 'Andi Wijaya', 'email' => 'andi.wijaya@gmail.com'],
            ['id' => self::MARKET_AKBAR_BUYER_5, 'name' => 'Sinta Dewi', 'email' => 'sinta.dewi@gmail.com'],
        ];

        $created = [];
        foreach ($buyersData as $buyer) {
            DB::table('users')->updateOrInsert(
                ['id' => $buyer['id']],
                [
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
                ]
            );

            if (isset($roleIds['buyer'])) {
                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $buyer['id'], 'role_id' => $roleIds['buyer']],
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

    private function createOrders(array $buyers, int $storeId, array $variants, Carbon $now, string $adminId): array
    {
        $orders = [];
        $statuses = ['completed', 'completed', 'completed', 'shipped', 'processing', 'pending', 'cancelled'];
        $orderNumber = 1;

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

                $orderNumberStr = 'MAB-' . $createdAt->format('Ymd') . '-' . str_pad((string) $orderNumber, 4, '0', STR_PAD_LEFT);
                $orderNumber++;

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

                DB::table('order_items')->insert([
                    'sub_order_id' => $subOrderId,
                    'product_id' => $variant['product_id'],
                    'variant_id' => $variant['id'],
                    'product_name' => DB::table('products')->where('id', $variant['product_id'])->value('name'),
                    'sku' => 'MA-' . strtoupper(Str::random(8)),
                    'price' => $variant['price'],
                    'quantity' => $quantity,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if ($status !== 'cancelled') {
                    DB::table('stock_movements')->insert([
                        'store_id' => $storeId,
                        'product_id' => $variant['product_id'],
                        'variant_id' => $variant['id'],
                        'order_id' => $orderId,
                        'order_item_id' => null,
                        'movement_key' => 'order-' . $orderId,
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
                    ]);
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

    private function createPayments(array $orders, Carbon $now): void
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
                    'transaction_id' => 'MAB-PAY-' . strtoupper(Str::random(8)),
                    'payment_method' => 'bank_transfer',
                    'amount' => $order['total_amount'],
                    'status' => 'settlement',
                    'payload' => json_encode(['seeded' => true, 'market_akbar' => true], JSON_UNESCAPED_UNICODE),
                    'created_at' => $paidAt,
                    'updated_at' => $paidAt,
                ]
            );
        }
    }

    private function createReviews(array $orders, array $products, array $buyers, Carbon $now, string $adminId): void
    {
        $reviewTexts = [
            'Produk sangat berkualitas! Pengiriman juga cepat.',
            'Barang sesuai deskripsi, kemasan rapi. Puas!',
            'Harga terjangkau untuk kualitas sebaik ini.',
            'Pelayanan seller sangat baik, responsif.',
            'Produk original dan berfungsi dengan baik.',
            'Pengiriman aman, bubble wrap tebal.',
            'Sesuai ekspektasi, akan beli lagi.',
            'Kualitas premium, recommended seller!',
        ];
        $ratings = [5, 5, 5, 4, 5, 4, 5, 4];

        foreach ($orders as $index => $order) {
            if ($order['status'] !== 'completed') {
                continue;
            }

            $orderItem = DB::table('order_items')
                ->where('sub_order_id', $order['sub_order_id'])
                ->where('product_id', $products[$index % count($products)]['id'])
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
                    'review' => $reviewTexts[$index % count($reviewTexts)],
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

    private function createFinanceData(int $storeId, array $orders, array $buyers, Carbon $now, string $adminId): void
    {
        $incomeCount = 0;
        $expenseCount = 0;
        $receivableCount = 0;
        $payableCount = 0;

        foreach ($orders as $index => $order) {
            $createdAt = $order['created_at'];
            $occurredAt = $createdAt->copy()->addHour();

            if ($order['status'] === 'completed') {
                DB::table('financial_transactions')->insert([
                    'store_id' => $storeId,
                    'order_id' => $order['id'],
                    'user_id' => $order['user_id'],
                    'reference_number' => 'FIN-AKBAR-INC-' . str_pad((string) (++$incomeCount), 4, '0', STR_PAD_LEFT),
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
                    'metadata' => json_encode(['seeded' => true, 'market_akbar' => true], JSON_UNESCAPED_UNICODE),
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $occurredAt,
                    'updated_at' => $occurredAt,
                    'deleted_at' => null,
                ]);
            }

            if ($index % 4 === 0) {
                $expenseAmount = rand(50000, 500000);
                $expenseAt = $createdAt->copy()->subDay();
                DB::table('financial_transactions')->insert([
                    'store_id' => $storeId,
                    'order_id' => null,
                    'user_id' => null,
                    'reference_number' => 'FIN-AKBAR-EXP-' . str_pad((string) (++$expenseCount), 4, '0', STR_PAD_LEFT),
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
                    'metadata' => json_encode(['seeded' => true, 'market_akbar' => true], JSON_UNESCAPED_UNICODE),
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $expenseAt,
                    'updated_at' => $expenseAt,
                    'deleted_at' => null,
                ]);
            }

            if ($index % 5 === 0) {
                $receivableAmount = rand(100000, 1000000);
                $dueDate = $now->copy()->addDays(rand(-15, 30));
                DB::table('financial_transactions')->insert([
                    'store_id' => $storeId,
                    'order_id' => null,
                    'user_id' => $order['user_id'],
                    'reference_number' => 'FIN-AKBAR-RCV-' . str_pad((string) (++$receivableCount), 4, '0', STR_PAD_LEFT),
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
                    'metadata' => json_encode(['seeded' => true, 'market_akbar' => true], JSON_UNESCAPED_UNICODE),
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'deleted_at' => null,
                ]);
            }

            if ($index % 6 === 0) {
                $payableAmount = rand(100000, 800000);
                $dueDate = $now->copy()->addDays(rand(-10, 20));
                DB::table('financial_transactions')->insert([
                    'store_id' => $storeId,
                    'order_id' => null,
                    'user_id' => null,
                    'reference_number' => 'FIN-AKBAR-PAY-' . str_pad((string) (++$payableCount), 4, '0', STR_PAD_LEFT),
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
                    'metadata' => json_encode(['seeded' => true, 'market_akbar' => true], JSON_UNESCAPED_UNICODE),
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'deleted_at' => null,
                ]);
            }
        }
    }

    private function createSettlements(int $storeId, array $orders, Carbon $now, string $adminId): void
    {
        $count = 0;
        foreach ($orders as $order) {
            if ($order['status'] !== 'completed') {
                continue;
            }

            $adminFee = round($order['item_total'] * 0.05, 2);
            $netAmount = $order['item_total'] - $adminFee;

            DB::table('seller_settlements')->insert([
                'store_id' => $storeId,
                'order_id' => $order['id'],
                'sub_order_id' => $order['sub_order_id'],
                'settlement_number' => 'STL-AKBAR-' . str_pad((string) (++$count), 4, '0', STR_PAD_LEFT),
                'gross_amount' => $order['item_total'],
                'admin_fee' => $adminFee,
                'shipping_fee' => 0,
                'net_amount' => $netAmount,
                'status' => $count % 3 === 0 ? 'pending' : 'settled',
                'settled_at' => $count % 3 !== 0 ? $order['created_at']->copy()->addDay() : null,
                'notes' => null,
                'metadata' => json_encode(['seeded' => true, 'market_akbar' => true], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $order['created_at'],
                'updated_at' => $order['created_at'],
                'deleted_at' => null,
            ]);
        }
    }

    private function createWithdrawals(int $storeId, string $sellerId, Carbon $now, string $adminId): void
    {
        $withdrawals = [
            ['amount' => 500000, 'method' => 'bank_transfer', 'status' => 'completed', 'daysAgo' => 25],
            ['amount' => 1000000, 'method' => 'bank_transfer', 'status' => 'completed', 'daysAgo' => 18],
            ['amount' => 750000, 'method' => 'e_wallet', 'status' => 'approved', 'daysAgo' => 10],
            ['amount' => 300000, 'method' => 'bank_transfer', 'status' => 'pending', 'daysAgo' => 3],
        ];

        foreach ($withdrawals as $index => $withdrawal) {
            $createdAt = $now->copy()->subDays($withdrawal['daysAgo']);
            $bankDetails = $withdrawal['method'] === 'bank_transfer'
                ? json_encode(['bank_name' => 'Bank BCA', 'account_number' => '1234567890', 'account_name' => 'Ahmad Market Akbar'], JSON_UNESCAPED_UNICODE)
                : null;

            DB::table('seller_withdrawals')->insert([
                'store_id' => $storeId,
                'user_id' => $sellerId,
                'withdrawal_number' => 'WD-AKBAR-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
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
            ]);
        }
    }

    private function createSchedules(string $sellerId, int $storeId, Carbon $now, string $adminId): void
    {
        $schedules = [
            ['title' => 'Cek Stok Gudang', 'type' => 'task', 'priority' => 'high', 'date' => $now->toDateString(), 'start_time' => '09:00:00', 'end_time' => '11:00:00'],
            ['title' => 'Meeting Supplier', 'type' => 'meeting', 'priority' => 'high', 'date' => $now->copy()->addDay()->toDateString(), 'start_time' => '14:00:00', 'end_time' => '15:30:00'],
            ['title' => 'Kirim Pesanan', 'type' => 'task', 'priority' => 'normal', 'date' => $now->copy()->addDay()->toDateString(), 'start_time' => '10:00:00', 'end_time' => '12:00:00'],
            ['title' => 'Restock Kopi Robusta', 'type' => 'task', 'priority' => 'normal', 'date' => $now->copy()->addDays(2)->toDateString(), 'start_time' => '08:00:00', 'end_time' => '10:00:00'],
            ['title' => 'Evaluasi Penjualan Mingguan', 'type' => 'task', 'priority' => 'high', 'date' => $now->copy()->addDays(3)->toDateString(), 'start_time' => '13:00:00', 'end_time' => '14:00:00'],
            ['title' => 'Promo Flash Sale', 'type' => 'reminder', 'priority' => 'urgent', 'date' => $now->copy()->addDays(5)->toDateString(), 'start_time' => '00:00:00', 'end_time' => '23:59:00'],
            ['title' => 'Buka Toko', 'type' => 'task', 'priority' => 'low', 'date' => $now->toDateString(), 'start_time' => '08:00:00', 'end_time' => '08:30:00', 'is_all_day' => false],
            ['title' => 'Rekap Penjualan Harian', 'type' => 'task', 'priority' => 'normal', 'date' => $now->copy()->subDay()->toDateString(), 'start_time' => '17:00:00', 'end_time' => '18:00:00', 'is_completed' => true],
        ];

        foreach ($schedules as $schedule) {
            $createdAt = $now->copy()->subDays(rand(0, 5));
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
                'is_all_day' => $schedule['is_all_day'] ?? false,
                'is_completed' => $schedule['is_completed'] ?? false,
                'completed_at' => ($schedule['is_completed'] ?? false) ? $createdAt->copy()->addHour() : null,
                'metadata' => json_encode(['seeded' => true, 'market_akbar' => true], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_by' => $sellerId,
                'updated_by' => $sellerId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
        }
    }

    private function createAdminFeeConfigs(Carbon $now, string $adminId): void
    {
        $configs = [
            ['name' => 'Default Marketplace Fee', 'code' => 'default', 'percentage' => 5.00, 'fixed_amount' => 0, 'category_slug' => null],
            ['name' => 'Fee Makanan & Minuman', 'code' => 'food-beverage', 'percentage' => 4.00, 'fixed_amount' => 500, 'category_slug' => 'makanan'],
            ['name' => 'Fee Elektronik', 'code' => 'electronics', 'percentage' => 6.00, 'fixed_amount' => 0, 'category_slug' => 'elektronik'],
            ['name' => 'Fee Dekorasi Rumah', 'code' => 'home-decor', 'percentage' => 5.50, 'fixed_amount' => 200, 'category_slug' => 'dekorasi-rumah'],
        ];

        foreach ($configs as $config) {
            $categoryId = $config['category_slug']
                ? DB::table('categories')->where('slug', $config['category_slug'])->value('id')
                : null;

            DB::table('admin_fee_configs')->updateOrInsert(
                ['code' => $config['code']],
                [
                    'category_id' => $categoryId,
                    'name' => $config['name'],
                    'percentage' => $config['percentage'],
                    'fixed_amount' => $config['fixed_amount'],
                    'min_fee' => 0,
                    'max_fee' => 0,
                    'is_active' => true,
                    'description' => 'Konfigurasi fee untuk ' . $config['name'],
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
        }
    }

    private function createPromotions(int $storeId, array $products, Carbon $now, string $adminId): void
    {
        $promotions = [
            [
                'name' => 'Flash Sale Nasi Goreng',
                'click_action' => 'product',
                'target_product_index' => 0,
            ],
            [
                'name' => 'Promo Kopi Terbaik',
                'click_action' => 'product',
                'target_product_index' => 3,
            ],
            [
                'name' => 'Diskon Speaker',
                'click_action' => 'product',
                'target_product_index' => 5,
            ],
        ];

        foreach ($promotions as $promo) {
            $product = $products[$promo['target_product_index'] % count($products)];
            $createdAt = $now->copy()->subDays(5);

            DB::table('promotions')->updateOrInsert(
                ['name' => $promo['name']],
                [
                    'store_id' => $storeId,
                    'promotion_payment_id' => null,
                    'image_url' => 'https://picsum.photos/seed/promo-' . Str::slug($promo['name']) . '/800/400',
                    'mobile_image_url' => null,
                    'click_action' => $promo['click_action'],
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

    private function createShowcases(int $storeId, array $products, Carbon $now, string $adminId): void
    {
        $showcases = [
            ['name' => 'Produk Terlaris', 'slug' => 'produk-terlaris-market-akbar', 'description' => 'Koleksi produk terlaris di Market Akbar'],
            ['name' => 'Promo Hari Ini', 'slug' => 'promo-hari-ini-market-akbar', 'description' => 'Promo spesial hari ini'],
            ['name' => 'Produk Baru', 'slug' => 'produk-baru-market-akbar', 'description' => 'Produk terbaru kami'],
        ];

        foreach ($showcases as $index => $showcase) {
            $createdAt = $now->copy()->subDays(10 + $index);
            $existingShowcase = DB::table('showcases')->where('slug', $showcase['slug'])->where('store_id', $storeId)->whereNull('deleted_at')->first(['id']);
            if ($existingShowcase) {
                $showcaseId = (int) $existingShowcase->id;
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
                DB::table('showcase_products')->insert([
                    'showcase_id' => $showcaseId,
                    'product_id' => $product['id'],
                    'sort_order' => $i + 1,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }

    private function createAdminNotifications(string $adminId, array $buyers, int $storeId, Carbon $now): void
    {
        $notifications = [
            ['module' => 'order', 'type' => 'new_order', 'title' => 'Pesanan baru dari Market Akbar', 'message' => 'Ada pesanan baru yang perlu diproses.'],
            ['module' => 'order', 'type' => 'payment_received', 'title' => 'Pembayaran diterima', 'message' => 'Pembayaran untuk pesanan Market Akbar telah diterima.'],
            ['module' => 'seller', 'type' => 'store_approved', 'title' => 'Toko Market Akbar disetujui', 'message' => 'Toko Market Akbar telah disetujui dan aktif.'],
            ['module' => 'product', 'type' => 'product_published', 'title' => 'Produk dipublikasikan', 'message' => '20 produk Market Akbar telah dipublikasikan.'],
            ['module' => 'finance', 'type' => 'settlement_ready', 'title' => 'Settlement siap', 'message' => 'Settlement untuk Market Akbar telah diproses.'],
            ['module' => 'review', 'type' => 'new_review', 'title' => 'Review baru', 'message' => 'Ada review baru untuk produk Market Akbar.'],
        ];

        foreach ($notifications as $index => $notification) {
            $createdAt = $now->copy()->subHours(rand(1, 48));
            DB::table('admin_notifications')->insert([
                'user_id' => $adminId,
                'actor_id' => $buyers[0]['id'] ?? null,
                'store_id' => $storeId,
                'module' => $notification['module'],
                'type' => $notification['type'],
                'title' => $notification['title'],
                'message' => $notification['message'],
                'reference_type' => 'market_akbar_seed',
                'reference_id' => (string) ($index + 1),
                'url' => '/admin/dashboard',
                'meta' => json_encode(['seeded' => true, 'market_akbar' => true], JSON_UNESCAPED_UNICODE),
                'read_at' => $index % 2 === 0 ? $createdAt->copy()->addMinutes(30) : null,
                'is_active' => true,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
        }
    }
}
