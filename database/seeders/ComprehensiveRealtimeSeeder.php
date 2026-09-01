<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ComprehensiveRealtimeSeeder extends Seeder
{
    private int $count = 100;

    public function run(): void
    {
        $this->count = max(100, (int) env('MARKETPLACE_REALTIME_COUNT', 100));
        $now = now();

        DB::transaction(function () use ($now): void {
            $this->seedReferenceVolume($now);
            $this->seedProductReviewsAndPayments($now);
            $this->seedPromotionPayments($now);
            $this->seedFinanceAndInstallments($now);
            $this->seedInventoryAndCosting($now);
            $this->seedShowcases($now);
            $this->seedSupport($now);
            $this->seedMissions($now);
            $this->seedCommunication($now);
            $this->seedAdminNotifications($now);
        });
    }

    private function seedReferenceVolume(CarbonInterface $now): void
    {
        $adminId = $this->adminId();

        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'like', 'testing_role_%')->delete();
            $missing = max(0, $this->count - DB::table('roles')->count());
            for ($i = 1; $i <= $missing; $i++) {
                DB::table('roles')->insert([
                    'name' => sprintf('testing_role_%03d', $i),
                    'description' => sprintf('Role testing volume %03d', $i),
                    'is_active' => false,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $this->time($now, $i, 31),
                    'updated_at' => $this->time($now, $i, 31),
                    'deleted_at' => null,
                ]);
            }
        }

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', 'like', 'testing.permission.%')->delete();
            $missing = max(0, $this->count - DB::table('permissions')->count());
            for ($i = 1; $i <= $missing; $i++) {
                DB::table('permissions')->insert([
                    'name' => sprintf('testing.permission.%03d', $i),
                    'description' => sprintf('Permission testing volume %03d', $i),
                    'is_active' => false,
                    'created_at' => $this->time($now, $i, 29),
                    'updated_at' => $this->time($now, $i, 29),
                ]);
            }
        }

        if (Schema::hasTable('role_permissions') && DB::table('role_permissions')->count() < $this->count) {
            $roles = DB::table('roles')->where('name', 'like', 'testing_role_%')->pluck('id')->values();
            $permissions = DB::table('permissions')->where('name', 'like', 'testing.permission.%')->pluck('id')->values();
            $needed = $this->count - DB::table('role_permissions')->count();
            for ($i = 0; $i < $needed && $roles->isNotEmpty() && $permissions->isNotEmpty(); $i++) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $roles[$i % $roles->count()],
                    'permission_id' => $permissions[$i % $permissions->count()],
                    'created_at' => $this->time($now, $i + 1, 23),
                    'updated_at' => $this->time($now, $i + 1, 23),
                ]);
            }
        }

        if (Schema::hasTable('catalog_groups')) {
            DB::table('catalog_groups')->where('slug', 'like', 'realtime-group-%')->delete();
            $missing = max(0, $this->count - DB::table('catalog_groups')->count());
            for ($i = 1; $i <= $missing; $i++) {
                DB::table('catalog_groups')->insert([
                    'name' => sprintf('Realtime Group %03d', $i),
                    'slug' => sprintf('realtime-group-%03d', $i),
                    'is_active' => false,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $this->time($now, $i, 37),
                    'updated_at' => $this->time($now, $i, 37),
                    'deleted_at' => null,
                ]);
            }
        }

        if (Schema::hasTable('product_attributes')) {
            DB::table('product_attributes')->where('slug', 'like', 'realtime-attribute-%')->delete();
            $missing = max(0, $this->count - DB::table('product_attributes')->count());
            for ($i = 1; $i <= $missing; $i++) {
                DB::table('product_attributes')->insert([
                    'name' => sprintf('Realtime Attribute %03d', $i),
                    'slug' => sprintf('realtime-attribute-%03d', $i),
                    'type' => $i % 3 === 0 ? 'text' : 'select',
                    'created_at' => $this->time($now, $i, 41),
                    'updated_at' => $this->time($now, $i, 41),
                ]);
            }
        }
    }

    private function seedProductReviewsAndPayments(CarbonInterface $now): void
    {
        if (! Schema::hasTable('product_reviews') || ! Schema::hasTable('order_items')) {
            return;
        }

        $adminId = $this->adminId();
        $rows = DB::table('order_items')
            ->join('sub_orders', 'sub_orders.id', '=', 'order_items.sub_order_id')
            ->join('orders', 'orders.id', '=', 'sub_orders.order_id')
            ->where('orders.order_number', 'like', 'FKR-%')
            ->select(
                'order_items.id as order_item_id',
                'order_items.product_id',
                'orders.id as order_id',
                'orders.order_number',
                'orders.user_id',
                'orders.total_amount'
            )
            ->orderBy('orders.id')
            ->limit($this->count)
            ->get();

        $ratings = [5, 5, 5, 4, 5, 4, 5, 4, 3, 2];
        $reviews = [
            'Produk sesuai foto dan deskripsi. Kualitas sangat baik dan pengiriman cepat.',
            'Barang diterima dengan aman, kemasan rapi, dan fungsi produk sesuai harapan.',
            'Kualitas bagus untuk harganya. Seller responsif dan proses pesanan cepat.',
            'Produk bekerja dengan baik dan kondisi saat diterima sangat bagus.',
            'Sangat puas dengan pembelian ini. Produk akan saya rekomendasikan.',
            'Barang sesuai pesanan, warna dan ukuran cocok, kemasan juga aman.',
            'Pengiriman tepat waktu dan produk tidak mengalami kerusakan.',
            'Secara keseluruhan bagus, kualitas produk sesuai dengan harga.',
            'Produk cukup baik, tetapi kemasan masih bisa dibuat lebih kuat.',
            'Barang berfungsi, namun waktu pengiriman lebih lama dari perkiraan.',
        ];

        foreach ($rows as $index => $row) {
            $position = $index + 1;
            $createdAt = $this->time($now, $position, 47);
            $receivedAt = $createdAt->copy()->subHours(2);
            $rating = $ratings[$index % count($ratings)];

            DB::table('orders')->where('id', $row->order_id)->update([
                'status' => 'completed',
                'payment_status' => 'paid',
                'received_at' => $receivedAt,
                'updated_at' => $createdAt,
            ]);
            DB::table('sub_orders')->where('order_id', $row->order_id)->update([
                'status' => 'completed',
                'updated_at' => $createdAt,
            ]);

            if (Schema::hasTable('payments')) {
                DB::table('payments')->updateOrInsert(
                    ['order_number' => $row->order_number],
                    [
                        'transaction_id' => sprintf('RT-TRX-%06d', $position),
                        'payment_method' => $position % 4 === 0 ? 'qris' : 'bank_transfer',
                        'amount' => $row->total_amount,
                        'status' => 'settlement',
                        'payload' => json_encode(['seeded' => true, 'realtime' => true, 'sequence' => $position], JSON_UNESCAPED_UNICODE),
                        'created_at' => $receivedAt->copy()->subHours(6),
                        'updated_at' => $receivedAt->copy()->subHours(6),
                    ]
                );
            }

            DB::table('product_reviews')->updateOrInsert(
                ['order_item_id' => $row->order_item_id],
                [
                    'product_id' => $row->product_id,
                    'order_id' => $row->order_id,
                    'user_id' => $row->user_id,
                    'rating' => $rating,
                    'review' => $reviews[$index % count($reviews)],
                    'media' => $position % 7 === 0
                        ? json_encode([sprintf('https://picsum.photos/seed/review-%03d/900/900', $position)], JSON_UNESCAPED_UNICODE)
                        : json_encode([], JSON_UNESCAPED_UNICODE),
                    'is_active' => true,
                    'created_by' => $row->user_id,
                    'updated_by' => $adminId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'deleted_at' => null,
                ]
            );
        }
    }

    private function seedPromotionPayments(CarbonInterface $now): void
    {
        if (! Schema::hasTable('promotion_payments')) {
            return;
        }

        DB::table('promotion_payments')->where('payment_number', 'like', 'RT-PROMO-PAY-%')->delete();
        $stores = $this->stores();
        $adminId = $this->adminId();
        if ($stores->isEmpty() || ! $adminId) {
            return;
        }

        for ($i = 1; $i <= $this->count; $i++) {
            $store = $stores[($i - 1) % $stores->count()];
            $createdAt = $this->time($now, $i, 53);
            $status = $i % 10 === 0 ? 'rejected' : ($i % 4 === 0 ? 'pending' : 'approved');
            DB::table('promotion_payments')->insert([
                'store_id' => $store->id,
                'user_id' => $store->user_id,
                'payment_number' => sprintf('RT-PROMO-PAY-%04d', $i),
                'package_name' => $i % 2 === 0 ? 'Paket Promosi 30 Hari' : 'Paket Promosi 7 Hari',
                'amount' => $i % 2 === 0 ? 450000 : 150000,
                'payment_method' => $i % 5 === 0 ? 'qris' : 'bank_transfer',
                'proof_url' => sprintf('/storage/promotion-payments/realtime-%03d.jpg', $i),
                'status' => $status,
                'rejection_reason' => $status === 'rejected' ? 'Bukti pembayaran perlu diunggah ulang.' : null,
                'paid_at' => $createdAt->copy()->addMinutes(2),
                'reviewed_at' => $status === 'pending' ? null : $createdAt->copy()->addMinutes(20),
                'reviewed_by' => $status === 'pending' ? null : $adminId,
                'is_active' => true,
                'created_by' => $store->user_id,
                'updated_by' => $status === 'pending' ? $store->user_id : $adminId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
        }
    }

    private function seedFinanceAndInstallments(CarbonInterface $now): void
    {
        if (! Schema::hasTable('financial_transactions')) {
            return;
        }

        DB::table('financial_transactions')->where('reference_number', 'like', 'RT-FIN-%')->delete();
        $stores = $this->stores();
        $buyers = $this->buyers();
        $orders = DB::table('orders')->where('order_number', 'like', 'FKR-%')->orderBy('id')->limit($this->count)->get(['id']);
        $adminId = $this->adminId();
        if ($stores->isEmpty() || ! $adminId) {
            return;
        }

        for ($i = 1; $i <= $this->count; $i++) {
            $store = $stores[($i - 1) % $stores->count()];
            $buyer = $buyers->isEmpty() ? null : $buyers[($i - 1) % $buyers->count()];
            $order = $orders->isEmpty() ? null : $orders[($i - 1) % $orders->count()];
            $type = ['income', 'expense', 'receivable', 'payable'][($i - 1) % 4];
            $amount = 150000 + (($i * 137000) % 4850000);
            $occurredAt = $this->time($now, $i, 61);
            $isDebt = in_array($type, ['receivable', 'payable'], true);
            $paidAmount = $isDebt ? round($amount * 0.5, 2) : $amount;
            $status = $isDebt ? 'partial' : 'paid';
            $transactionId = (int) DB::table('financial_transactions')->insertGetId([
                'store_id' => $store->id,
                'order_id' => $type === 'income' ? $order?->id : null,
                'user_id' => in_array($type, ['income', 'receivable'], true) ? $buyer?->id : null,
                'reference_number' => sprintf('RT-FIN-%04d', $i),
                'type' => $type,
                'title' => sprintf('%s realtime %03d', ucfirst($type), $i),
                'description' => 'Data transaksi testing dengan timeline relatif terhadap waktu seeder dijalankan.',
                'amount' => $amount,
                'paid_amount' => $paidAmount,
                'status' => $status,
                'due_date' => $isDebt ? $now->copy()->addDays(7 + ($i % 28))->toDateString() : null,
                'occurred_at' => $occurredAt,
                'settled_at' => $status === 'paid' ? $occurredAt->copy()->addMinutes(30) : null,
                'is_active' => true,
                'metadata' => json_encode(['seeded' => true, 'realtime' => true, 'sequence' => $i], JSON_UNESCAPED_UNICODE),
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
                'deleted_at' => null,
            ]);

            if ($isDebt && Schema::hasTable('financial_payment_histories')) {
                $first = round($amount * 0.25, 2);
                $second = round($amount * 0.25, 2);
                DB::table('financial_payment_histories')->insert([
                    [
                        'financial_transaction_id' => $transactionId,
                        'store_id' => $store->id,
                        'recorded_by' => $adminId,
                        'amount' => $first,
                        'balance_before' => $amount,
                        'balance_after' => $amount - $first,
                        'payment_method' => 'bank_transfer',
                        'reference_number' => sprintf('RT-INSTALLMENT-%04d-A', $i),
                        'notes' => 'Cicilan pertama testing realtime.',
                        'paid_at' => $occurredAt->copy()->addHours(6),
                        'created_at' => $occurredAt->copy()->addHours(6),
                        'updated_at' => $occurredAt->copy()->addHours(6),
                    ],
                    [
                        'financial_transaction_id' => $transactionId,
                        'store_id' => $store->id,
                        'recorded_by' => $adminId,
                        'amount' => $second,
                        'balance_before' => $amount - $first,
                        'balance_after' => $amount - $first - $second,
                        'payment_method' => $i % 3 === 0 ? 'cash' : 'bank_transfer',
                        'reference_number' => sprintf('RT-INSTALLMENT-%04d-B', $i),
                        'notes' => 'Cicilan kedua testing realtime.',
                        'paid_at' => $occurredAt->copy()->addHours(12),
                        'created_at' => $occurredAt->copy()->addHours(12),
                        'updated_at' => $occurredAt->copy()->addHours(12),
                    ],
                ]);
            }
        }
    }

    private function seedInventoryAndCosting(CarbonInterface $now): void
    {
        if (! Schema::hasTable('raw_materials')) {
            return;
        }

        DB::table('raw_materials')->where('code', 'like', 'RT-RM-%')->delete();
        $stores = $this->stores();
        $products = DB::table('products')
            ->where('slug', 'like', 'produk-faker-%')
            ->orderBy('id')
            ->limit($this->count)
            ->get(['id', 'store_id']);
        $adminId = $this->adminId();
        if ($stores->isEmpty() || $products->isEmpty()) {
            return;
        }

        $materialsByStore = [];
        for ($i = 1; $i <= $this->count; $i++) {
            $store = $stores[($i - 1) % $stores->count()];
            $createdAt = $this->time($now, $i, 43);
            $stock = 50 + (($i * 17) % 950);
            $unitCost = 500 + (($i * 725) % 25000);
            $materialId = (int) DB::table('raw_materials')->insertGetId([
                'store_id' => $store->id,
                'code' => sprintf('RT-RM-%04d', $i),
                'name' => sprintf('Bahan Baku Realtime %03d', $i),
                'unit' => $i % 4 === 0 ? 'kg' : ($i % 3 === 0 ? 'meter' : 'pcs'),
                'stock' => $stock,
                'minimum_stock' => 20 + ($i % 30),
                'average_cost' => $unitCost,
                'is_active' => $i % 17 !== 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
            $materialsByStore[$store->id] = ['id' => $materialId, 'cost' => $unitCost];

            if (Schema::hasTable('raw_material_stock_movements')) {
                DB::table('raw_material_stock_movements')->insert([
                    'store_id' => $store->id,
                    'raw_material_id' => $materialId,
                    'type' => 'restock',
                    'quantity_delta' => $stock,
                    'balance_after' => $stock,
                    'unit_cost' => $unitCost,
                    'total_cost' => $stock * $unitCost,
                    'reference_type' => 'realtime_seed',
                    'reference_number' => sprintf('RT-RM-MOVE-%04d', $i),
                    'notes' => 'Restock bahan baku testing realtime.',
                    'occurred_at' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }

        foreach ($products as $index => $product) {
            $i = $index + 1;
            $material = $materialsByStore[$product->store_id] ?? null;
            if (! $material) {
                continue;
            }
            $quantity = 1 + (($i % 4) * 0.25);
            $materialCost = round($quantity * $material['cost'], 4);
            $labor = 3000 + (($i * 500) % 12000);
            $overhead = 1500 + (($i * 250) % 7000);
            $other = ($i % 5) * 500;
            $hpp = $materialCost + $labor + $overhead + $other;
            $margin = 20 + ($i % 31);
            $selling = round($hpp * (1 + ($margin / 100)), 2);

            if (Schema::hasTable('product_materials')) {
                DB::table('product_materials')->updateOrInsert(
                    ['product_id' => $product->id, 'raw_material_id' => $material['id']],
                    [
                        'quantity' => $quantity,
                        'unit_cost' => $material['cost'],
                        'total_cost' => $materialCost,
                        'created_at' => $this->time($now, $i, 39),
                        'updated_at' => $this->time($now, $i, 39),
                    ]
                );
            }

            if (Schema::hasTable('product_costings')) {
                DB::table('product_costings')->updateOrInsert(
                    ['product_id' => $product->id],
                    [
                        'store_id' => $product->store_id,
                        'material_cost' => $materialCost,
                        'labor_cost' => $labor,
                        'overhead_cost' => $overhead,
                        'other_cost' => $other,
                        'hpp' => $hpp,
                        'margin_percent' => $margin,
                        'suggested_price' => $selling,
                        'selling_price' => $selling,
                        'created_at' => $this->time($now, $i, 39),
                        'updated_at' => $this->time($now, $i, 39),
                    ]
                );
            }
        }

        if (Schema::hasTable('stock_movements')) {
            DB::table('stock_movements')->where('movement_key', 'like', 'rt-stock-%')->delete();
            $variants = DB::table('product_variants')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->where('products.slug', 'like', 'produk-faker-%')
                ->select('product_variants.id', 'product_variants.product_id', 'product_variants.store_id', 'product_variants.stock')
                ->orderBy('product_variants.id')
                ->limit($this->count)
                ->get();
            foreach ($variants as $index => $variant) {
                $i = $index + 1;
                $occurredAt = $this->time($now, $i, 35);
                DB::table('stock_movements')->insert([
                    'store_id' => $variant->store_id,
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'order_id' => null,
                    'order_item_id' => null,
                    'movement_key' => sprintf('rt-stock-%04d', $i),
                    'type' => $i % 4 === 0 ? 'restock' : 'opening_balance',
                    'quantity_delta' => max(1, (int) $variant->stock),
                    'balance_after' => (int) $variant->stock,
                    'reference_type' => 'realtime_seed',
                    'reference_id' => sprintf('RT-STOCK-%04d', $i),
                    'notes' => 'Pergerakan stok testing realtime.',
                    'occurred_at' => $occurredAt,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $occurredAt,
                    'updated_at' => $occurredAt,
                    'deleted_at' => null,
                ]);
            }
        }
    }

    private function seedShowcases(CarbonInterface $now): void
    {
        if (! Schema::hasTable('showcases')) {
            return;
        }

        DB::table('showcases')->where('slug', 'like', 'etalase-realtime-%')->delete();
        $stores = $this->stores();
        $adminId = $this->adminId();
        if ($stores->isEmpty()) {
            return;
        }

        for ($i = 1; $i <= $this->count; $i++) {
            $store = $stores[($i - 1) % $stores->count()];
            $product = DB::table('products')->where('store_id', $store->id)->whereNull('deleted_at')->orderBy('id')->first(['id']);
            if (! $product) {
                continue;
            }
            $createdAt = $this->time($now, $i, 67);
            $showcaseId = (int) DB::table('showcases')->insertGetId([
                'store_id' => $store->id,
                'name' => sprintf('Etalase Realtime %03d', $i),
                'slug' => sprintf('etalase-realtime-%03d', $i),
                'description' => sprintf('Pengelompokan produk testing realtime nomor %03d.', $i),
                'sort_order' => $i,
                'is_active' => $i % 13 !== 0,
                'created_by' => $store->user_id,
                'updated_by' => $adminId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
            DB::table('showcase_products')->insert([
                'showcase_id' => $showcaseId,
                'product_id' => $product->id,
                'sort_order' => 1,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    private function seedSupport(CarbonInterface $now): void
    {
        if (! Schema::hasTable('support_tickets')) {
            return;
        }

        DB::table('support_tickets')->where('ticket_number', 'like', 'RT-TICKET-%')->delete();
        $buyers = $this->buyers();
        $stores = $this->stores();
        $orders = DB::table('orders')->where('order_number', 'like', 'FKR-%')->orderBy('id')->limit($this->count)->get(['id']);
        $adminId = $this->adminId();
        if ($buyers->isEmpty() || $stores->isEmpty()) {
            return;
        }

        for ($i = 1; $i <= $this->count; $i++) {
            $buyer = $buyers[($i - 1) % $buyers->count()];
            $store = $stores[($i - 1) % $stores->count()];
            $order = $orders->isEmpty() ? null : $orders[($i - 1) % $orders->count()];
            $createdAt = $this->time($now, $i, 71);
            $status = $i % 8 === 0 ? 'resolved' : ($i % 3 === 0 ? 'pending' : 'open');
            $ticketId = (int) DB::table('support_tickets')->insertGetId([
                'ticket_number' => sprintf('RT-TICKET-%05d', $i),
                'user_id' => $buyer->id,
                'store_id' => $store->id,
                'order_id' => $order?->id,
                'category' => ['order', 'payment', 'shipping', 'product'][($i - 1) % 4],
                'subject' => sprintf('Permintaan bantuan testing realtime %03d', $i),
                'description' => 'Tiket ini dibuat untuk menguji daftar, filter, detail, balasan, dan status bantuan.',
                'priority' => $i % 11 === 0 ? 'high' : 'normal',
                'status' => $status,
                'assigned_to' => $adminId,
                'last_replied_at' => $createdAt->copy()->addMinutes(12),
                'resolved_at' => $status === 'resolved' ? $createdAt->copy()->addHours(2) : null,
                'is_active' => true,
                'created_by' => $buyer->id,
                'updated_by' => $adminId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
            if (Schema::hasTable('support_ticket_messages')) {
                DB::table('support_ticket_messages')->insert([
                    'ticket_id' => $ticketId,
                    'user_id' => $buyer->id,
                    'message' => sprintf('Pesan bantuan realtime untuk tiket %03d.', $i),
                    'attachments' => null,
                    'is_internal' => false,
                    'read_at' => $i % 4 === 0 ? null : $createdAt->copy()->addMinutes(10),
                    'created_at' => $createdAt->copy()->addMinutes(1),
                    'updated_at' => $createdAt->copy()->addMinutes(1),
                    'deleted_at' => null,
                ]);
            }
        }
    }

    private function seedMissions(CarbonInterface $now): void
    {
        if (! Schema::hasTable('missions')) {
            return;
        }

        DB::table('missions')->where('code', 'like', 'RT-MISSION-%')->delete();
        if (Schema::hasTable('user_vouchers')) {
            DB::table('user_vouchers')->where('source_type', 'realtime_seed')->delete();
        }
        $buyers = $this->buyers();
        $vouchers = DB::table('vouchers')->where('is_active', true)->whereNull('deleted_at')->orderBy('id')->limit($this->count)->get(['id']);
        $adminId = $this->adminId();
        if ($buyers->isEmpty() || $vouchers->isEmpty()) {
            return;
        }

        for ($i = 1; $i <= $this->count; $i++) {
            $buyer = $buyers[($i - 1) % $buyers->count()];
            $voucher = $vouchers[($i - 1) % $vouchers->count()];
            $createdAt = $this->time($now, $i, 79);
            $missionId = (int) DB::table('missions')->insertGetId([
                'voucher_id' => $voucher->id,
                'name' => sprintf('Misi Realtime %03d', $i),
                'code' => sprintf('RT-MISSION-%04d', $i),
                'description' => 'Misi testing untuk menampilkan progres dan reward secara dinamis.',
                'event_type' => ['order_completed', 'review_submitted', 'wishlist_added', 'login'][($i - 1) % 4],
                'target_value' => 1 + ($i % 5),
                'conditions' => json_encode(['seeded' => true, 'realtime' => true], JSON_UNESCAPED_UNICODE),
                'starts_at' => $now->copy()->subDays(7),
                'ends_at' => $now->copy()->addDays(30),
                'is_active' => $i % 17 !== 0,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
            $completed = $i % 4 !== 0;
            if (Schema::hasTable('mission_user_progress')) {
                DB::table('mission_user_progress')->insert([
                    'mission_id' => $missionId,
                    'user_id' => $buyer->id,
                    'progress_value' => $completed ? 1 + ($i % 5) : ($i % 3),
                    'status' => $completed ? 'rewarded' : 'in_progress',
                    'completed_at' => $completed ? $createdAt->copy()->addMinutes(30) : null,
                    'rewarded_at' => $completed ? $createdAt->copy()->addMinutes(31) : null,
                    'reward_voucher_id' => $completed ? $voucher->id : null,
                    'metadata' => json_encode(['seeded' => true, 'realtime' => true], JSON_UNESCAPED_UNICODE),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
            if (Schema::hasTable('user_vouchers') && $completed) {
                DB::table('user_vouchers')->insert([
                    'user_id' => $buyer->id,
                    'voucher_id' => $voucher->id,
                    'source_type' => 'realtime_seed',
                    'source_id' => (string) $missionId,
                    'status' => 'available',
                    'claimed_at' => null,
                    'used_at' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }

    private function seedCommunication(CarbonInterface $now): void
    {
        if (! Schema::hasTable('conversations')) {
            return;
        }

        $conversationIds = DB::table('conversations')->where('subject', 'like', 'Realtime Conversation %')->pluck('id');
        if ($conversationIds->isNotEmpty()) {
            DB::table('conversations')->whereIn('id', $conversationIds)->delete();
        }
        $buyers = $this->buyers();
        $stores = $this->stores();
        $orders = DB::table('orders')->where('order_number', 'like', 'FKR-%')->orderBy('id')->limit($this->count)->get(['id']);
        if ($buyers->isEmpty() || $stores->isEmpty()) {
            return;
        }

        for ($i = 1; $i <= $this->count; $i++) {
            $buyer = $buyers[($i - 1) % $buyers->count()];
            $store = $stores[($i - 1) % $stores->count()];
            $order = $orders->isEmpty() ? null : $orders[($i - 1) % $orders->count()];
            $createdAt = $this->time($now, $i, 17);
            $conversationId = (int) DB::table('conversations')->insertGetId([
                'type' => 'order',
                'store_id' => $store->id,
                'order_id' => $order?->id,
                'subject' => sprintf('Realtime Conversation %03d', $i),
                'target_role' => null,
                'is_active' => true,
                'created_by' => $buyer->id,
                'updated_by' => $store->user_id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addMinutes(4),
                'deleted_at' => null,
            ]);
            DB::table('conversation_participants')->insert([
                [
                    'conversation_id' => $conversationId,
                    'user_id' => $buyer->id,
                    'last_read_at' => $createdAt->copy()->addMinutes(5),
                    'joined_at' => $createdAt,
                    'left_at' => null,
                    'is_muted' => false,
                ],
                [
                    'conversation_id' => $conversationId,
                    'user_id' => $store->user_id,
                    'last_read_at' => $createdAt->copy()->addMinutes(5),
                    'joined_at' => $createdAt,
                    'left_at' => null,
                    'is_muted' => $i % 23 === 0,
                ],
            ]);
            $buyerMessageId = (int) DB::table('chat_messages')->insertGetId([
                'conversation_id' => $conversationId,
                'sender_id' => $buyer->id,
                'message_type' => 'text',
                'message' => sprintf('Halo, saya ingin menanyakan status pesanan testing %03d.', $i),
                'attachments' => null,
                'edited_at' => null,
                'created_at' => $createdAt->copy()->addMinute(),
                'updated_at' => $createdAt->copy()->addMinute(),
                'deleted_at' => null,
            ]);
            DB::table('chat_messages')->insert([
                'conversation_id' => $conversationId,
                'sender_id' => $store->user_id,
                'message_type' => 'text',
                'message' => 'Pesanan sedang diproses dan status terbaru akan tampil pada detail pesanan.',
                'attachments' => null,
                'edited_at' => null,
                'created_at' => $createdAt->copy()->addMinutes(4),
                'updated_at' => $createdAt->copy()->addMinutes(4),
                'deleted_at' => null,
            ]);
            if (Schema::hasTable('chat_message_reads')) {
                DB::table('chat_message_reads')->insert([
                    'message_id' => $buyerMessageId,
                    'user_id' => $store->user_id,
                    'read_at' => $createdAt->copy()->addMinutes(3),
                ]);
            }
        }
    }

    private function seedAdminNotifications(CarbonInterface $now): void
    {
        if (! Schema::hasTable('admin_notifications')) {
            return;
        }

        DB::table('admin_notifications')->where('type', 'like', 'rt_seed_%')->delete();
        $adminId = $this->adminId();
        $buyers = $this->buyers();
        $stores = $this->stores();
        if (! $adminId || $stores->isEmpty()) {
            return;
        }
        $modules = ['order', 'seller', 'product', 'finance', 'review', 'support'];

        for ($i = 1; $i <= $this->count; $i++) {
            $store = $stores[($i - 1) % $stores->count()];
            $buyer = $buyers->isEmpty() ? null : $buyers[($i - 1) % $buyers->count()];
            $createdAt = $this->time($now, $i, 11);
            $module = $modules[($i - 1) % count($modules)];
            DB::table('admin_notifications')->insert([
                'user_id' => $adminId,
                'actor_id' => $buyer?->id,
                'store_id' => $store->id,
                'module' => $module,
                'type' => sprintf('rt_seed_%s_%03d', $module, $i),
                'title' => sprintf('Aktivitas realtime %03d', $i),
                'message' => sprintf('Data testing modul %s diperbarui pada timeline realtime seeder.', $module),
                'reference_type' => 'realtime_seed',
                'reference_id' => (string) $i,
                'url' => '/admin/dashboard',
                'meta' => json_encode(['seeded' => true, 'realtime' => true, 'sequence' => $i], JSON_UNESCAPED_UNICODE),
                'read_at' => $i % 3 === 0 ? $createdAt->copy()->addMinutes(2) : null,
                'is_active' => true,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
        }
    }

    private function stores()
    {
        return DB::table('stores')
            ->where('slug', 'like', 'toko-faker-%')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->limit($this->count)
            ->get(['id', 'user_id']);
    }

    private function buyers()
    {
        return DB::table('users')
            ->where('email', 'like', 'faker.buyer.%@marketku.test')
            ->whereNull('deleted_at')
            ->orderBy('created_at')
            ->limit($this->count)
            ->get(['id', 'name']);
    }

    private function adminId(): ?string
    {
        return DB::table('users')->where('id', SeederIds::SUPER_ADMIN)->value('id')
            ?? DB::table('users')->orderBy('created_at')->value('id');
    }

    private function time(CarbonInterface $now, int $index, int $step): CarbonInterface
    {
        return $now->copy()->subMinutes(1 + (($index * $step) % 43200));
    }
}
