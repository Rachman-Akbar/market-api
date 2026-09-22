<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Transaksi marketplace: pesanan, pembayaran, ulasan, mutasi stok produk,
 * settlement & penarikan dana seller, data keuangan toko operasional,
 * chat, tiket, jadwal, notifikasi admin, keranjang & wishlist.
 */
final class DemoCommerceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Demo: membuat pesanan, pembayaran, ulasan & finance...');

        $adminId = DemoIds::SUPER_ADMIN;
        $now = now()->toDateTimeString();

        $productsByStore = DemoState::get('products_by_store', []);
        $variantList = DemoState::get('variant_list', []);
        $variantsByStore = $this->indexVariants($variantList);

        $storeSlugs = array_keys($productsByStore);
        $buyers = DemoIds::BUYERS;

        $orders = [];
        $orderItems = [];
        $soldPerVariant = [];

        // Weighted status: completed dominan.
        $statusPool = array_merge(
            array_fill(0, 55, 'completed'),
            array_fill(0, 8, 'processing'),
            array_fill(0, 5, 'shipped'),
            array_fill(0, 7, 'pending'),
            array_fill(0, 6, 'cancelled'),
        );

        $buyerAddresses = DB::table('addresses')
            ->where('user_id', '!=', null)
            ->whereNotNull('user_id')
            ->get()
            ->keyBy('user_id');

        $totalOrders = random_int(70, 80);
        $orderSeq = 10000;

        for ($o = 0; $o < $totalOrders; $o++) {
            $buyerId = $buyers[array_rand($buyers)];
            $storeSlug = $storeSlugs[array_rand($storeSlugs)];
            $storeId = DemoState::storeId($storeSlug);
            $status = $statusPool[array_rand($statusPool)];

            $createdAt = now()->subDays(random_int(1, 120))->subMinutes(random_int(0, 1400));
            $items = $this->pickItems($variantsByStore[$storeSlug], $soldPerVariant, $createdAt);

            if (empty($items)) {
                $o--;
                $totalOrders--;

                continue;
            }

            $shipping = $this->shippingCost($storeSlug, random_int(0, 4));
            $subtotal = array_sum(array_map(static fn ($it) => $it['price'] * $it['qty'], $items));

            // Sebagian pesanan memakai voucher & sebagian lagi belum dibayar.
            $useVoucher = $o % 9 === 0 && $status !== 'pending';
            $discount = $useVoucher ? min(round($subtotal * 0.1, 0), 15000) : 0;

            $total = $subtotal + $shipping - $discount;
            $adminFee = round($subtotal * 0.025, 2);
            $sellerNet = $subtotal + $shipping - $discount - $adminFee;

            if ($status === 'cancelled') {
                $total = $subtotal + $shipping - $discount;
            }

            $orderNumber = 'INV-'.sprintf('%05d', $orderSeq++);

            $orderId = DB::table('orders')->insertGetId([
                'order_number' => $orderNumber,
                'order_type' => 'normal',
                'preorder_release_at' => null,
                'scheduled_at' => null,
                'received_at' => $status === 'completed' ? $createdAt->copy()->addDays(random_int(2, 5))->toDateTimeString() : null,
                'user_id' => $buyerId,
                'voucher_id' => $useVoucher ? $this->randomVoucher() : null,
                'total_amount' => max(0, $total),
                'discount_amount' => $discount,
                'shipping_discount_amount' => 0,
                'admin_fee' => $adminFee,
                'seller_net' => max(0, round($sellerNet, 2)),
                'status' => $status,
                'payment_status' => $this->paymentStatusFor($status),
                'payment_method' => in_array($status, ['pending', 'cancelled'], true) ? null : ['transfer_bank', 'qris', 'midtrans', 'e_wallet'][array_rand(['transfer_bank', 'qris', 'midtrans', 'e_wallet'])],
                'midtrans_snap_token' => in_array($status, ['pending', 'cancelled'], true) ? null : 'demo-snap-'.$orderNumber,
                'shipping_address' => $this->shippingAddressJson($buyerId, $buyerAddresses),
                'created_at' => $createdAt,
                'updated_at' => in_array($status, ['completed', 'processing', 'shipped'], true) ? $createdAt->copy()->addDays(random_int(0, 3)) : $createdAt,
            ]);

            $subTotal2 = $subtotal;
            $subOrderNumber = 'SUB-'.$orderNumber;
            $subOrderId = DB::table('sub_orders')->insertGetId([
                'order_id' => $orderId,
                'store_id' => $storeId,
                'sub_order_number' => $subOrderNumber,
                'total_items_price' => $subTotal2,
                'shipping_cost' => $shipping,
                'admin_fee' => $adminFee,
                'seller_net' => max(0, round($sellerNet, 2)),
                'courier' => $status === 'pending' || $status === 'cancelled' ? null : ['jne', 'jnt', 'sicepat', 'anteraja'][array_rand(['jne', 'jnt', 'sicepat', 'anteraja'])],
                'service' => $status === 'pending' || $status === 'cancelled' ? null : 'Reguler',
                'destination_id' => $this->destinationFor($buyerId, $buyerAddresses),
                'status' => $status,
                'tracking_number' => in_array($status, ['shipped', 'completed'], true) ? 'KMR'.random_int(100000000, 999999999) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(random_int(0, 3)),
            ]);

            // Order items.
            $itemRows = [];
            foreach ($items as $it) {
                $itemRows[] = [
                    'sub_order_id' => $subOrderId,
                    'product_id' => $it['product_id'],
                    'variant_id' => $it['variant_id'],
                    'product_name' => $it['name'],
                    'sku' => $it['sku'],
                    'price' => $it['price'],
                    'quantity' => $it['qty'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
            DB::table('order_items')->insert($itemRows);
            $orderItemIds = DB::table('order_items')->where('sub_order_id', $subOrderId)->pluck('id')->all();

            foreach ($itemRows as $idx => $row) {
                $orderItems[] = [
                    'order_id' => $orderId,
                    'order_item_id' => $orderItemIds[$idx] ?? null,
                    'product_id' => $row['product_id'],
                    'variant_id' => $row['variant_id'],
                    'store_slug' => $storeSlug,
                    'store_id' => $storeId,
                    'buyer_id' => $buyerId,
                    'status' => $status,
                    'qty' => $row['quantity'],
                    'created_at' => $createdAt,
                ];
            }

            $orders[] = [
                'id' => $orderId,
                'order_number' => $orderNumber,
                'store_id' => $storeId,
                'store_slug' => $storeSlug,
                'buyer_id' => $buyerId,
                'status' => $status,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'admin_fee' => $adminFee,
                'seller_net' => round($sellerNet, 2),
                'created_at' => $createdAt,
            ];

            // Pembayaran.
            if (in_array($status, ['processing', 'shipped', 'completed'], true)) {
                $payStatus = $status === 'completed' && $o % 2 === 0 ? 'settled' : 'paid';
                DB::table('payments')->insert([
                    'order_number' => $orderNumber,
                    'transaction_id' => 'TRX-'.$orderNumber,
                    'payment_method' => 'transfer_bank',
                    'amount' => max(0, $total),
                    'status' => $payStatus,
                    'payload' => json_encode(['paid_at' => $createdAt->copy()->addHours(random_int(1, 10))->toDateTimeString(), 'channel' => 'demo']),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }

        // ── Mutasi stok produk (ledger konsisten: berakhir = stok saat ini) ──
        $this->writeProductStockLedger($variantList, $orderItems, $adminId, $now);

        // ── Ulasan produk (banyak review) ───────────────────────────────────
        $this->seedReviews($orderItems, $now);

        // ── Settlement & penarikan dana ────────────────────────────────────
        $this->seedSettlements($orders, $adminId, $now);
        $this->seedWithdrawals($adminId, $now);

        // ── Keuangan toko operasional ────────────────────────────────────────
        $this->seedOpsFinance($adminId, $now);

        // ── Keranjang & wishlist ─────────────────────────────────────────────
        $this->seedBrowsing($productsByStore, $variantList);

        // ── Chat, tiket, jadwal, notifikasi admin ────────────────────────────
        $this->seedCommunication($adminId, $now);
        $this->seedAdminNotifications($adminId, $orders, $now);

        $this->command?->info('Demo commerce selesai: '.count($orders).' pesanan, '.count($orderItems).' item terjual.');
    }

    // ─────────────────────────── helpers ───────────────────────────────────

    private function indexVariants(array $variantList): array
    {
        $map = [];
        foreach ($variantList as $v) {
            $map[$v['store_slug']][] = $v;
        }

        return $map;
    }

    private function pickItems(array $variants, array &$sold, Carbon $createdAt): array
    {
        if (empty($variants)) {
            return [];
        }

        $items = [];
        $picked = [];
        $count = random_int(1, 3);

        for ($i = 0; $i < $count; $i++) {
            $candidates = array_values(array_filter($variants, static fn ($v) => ! in_array($v['variant_id'], $picked, true)));
            if (empty($candidates)) {
                break;
            }
            $variant = $candidates[array_rand($candidates)];
            $qty = random_int(1, 3);
            $picked[] = $variant['variant_id'];

            $items[] = [
                'product_id' => $variant['product_id'],
                'variant_id' => $variant['variant_id'],
                'sku' => $variant['sku'],
                'name' => $variant['name'],
                'price' => $variant['price'],
                'qty' => $qty,
            ];
            $sold[$variant['variant_id']] = ($sold[$variant['variant_id']] ?? 0) + $qty;
        }

        return $items;
    }

    private function shippingCost(string $storeSlug, int $roll): float
    {
        if ($roll === 0) {
            return 0; // gratis ongkir.
        }
        $rates = ['sari-nusantara' => 12000, 'raka-teknologi' => 15000, 'kopi-nusantara' => 10000,
            'purnama-batik' => 13000, 'ananda-stationery' => 11000, 'sentra-operasional' => 14000];

        return (float) ($rates[$storeSlug] ?? 12000);
    }

    private function randomVoucher(): ?int
    {
        $codes = ['WELCOME20', 'HEMAT5RB', 'GRATISONGKIR', 'SETIA10', 'SARINUS12', 'KOPIPAGI'];

        return DB::table('vouchers')->where('code', $codes[array_rand($codes)])->value('id');
    }

    private function paymentStatusFor(string $status): string
    {
        return match ($status) {
            'pending', 'cancelled' => 'unpaid',
            'completed' => 'settled',
            default => 'paid',
        };
    }

    private function shippingAddressJson(string $buyerId, $addresses): string
    {
        $addr = $addresses->get($buyerId);
        $recipient = $addr->recipient_name ?? 'Penerima Demo';
        $full = $addr ? ($addr->full_address.', '.$addr->subdistrict.', '.$addr->district.', '.$addr->city_or_regency.', '.$addr->postal_code) : 'Jl. Demo No. 1';

        return json_encode(['recipient' => $recipient, 'phone' => $addr->phone_number ?? '', 'address' => $full], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function destinationFor(string $buyerId, $addresses): string
    {
        $addr = $addresses->get($buyerId);

        return $addr->komerce_destination_id ?? 'KMB-1011';
    }

    /** @param array<int, array<string,mixed>> $orderItems */
    private function writeProductStockLedger(array $variantList, array $orderItems, string $adminId, string $now): void
    {
        $sold = [];
        $occurred = [];
        foreach ($orderItems as $item) {
            $vid = $item['variant_id'];
            $sold[$vid] = ($sold[$vid] ?? 0) + $item['qty'];
            if (! isset($occurred[$vid]) || $occurred[$vid]->gt($item['created_at'])) {
                $occurred[$vid] = $item['created_at'];
            }
        }

        foreach ($variantList as $variant) {
            $vid = $variant['variant_id'];
            $stock = (int) $variant['stock'];
            $qtySold = $sold[$vid] ?? 0;
            $opening = $stock + $qtySold;
            $openingAt = now()->subDays(121)->startOfDay();

            DB::table('stock_movements')->insert([
                'store_id' => $variant['store_id'],
                'product_id' => $variant['product_id'],
                'variant_id' => $vid,
                'order_id' => null,
                'order_item_id' => null,
                'movement_key' => 'opening-'.$vid,
                'type' => 'opening_balance',
                'quantity_delta' => $opening,
                'balance_after' => $opening,
                'stock_dimension' => 'available',
                'reference_type' => 'seed',
                'reference_id' => null,
                'notes' => 'Saldo awal produk saat masuk demo.',
                'occurred_at' => $openingAt,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($qtySold === 0) {
                continue;
            }

            // Outbound per pesanan secara kronologis.
            $balance = $opening;
            $chrono = array_values(array_filter($orderItems, static fn ($it) => (int) $it['variant_id'] === $vid));
            usort($chrono, static fn ($a, $b) => $a['created_at'] <=> $b['created_at']);

            foreach ($chrono as $idx => $item) {
                $balance -= $item['qty'];
                DB::table('stock_movements')->insert([
                    'store_id' => $variant['store_id'],
                    'product_id' => $variant['product_id'],
                    'variant_id' => $vid,
                    'order_id' => $item['order_id'],
                    'order_item_id' => $item['order_item_id'],
                    'movement_key' => 'outbound-'.$vid.'-'.$idx,
                    'type' => 'outbound',
                    'quantity_delta' => -$item['qty'],
                    'balance_after' => $balance,
                    'stock_dimension' => 'available',
                    'reference_type' => 'order',
                    'reference_id' => $item['order_id'],
                    'notes' => 'Penjualan melalui pesanan.',
                    'occurred_at' => $item['created_at'],
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /** @param array<int, array<string,mixed>> $orderItems */
    private function seedReviews(array $orderItems, string $now): void
    {
        $template = [
            'Enak, dikemas rapi, cepat sampai. Recommended!',
            'Produk sesuai deskripsi, kualitas bagus.',
            'Pengiriman cepat, packing aman. Terima kasih.',
            'Sesuai ekspektasi, akan repeat order.',
            'Bagus tapi dikit, overall ok.',
            'Mantap, rasanya enak dan segar.',
            'Barang original, harga bersaing.',
            'Sudah belanja dua kali, selalu puas.',
        ];

        $reviewed = [];
        foreach ($orderItems as $item) {
            if ($item['status'] !== 'completed' || random_int(1, 100) > 55) {
                continue;
            }
            $key = $item['buyer_id'].'|'.$item['order_item_id'];
            if (isset($reviewed[$key])) {
                continue;
            }
            $reviewed[$key] = true;

            $rating = random_int(3, 5);
            DB::table('product_reviews')->insert([
                'product_id' => $item['product_id'],
                'order_id' => $item['order_id'],
                'order_item_id' => $item['order_item_id'],
                'user_id' => $item['buyer_id'],
                'rating' => $rating,
                'review' => $rating >= 4 ? $template[array_rand($template)] : 'Cukup oke, masih bisa diperbaiki kemasannya.',
                'media' => null,
                'is_active' => true,
                'created_by' => $item['buyer_id'],
                'updated_by' => $item['buyer_id'],
                'created_at' => $item['created_at'],
                'updated_at' => $item['created_at'],
            ]);
        }

        $this->command?->info('Demo ulasan: '.count($reviewed).' review dibuat.');
    }

    /** @param array<int, array<string,mixed>> $orders */
    private function seedSettlements(array $orders, string $adminId, string $now): void
    {
        $seq = 1000;
        foreach ($orders as $order) {
            if ($order['status'] !== 'completed') {
                continue;
            }
            $settledAt = $order['created_at']->copy()->addDays(7)->toDateTimeString();
            DB::table('seller_settlements')->insert([
                'store_id' => $order['store_id'],
                'order_id' => $order['id'],
                'sub_order_id' => DB::table('sub_orders')->where('order_id', $order['id'])->value('id'),
                'settlement_number' => 'STL-'.($seq++),
                'gross_amount' => $order['subtotal'],
                'admin_fee' => $order['admin_fee'],
                'shipping_fee' => $order['shipping'],
                'net_amount' => $order['seller_net'],
                'status' => 'settled',
                'settled_at' => $settledAt,
                'notes' => 'Settlement otomatis pesanan selesai.',
                'metadata' => json_encode(['source' => 'demo']),
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $order['created_at'],
                'updated_at' => $now,
            ]);
        }
    }

    private function seedWithdrawals(string $adminId, string $now): void
    {
        $seq = 2000;
        $methods = ['bank_transfer', 'e_wallet'];

        foreach (DemoIds::SELLERS as $sellerId) {
            $storeId = DB::table('stores')->where('user_id', $sellerId)->value('id');
            if (! $storeId) {
                continue;
            }

            $netSum = (float) DB::table('seller_settlements')->where('store_id', $storeId)->sum('net_amount');
            if ($netSum <= 0) {
                continue;
            }

            $count = random_int(1, 2);
            for ($i = 0; $i < $count; $i++) {
                $amount = (float) number_format($netSum / ($count + 1), 2, '.', '');
                $status = $i === 0 ? 'completed' : 'pending';
                $created = now()->subDays(random_int(1, 20));

                DB::table('seller_withdrawals')->insert([
                    'store_id' => $storeId,
                    'user_id' => $sellerId,
                    'withdrawal_number' => 'WD-'.($seq++),
                    'amount' => $amount,
                    'method' => $methods[array_rand($methods)],
                    'bank_details' => json_encode(['bank' => 'BCA', 'account_no' => '5040'.random_int(1000, 9999), 'account_name' => 'Pemilik Toko']),
                    'status' => $status,
                    'rejection_reason' => null,
                    'processed_at' => $status === 'completed' ? $created->copy()->addDays(1) : null,
                    'processed_by' => $status === 'completed' ? $adminId : null,
                    'is_active' => true,
                    'created_by' => $sellerId,
                    'updated_by' => $sellerId,
                    'created_at' => $created,
                    'updated_at' => $created,
                ]);
            }
        }
    }

    private function seedOpsFinance(string $adminId, string $now): void
    {
        $storeId = DemoState::storeId('sentra-operasional');
        if (! $storeId) {
            return;
        }

        $seq = 3000;
        $now = now();

        // Pemasukan kas harian 6 bulan terakhir.
        for ($m = 5; $m >= 0; $m--) {
            $base = $now->copy()->firstOfMonth()->subMonths($m);
            $amount = round((random_int(35, 55) * 10000), 2);

            DB::table('financial_transactions')->insert([
                'store_id' => $storeId,
                'order_id' => null,
                'user_id' => DemoIds::SELLER_OPS,
                'reference_number' => 'FIN-INC-'.($seq++),
                'type' => 'income',
                'title' => 'Pemasukan operasional '.$base->format('F Y'),
                'description' => 'Rekap pendapatan penjualan harian dari dapur produksi.',
                'amount' => $amount,
                'paid_amount' => $amount,
                'status' => 'settled',
                'due_date' => null,
                'occurred_at' => $base->copy()->addDay()->setTime(21, 0),
                'settled_at' => $base->copy()->addDay()->setTime(21, 0),
                'is_active' => true,
                'metadata' => json_encode(['period' => $base->format('Y-m')]),
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $base->copy()->addDay(),
                'updated_at' => $base->copy()->addDay(),
            ]);
        }

        // Pengeluaran rutin (belanja bahan baku & operasional).
        $expenses = [
            ['Belanja Beras 100kg', 'RM supply beras 100kg untuk 2 bulan.', 1250000, 'open'],
            ['Belanja Ayam Potong', 'Ayam potong segar 50kg untuk produksi.', 1600000, 'settled'],
            ['Belanja Daging Sapi Giling', 'Daging sapi giling 20kg untuk bakso.', 1760000, 'settled'],
            ['Gas LPG 12kg x 2', 'Pengisian gas untuk operasional dapur.', 420000, 'settled'],
            ['Kemasan & Plastik', 'Packaging pesanan dan pembungkus.', 260000, 'open'],
            ['Perawatan Alat Dapur', 'Servis mixer dan kompor.', 350000, 'settled'],
        ];
        foreach ($expenses as [$title, $desc, $amount, $status]) {
            DB::table('financial_transactions')->insert([
                'store_id' => $storeId,
                'order_id' => null,
                'user_id' => DemoIds::SELLER_OPS,
                'reference_number' => 'FIN-EXP-'.($seq++),
                'type' => 'expense',
                'title' => $title,
                'description' => $desc,
                'amount' => (float) $amount,
                'paid_amount' => $status === 'settled' ? (float) $amount : 0,
                'status' => $status,
                'due_date' => null,
                'occurred_at' => now()->subDays(random_int(3, 60))->setTime(15, 0),
                'settled_at' => $status === 'settled' ? now()->subDays(random_int(2, 55))->setTime(15, 0) : null,
                'is_active' => true,
                'metadata' => null,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Piutang: katering kantor (cicilan) + open.
        $receivables = [
            ['Katering Kantor PT Maju Jaya', 'Pesanan katering 3 bulan untuk 50 karyawan. Cicilan 3x.', 27000000, 'partial', now()->subDays(50)],
            ['Pesanan Partai Acara Nikah', 'Katering acara nikah 200 pax, sisa pelunasan.', 18000000, 'open', now()->subDays(20)],
        ];
        foreach ($receivables as [$title, $desc, $amount, $status, $date]) {
            $txId = DB::table('financial_transactions')->insertGetId([
                'store_id' => $storeId,
                'order_id' => null,
                'user_id' => DemoIds::SELLER_OPS,
                'reference_number' => 'FIN-RCV-'.($seq++),
                'type' => 'receivable',
                'title' => $title,
                'description' => $desc,
                'amount' => (float) $amount,
                'paid_amount' => 0,
                'status' => $status,
                'due_date' => $date->copy()->addDays(90)->toDateString(),
                'occurred_at' => $date,
                'settled_at' => null,
                'is_active' => true,
                'metadata' => json_encode(['payer' => 'PT Maju Jaya', 'invoice_prefix' => 'INV-OP']),
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $date,
                'updated_at' => $now,
            ]);

            if ($status === 'partial') {
                $paid = round($amount / 3, 0);
                DB::table('financial_transactions')->where('id', $txId)->update(['paid_amount' => $paid]);
                $this->insertPaymentHistory($storeId, $txId, $paid, 0, $paid, 'transfer_bank', 'Cicilan 1/3 '.$title, $date->copy()->addDays(3));
            }
        }

        // Hutang: ke pemasok.
        $payables = [
            ['Hutang Pemasok Beras', 'Beras premium 100kg diambil lebih dulu.', 1250000, 'open', now()->subDays(14)],
            ['Hutang Pengrajin Kemasan', 'Cetak kemasan edisi baru.', 875000, 'partial', now()->subDays(21)],
        ];
        foreach ($payables as [$title, $desc, $amount, $status, $date]) {
            $txId = DB::table('financial_transactions')->insertGetId([
                'store_id' => $storeId,
                'order_id' => null,
                'user_id' => DemoIds::SELLER_OPS,
                'reference_number' => 'FIN-PAY-'.($seq++),
                'type' => 'payable',
                'title' => $title,
                'description' => $desc,
                'amount' => (float) $amount,
                'paid_amount' => 0,
                'status' => $status,
                'due_date' => $date->copy()->addDays(30)->toDateString(),
                'occurred_at' => $date,
                'settled_at' => null,
                'is_active' => true,
                'metadata' => json_encode(['supplier' => 'Supplier Pemasok']),
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $date,
                'updated_at' => $now,
            ]);

            if ($status === 'partial') {
                $paid = round($amount / 2, 0);
                DB::table('financial_transactions')->where('id', $txId)->update(['paid_amount' => $paid]);
                $this->insertPaymentHistory($storeId, $txId, $paid, 0, $paid, 'cash', 'Pembayaran 1/2 '.$title, $date->copy()->addDays(5));
            }
        }

        $this->command?->info('Demo finance ops: 6 income, 6 expense, 2 receivable, 2 payable.');
    }

    private function insertPaymentHistory(int $storeId, int $txId, float $amount, float $before, float $after, string $method, string $notes, $paidAt): void
    {
        DB::table('financial_payment_histories')->insert([
            'financial_transaction_id' => $txId,
            'store_id' => $storeId,
            'recorded_by' => DemoIds::SELLER_OPS,
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'payment_method' => $method,
            'reference_number' => 'PAYH-'.$txId,
            'notes' => $notes,
            'paid_at' => $paidAt,
            'created_at' => $paidAt,
            'updated_at' => $paidAt,
        ]);
    }

    private function seedBrowsing(array $productsByStore, array $variantList): void
    {
        $now = now()->toDateTimeString();
        $allProducts = array_values(array_unique(array_merge(...array_values($productsByStore))));

        foreach (DemoIds::BUYERS as $userId) {
            $cartId = DB::table('carts')->where('user_id', $userId)->value('id');
            $wishlistId = $userId;

            // Keranjang: 1-3 item.
            $picked = (array) array_rand($variantList, min(3, count($variantList)));
            if (empty($picked)) {
                continue;
            }
            foreach ((array) $picked as $idx) {
                $variant = $variantList[$idx];
                DB::table('cart_items')->insertOrIgnore([
                    'cart_id' => $cartId,
                    'product_variant_id' => $variant['variant_id'],
                    'quantity' => random_int(1, 2),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Wishlist: 2-5 produk.
            $wishPicks = array_rand($allProducts, min(5, count($allProducts)));
            foreach ((array) $wishPicks as $pid) {
                DB::table('wishlist_items')->insertOrIgnore([
                    'wishlist_id' => $wishlistId,
                    'product_id' => $allProducts[$pid],
                    'added_at' => now()->subDays(random_int(1, 30)),
                ]);
            }
        }
    }

    private function seedCommunication(string $adminId, string $now): void
    {
        $now = now();
        $buyer1 = DemoIds::BUYER_1;
        $buyer2 = DemoIds::BUYER_3;
        $sariSeller = DemoIds::SELLER_SARI;

        // Chat buyer-Seller Sari.
        $conv = DB::table('conversations')->insertGetId([
            'type' => 'direct',
            'store_id' => DemoState::storeId('sari-nusantara'),
            'order_id' => null,
            'subject' => 'Tanya produk keripik',
            'target_role' => 'seller',
            'is_active' => true,
            'created_by' => $buyer1,
            'updated_by' => $buyer1,
            'created_at' => $now->subDays(2),
            'updated_at' => $now,
        ]);
        DB::table('conversation_participants')->insert([
            ['conversation_id' => $conv, 'user_id' => $buyer1, 'last_read_at' => $now, 'joined_at' => $now->subDays(2), 'is_muted' => false],
            ['conversation_id' => $conv, 'user_id' => $sariSeller, 'last_read_at' => $now->subHours(1), 'joined_at' => $now->subDays(2), 'is_muted' => false],
        ]);
        $messages = [
            [$buyer1, 'Halo kak, keripik singkong balado masih ready ya?'],
            [$sariSeller, 'Halo kak, ready kok. Mau pesan berapa?'],
            [$buyer1, '2 bungkus balado + 1 original ya kak.'],
            [$sariSeller, 'Siap, nanti saya siapkan pesanannya. Terima kasih kak!'],
        ];
        foreach ($messages as $i => [$sender, $text]) {
            DB::table('chat_messages')->insert([
                'conversation_id' => $conv,
                'sender_id' => $sender,
                'message_type' => 'text',
                'message' => $text,
                'attachments' => null,
                'edited_at' => null,
                'created_at' => $now->subDays(2)->addHours($i),
                'updated_at' => $now->subDays(2)->addHours($i),
            ]);
        }

        // Chat admin-seller (notifikasi promo).
        $conv2 = DB::table('conversations')->insertGetId([
            'type' => 'admin',
            'store_id' => DemoState::storeId('raka-teknologi'),
            'order_id' => null,
            'subject' => 'Persetujuan promo',
            'target_role' => 'seller',
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'created_at' => $now->subDays(5),
            'updated_at' => $now,
        ]);
        DB::table('conversation_participants')->insert([
            ['conversation_id' => $conv2, 'user_id' => $adminId, 'last_read_at' => $now, 'joined_at' => $now->subDays(5), 'is_muted' => false],
            ['conversation_id' => $conv2, 'user_id' => DemoIds::SELLER_RAKA, 'last_read_at' => $now->subHours(2), 'joined_at' => $now->subDays(5), 'is_muted' => false],
        ]);
        foreach ([[$adminId, 'Selamat, promo toko Kak Raka sudah disetujui.'], [DemoIds::SELLER_RAKA, 'Terima kasih admin! Mau lanjut pasang yang lain bisa?'], [$adminId, 'Tentu bisa. Ajukan lewat menu promosi ya.']] as $i => [$sender, $text]) {
            DB::table('chat_messages')->insert([
                'conversation_id' => $conv2,
                'sender_id' => $sender,
                'message_type' => 'text',
                'message' => $text,
                'attachments' => null,
                'edited_at' => null,
                'created_at' => $now->subDays(5)->addHours($i * 5),
                'updated_at' => $now->subDays(5)->addHours($i * 5),
            ]);
        }

        // Support tickets.
        $tickets = [
            [$buyer2, 'pengiriman', 'Pesanan belum sampai', 'Pesanan sudah 5 hari tapi belum kunjung sampai. Mohon dibantu cek resinya.', 'high', 'open', $now->subDays(6)],
            [DemoIds::BUYER_4, 'produk', 'Barang kurang satu item', 'Saya menerima pesanan tapi kurang 1 item untuk varian yang dipesan.', 'medium', 'resolved', $now->subDays(12)],
            [DemoIds::BUYER_6, 'pembayaran', 'Voucher tidak terpotong', 'Saat checkout voucher tidak terpotong otomatis.', 'low', 'open', $now->subDays(3)],
            [DemoIds::BUYER_8, 'pengembalian', 'Minta pengembalian dana', 'Produk rusak saat diterima, ingin ajukan pengembalian.', 'high', 'resolved', $now->subDays(8)],
        ];
        $ticketSeq = 4000;
        foreach ($tickets as [$buyerId, $category, $subject, $desc, $priority, $status, $date]) {
            DB::table('support_tickets')->insert([
                'ticket_number' => 'TCK-'.($ticketSeq++),
                'user_id' => $buyerId,
                'store_id' => $status === 'resolved' ? DemoState::storeId('purnama-batik') : null,
                'order_id' => null,
                'category' => $category,
                'subject' => $subject,
                'description' => $desc,
                'priority' => $priority,
                'status' => $status,
                'assigned_to' => $status === 'resolved' ? $adminId : null,
                'last_replied_at' => $status === 'resolved' ? $date->copy()->addDay() : null,
                'resolved_at' => $status === 'resolved' ? $date->copy()->addDay() : null,
                'is_active' => true,
                'created_by' => $buyerId,
                'updated_by' => $buyerId,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        // Schedules (planner) untuk seller ops + seller.
        $schedules = [
            [DemoIds::SELLER_OPS, 'produksi', 'Produksi bakso mingguan', 'Buat bakso 10kg untuk stok toko.', 'todo', 'high', 'Senin pagi'],
            [DemoIds::SELLER_OPS, 'produksi', 'Stok opname bahan baku', 'Hitung stok beras, cabai, dan minyak.', 'in_progress', 'medium', 'Hari ini'],
            [DemoIds::SELLER_OPS, 'pesanan', 'Kirim pesanan partai katering', 'Antarkan 50 boks pesanan PT Maju Jaya.', 'done', 'high', 'Kemarin'],
            [DemoIds::SELLER_SARI, 'promosi', 'Foto produk kue kering baru', 'Sesi foto untuk koleksi baru nastar.', 'todo', 'medium', 'Jumat'],
            [DemoIds::SELLER_PURNAMA, 'produksi', 'Terima kain batik dari pengrajin', 'Pengecekan kualitas 20 lembar kain.', 'in_progress', 'medium', 'Hari ini'],
        ];
        $scheduleSeq = 1;
        foreach ($schedules as [$owner, $type, $title, $desc, $status, $priority, $dayLabel]) {
            $date = $this->scheduleDate($dayLabel);
            DB::table('schedules')->insert([
                'user_id' => $owner,
                'store_id' => DB::table('stores')->where('user_id', $owner)->value('id'),
                'title' => $title,
                'description' => $desc,
                'type' => $type,
                'status' => $status,
                'position' => $scheduleSeq++,
                'assignee' => null,
                'label' => $type === 'produksi' ? 'Produksi' : 'Operasional',
                'priority' => $priority,
                'color' => $type === 'produksi' ? '#10B981' : '#F59E0B',
                'date' => $date->toDateString(),
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'is_all_day' => false,
                'is_completed' => $status === 'done',
                'completed_at' => $status === 'done' ? $date->copy()->setTime(12, 0) : null,
                'metadata' => json_encode(['source' => 'demo']),
                'completion_proof' => $status === 'done' ? json_encode(['photo' => null]) : null,
                'is_active' => true,
                'created_by' => $owner,
            ]);
        }
    }

    private function scheduleDate(string $label): Carbon
    {
        return match ($label) {
            'Senin pagi' => now()->next('Monday')->setTime(8, 0),
            'Hari ini' => now()->setTime(8, 0),
            'Kemarin' => now()->subDay()->setTime(8, 0),
            default => now()->addDays(2)->setTime(8, 0),
        };
    }

    private function seedAdminNotifications(string $adminId, array $orders, string $now): void
    {
        $now = now();
        $notifications = [];

        // Order baru (untuk admin).
        if (count($orders) > 0) {
            $recent = array_values(array_filter($orders, static fn ($o) => $o['created_at']->gte($now->subDays(3))));
            foreach (array_slice($recent, 0, 8) as $order) {
                $notifications[] = [
                    'user_id' => $adminId,
                    'actor_id' => $order['buyer_id'],
                    'store_id' => $order['store_id'],
                    'module' => 'order',
                    'type' => 'order_created',
                    'title' => 'Pesanan baru #'.$order['order_number'],
                    'message' => 'Pesanan baru senilai Rp'.number_format($order['subtotal']).' masuk ke toko '.$order['store_slug'].'.',
                    'reference_type' => 'order',
                    'reference_id' => $order['order_number'],
                    'url' => '/admin/orders/'.$order['order_number'],
                    'meta' => json_encode(['amount' => $order['subtotal']]),
                    'read_at' => null,
                    'is_active' => true,
                    'created_at' => $order['created_at'],
                    'updated_at' => $order['created_at'],
                ];
            }
        }

        // Review baru.
        $notifications[] = [
            'user_id' => $adminId,
            'actor_id' => DemoIds::BUYER_3,
            'store_id' => DemoState::storeId('kopi-nusantara'),
            'module' => 'review',
            'type' => 'review_received',
            'title' => 'Ulasan bintang 5 untuk Rumah Kopi Nusantara',
            'message' => 'Pembeli memberikan ulasan positif pada produk Kopi Arabika Gayo.',
            'reference_type' => 'review',
            'reference_id' => null,
            'url' => '/admin/reviews',
            'meta' => json_encode(['rating' => 5]),
            'read_at' => null,
            'is_active' => true,
            'created_at' => $now->subHours(5),
            'updated_at' => $now->subHours(5),
        ];

        // Penarikan dana seller.
        $notifications[] = [
            'user_id' => $adminId,
            'actor_id' => DemoIds::SELLER_RAKA,
            'store_id' => DemoState::storeId('raka-teknologi'),
            'module' => 'withdrawal',
            'type' => 'withdrawal_request',
            'title' => 'Permintaan penarikan dana dari Raka Cell',
            'message' => 'Seller mengajukan penarikan dana sebesar Rp1.200.000.',
            'reference_type' => 'withdrawal',
            'reference_id' => '',
            'url' => '/admin/withdrawals',
            'meta' => json_encode(['amount' => 1200000, 'status' => 'pending']),
            'read_at' => null,
            'is_active' => true,
            'created_at' => $now->subHours(2),
            'updated_at' => $now->subHours(2),
        ];

        // Promo menunggu persetujuan.
        $notifications[] = [
            'user_id' => $adminId,
            'actor_id' => DemoIds::SELLER_SARI,
            'store_id' => DemoState::storeId('sari-nusantara'),
            'module' => 'promotion',
            'type' => 'promotion_pending',
            'title' => 'Promo baru menunggu persetujuan',
            'message' => 'Toko Sari Nusantara mengajukan promo gratis ongkir.',
            'reference_type' => 'promotion',
            'reference_id' => '',
            'url' => '/admin/promotions',
            'meta' => json_encode(['status' => 'pending']),
            'read_at' => $now->subHours(1),
            'is_active' => true,
            'created_at' => $now->subDay(),
            'updated_at' => $now->subDay(),
        ];

        // Stok menipis.
        $notifications[] = [
            'user_id' => $adminId,
            'actor_id' => DemoIds::SELLER_ANANDA,
            'store_id' => DemoState::storeId('ananda-stationery'),
            'module' => 'stock',
            'type' => 'low_stock',
            'title' => 'Stok Buku Catatan A5 varian Merah habis',
            'message' => 'Varian Merah Buku Catatan Hardcover A5 berada di bawah stok minimum.',
            'reference_type' => 'low_stock',
            'reference_id' => '',
            'url' => '/admin/stocks',
            'meta' => json_encode(['warning' => 'stok kurang dari minimum']),
            'read_at' => null,
            'is_active' => true,
            'created_at' => $now->subMinutes(30),
            'updated_at' => $now->subMinutes(30),
        ];

        DB::table('admin_notifications')->insert($notifications);
        $this->command?->info('Demo notifikasi admin: '.count($notifications).' notifikasi.');
    }
}
