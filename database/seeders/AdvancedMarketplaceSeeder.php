<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class AdvancedMarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('financial_transactions')) {
            return;
        }

        $now = now();
        $adminId = DB::table('users')->where('id', SeederIds::SUPER_ADMIN)->value('id')
            ?? DB::table('users')->orderBy('created_at')->value('id');
        $store = DB::table('stores')->where('status', 'approved')->where('is_active', true)->orderBy('id')->first();
        $buyer = DB::table('users')
            ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('roles.name', 'buyer')
            ->where('users.is_active', true)
            ->select('users.id', 'users.name')
            ->orderBy('users.created_at')
            ->first();

        if (! $adminId || ! $store || ! $buyer) {
            return;
        }

        DB::transaction(function () use ($now, $adminId, $store, $buyer): void {
            $this->seedOrderTypes($now);
            $this->seedPromotionPayments($store, $adminId, $now);
            $this->seedFinance($store, $buyer, $adminId, $now);
            $this->seedStock($store, $adminId, $now);
            $this->seedShowcases($store, $adminId, $now);
            $this->seedTickets($store, $buyer, $adminId, $now);
            $this->seedMissions($buyer, $adminId, $now);
            $this->seedReview($buyer, $adminId, $now);
            $this->seedChat($store, $buyer, $adminId, $now);
        });
    }

    private function seedOrderTypes($now): void
    {
        $orders = DB::table('orders')->orderBy('id')->limit(3)->get();
        $types = ['normal', 'preorder', 'booking'];

        foreach ($orders as $index => $order) {
            $type = $types[$index] ?? 'normal';
            DB::table('orders')->where('id', $order->id)->update([
                'order_type' => $type,
                'preorder_release_at' => $type === 'preorder' ? $now->copy()->addDays(14) : null,
                'booking_expires_at' => $type === 'booking' ? $now->copy()->addHours(24) : null,
                'received_at' => in_array($order->status, ['received', 'completed'], true) ? $now->copy()->subDay() : null,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedPromotionPayments(object $store, string $adminId, $now): void
    {
        $rows = [
            [
                'payment_number' => 'PROMO-PAY-DEMO-001',
                'package_name' => 'Paket Promosi 7 Hari',
                'amount' => 150000,
                'status' => 'approved',
                'reviewed_at' => $now,
                'reviewed_by' => $adminId,
            ],
            [
                'payment_number' => 'PROMO-PAY-DEMO-002',
                'package_name' => 'Paket Promosi 30 Hari',
                'amount' => 450000,
                'status' => 'pending',
                'reviewed_at' => null,
                'reviewed_by' => null,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('promotion_payments')->updateOrInsert(
                ['payment_number' => $row['payment_number']],
                [
                    'store_id' => $store->id,
                    'user_id' => $store->user_id,
                    'package_name' => $row['package_name'],
                    'amount' => $row['amount'],
                    'payment_method' => 'bank_transfer',
                    'proof_url' => '/storage/promotion-payments/demo-proof.jpg',
                    'status' => $row['status'],
                    'rejection_reason' => null,
                    'paid_at' => $now->copy()->subDay(),
                    'reviewed_at' => $row['reviewed_at'],
                    'reviewed_by' => $row['reviewed_by'],
                    'is_active' => true,
                    'created_by' => $store->user_id,
                    'updated_by' => $row['reviewed_by'] ?? $store->user_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
        }
    }

    private function seedFinance(object $store, object $buyer, string $adminId, $now): void
    {
        $order = DB::table('orders')
            ->join('sub_orders', 'sub_orders.order_id', '=', 'orders.id')
            ->where('sub_orders.store_id', $store->id)
            ->select('orders.id', 'orders.total_amount')
            ->orderByDesc('orders.id')
            ->first();
        $rows = [
            ['reference_number' => 'FIN-DEMO-INCOME-001', 'type' => 'income', 'title' => 'Penjualan Marketplace', 'amount' => $order?->total_amount ?? 850000, 'paid_amount' => $order?->total_amount ?? 850000, 'status' => 'paid', 'due_date' => null, 'settled_at' => $now, 'order_id' => $order?->id, 'user_id' => $buyer->id],
            ['reference_number' => 'FIN-DEMO-EXPENSE-001', 'type' => 'expense', 'title' => 'Biaya Kemasan dan Operasional', 'amount' => 175000, 'paid_amount' => 175000, 'status' => 'paid', 'due_date' => null, 'settled_at' => $now, 'order_id' => null, 'user_id' => null],
            ['reference_number' => 'FIN-DEMO-RECEIVABLE-001', 'type' => 'receivable', 'title' => 'Piutang Pesanan Korporat', 'amount' => 1250000, 'paid_amount' => 500000, 'status' => 'partial', 'due_date' => $now->copy()->addDays(14)->toDateString(), 'settled_at' => null, 'order_id' => null, 'user_id' => $buyer->id],
            ['reference_number' => 'FIN-DEMO-PAYABLE-001', 'type' => 'payable', 'title' => 'Hutang Pengadaan Stok', 'amount' => 2300000, 'paid_amount' => 0, 'status' => 'open', 'due_date' => $now->copy()->addDays(21)->toDateString(), 'settled_at' => null, 'order_id' => null, 'user_id' => null],
        ];

        foreach ($rows as $row) {
            DB::table('financial_transactions')->updateOrInsert(
                ['reference_number' => $row['reference_number']],
                [
                    'store_id' => $store->id,
                    'order_id' => $row['order_id'],
                    'user_id' => $row['user_id'],
                    'type' => $row['type'],
                    'title' => $row['title'],
                    'description' => 'Data contoh terhubung untuk pengujian fitur keuangan.',
                    'amount' => $row['amount'],
                    'paid_amount' => $row['paid_amount'],
                    'status' => $row['status'],
                    'due_date' => $row['due_date'],
                    'occurred_at' => $now->copy()->subDays(2),
                    'settled_at' => $row['settled_at'],
                    'is_active' => true,
                    'metadata' => json_encode(['seeded' => true], JSON_UNESCAPED_UNICODE),
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
        }
    }

    private function seedStock(object $store, string $adminId, $now): void
    {
        $variants = DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('product_variants.store_id', $store->id)
            ->select('product_variants.id', 'product_variants.product_id', 'product_variants.stock')
            ->orderBy('product_variants.id')
            ->limit(4)
            ->get();

        foreach ($variants as $index => $variant) {
            $key = 'seed-opening-'.$variant->id;
            $existing = DB::table('stock_movements')->where('movement_key', $key)->first();
            $payload = [
                'store_id' => $store->id,
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'order_id' => null,
                'order_item_id' => null,
                'movement_key' => $key,
                'type' => 'opening_balance',
                'quantity_delta' => (int) $variant->stock,
                'balance_after' => (int) $variant->stock,
                'reference_type' => 'seeder',
                'reference_id' => 'advanced-marketplace',
                'notes' => 'Saldo awal stok untuk pengujian riwayat.',
                'occurred_at' => $now->copy()->subDays(5 - $index),
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'updated_at' => $now,
                'deleted_at' => null,
            ];

            if ($existing) {
                DB::table('stock_movements')->where('id', $existing->id)->update($payload);
            } else {
                DB::table('stock_movements')->insert([...$payload, 'created_at' => $now]);
            }
        }
    }

    private function seedShowcases(object $store, string $adminId, $now): void
    {
        $definitions = [
            ['slug' => 'produk-unggulan', 'name' => 'Produk Unggulan', 'description' => 'Pilihan produk paling diminati pelanggan.', 'sort_order' => 1],
            ['slug' => 'produk-terbaru', 'name' => 'Produk Terbaru', 'description' => 'Koleksi produk terbaru dari toko.', 'sort_order' => 2],
        ];
        $productIds = DB::table('products')->where('store_id', $store->id)->where('is_active', true)->orderBy('id')->limit(8)->pluck('id')->all();

        foreach ($definitions as $definition) {
            DB::table('showcases')->updateOrInsert(
                ['store_id' => $store->id, 'slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'sort_order' => $definition['sort_order'],
                    'is_active' => true,
                    'created_by' => $store->user_id,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
            $showcaseId = DB::table('showcases')->where('store_id', $store->id)->where('slug', $definition['slug'])->value('id');
            DB::table('showcase_products')->where('showcase_id', $showcaseId)->delete();
            foreach (array_slice($productIds, $definition['sort_order'] - 1, 4) as $index => $productId) {
                DB::table('showcase_products')->insert([
                    'showcase_id' => $showcaseId,
                    'product_id' => $productId,
                    'sort_order' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function seedTickets(object $store, object $buyer, string $adminId, $now): void
    {
        $ticketNumber = 'HLP-DEMO-001';
        DB::table('support_tickets')->updateOrInsert(
            ['ticket_number' => $ticketNumber],
            [
                'user_id' => $buyer->id,
                'store_id' => $store->id,
                'order_id' => DB::table('orders')->where('user_id', $buyer->id)->value('id'),
                'category' => 'order',
                'subject' => 'Konfirmasi status pengiriman pesanan',
                'description' => 'Pembeli membutuhkan bantuan untuk memastikan status pengiriman.',
                'priority' => 'normal',
                'status' => 'in_progress',
                'assigned_to' => $adminId,
                'last_replied_at' => $now,
                'resolved_at' => null,
                'is_active' => true,
                'created_by' => $buyer->id,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]
        );
        $ticketId = DB::table('support_tickets')->where('ticket_number', $ticketNumber)->value('id');
        DB::table('support_ticket_messages')->where('ticket_id', $ticketId)->delete();
        DB::table('support_ticket_messages')->insert([
            [
                'ticket_id' => $ticketId,
                'user_id' => $buyer->id,
                'message' => 'Mohon informasi status pengiriman pesanan saya.',
                'attachments' => null,
                'is_internal' => false,
                'read_at' => $now,
                'created_at' => $now->copy()->subHour(),
                'updated_at' => $now->copy()->subHour(),
                'deleted_at' => null,
            ],
            [
                'ticket_id' => $ticketId,
                'user_id' => $adminId,
                'message' => 'Tim kami sedang menghubungi seller dan akan memperbarui statusnya.',
                'attachments' => null,
                'is_internal' => false,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ]);
    }

    private function seedMissions(object $buyer, string $adminId, $now): void
    {
        $voucher = DB::table('vouchers')->where('is_active', true)->where('ends_at', '>', $now)->orderBy('id')->first();
        $definitions = [
            ['code' => 'MISSION-ORDER-3', 'name' => 'Selesaikan 3 Pesanan', 'event_type' => 'order_completed', 'target_value' => 3, 'progress' => 2],
            ['code' => 'MISSION-REVIEW-1', 'name' => 'Berikan Ulasan Pertama', 'event_type' => 'review_submitted', 'target_value' => 1, 'progress' => 1],
        ];

        foreach ($definitions as $definition) {
            DB::table('missions')->updateOrInsert(
                ['code' => $definition['code']],
                [
                    'voucher_id' => $voucher?->id,
                    'name' => $definition['name'],
                    'description' => 'Selesaikan target misi untuk memperoleh voucher reward.',
                    'event_type' => $definition['event_type'],
                    'target_value' => $definition['target_value'],
                    'conditions' => json_encode([], JSON_UNESCAPED_UNICODE),
                    'starts_at' => $now->copy()->subDays(7),
                    'ends_at' => $now->copy()->addDays(30),
                    'is_active' => true,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
            $missionId = DB::table('missions')->where('code', $definition['code'])->value('id');
            $completed = $definition['progress'] >= $definition['target_value'];
            DB::table('mission_user_progress')->updateOrInsert(
                ['mission_id' => $missionId, 'user_id' => $buyer->id],
                [
                    'progress_value' => $definition['progress'],
                    'status' => $completed ? ($voucher ? 'rewarded' : 'completed') : 'in_progress',
                    'completed_at' => $completed ? $now : null,
                    'rewarded_at' => $completed && $voucher ? $now : null,
                    'reward_voucher_id' => $completed ? $voucher?->id : null,
                    'metadata' => json_encode(['seeded' => true], JSON_UNESCAPED_UNICODE),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            if ($completed && $voucher) {
                DB::table('user_vouchers')->updateOrInsert(
                    ['user_id' => $buyer->id, 'voucher_id' => $voucher->id, 'source_type' => 'mission', 'source_id' => (string) $missionId],
                    ['status' => 'available', 'claimed_at' => $now, 'used_at' => null, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    private function seedReview(object $buyer, string $adminId, $now): void
    {
        $item = DB::table('order_items')
            ->join('sub_orders', 'sub_orders.id', '=', 'order_items.sub_order_id')
            ->join('orders', 'orders.id', '=', 'sub_orders.order_id')
            ->where('orders.user_id', $buyer->id)
            ->whereIn('orders.status', ['received', 'completed'])
            ->select('order_items.id', 'order_items.product_id', 'orders.id as order_id')
            ->orderBy('order_items.id')
            ->first();

        if (! $item) {
            $item = DB::table('order_items')
                ->join('sub_orders', 'sub_orders.id', '=', 'order_items.sub_order_id')
                ->join('orders', 'orders.id', '=', 'sub_orders.order_id')
                ->select('order_items.id', 'order_items.product_id', 'orders.id as order_id', 'orders.user_id')
                ->orderBy('order_items.id')
                ->first();

            if (! $item) {
                return;
            }

            $buyer = (object) ['id' => $item->user_id];
            DB::table('orders')->where('id', $item->order_id)->update(['status' => 'completed', 'received_at' => $now, 'updated_at' => $now]);
            DB::table('sub_orders')->where('order_id', $item->order_id)->update(['status' => 'completed', 'updated_at' => $now]);
        }

        DB::table('product_reviews')->updateOrInsert(
            ['order_item_id' => $item->id],
            [
                'product_id' => $item->product_id,
                'order_id' => $item->order_id,
                'user_id' => $buyer->id,
                'rating' => 5,
                'review' => 'Produk sesuai deskripsi, dikemas rapi, dan pengiriman cepat.',
                'media' => json_encode([], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_by' => $buyer->id,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]
        );
    }

    private function seedChat(object $store, object $buyer, string $adminId, $now): void
    {
        $orderId = DB::table('orders')->where('user_id', $buyer->id)->orderBy('id')->value('id');
        $subject = 'Percakapan Pesanan Demo';
        $conversation = DB::table('conversations')->where('subject', $subject)->first();
        $payload = [
            'type' => 'order',
            'store_id' => $store->id,
            'order_id' => $orderId,
            'subject' => $subject,
            'target_role' => null,
            'is_active' => true,
            'created_by' => $buyer->id,
            'updated_by' => $buyer->id,
            'updated_at' => $now,
            'deleted_at' => null,
        ];
        if ($conversation) {
            DB::table('conversations')->where('id', $conversation->id)->update($payload);
            $conversationId = $conversation->id;
        } else {
            $conversationId = DB::table('conversations')->insertGetId([...$payload, 'created_at' => $now]);
        }

        foreach ([$buyer->id, $store->user_id] as $userId) {
            DB::table('conversation_participants')->updateOrInsert(
                ['conversation_id' => $conversationId, 'user_id' => $userId],
                ['last_read_at' => $now, 'joined_at' => $now, 'left_at' => null, 'is_muted' => false]
            );
        }
        DB::table('chat_messages')->where('conversation_id', $conversationId)->delete();
        DB::table('chat_messages')->insert([
            [
                'conversation_id' => $conversationId,
                'sender_id' => $buyer->id,
                'message_type' => 'text',
                'message' => 'Apakah pesanan saya dapat dikirim hari ini?',
                'attachments' => null,
                'edited_at' => null,
                'created_at' => $now->copy()->subMinutes(10),
                'updated_at' => $now->copy()->subMinutes(10),
                'deleted_at' => null,
            ],
            [
                'conversation_id' => $conversationId,
                'sender_id' => $store->user_id,
                'message_type' => 'text',
                'message' => 'Pesanan sedang dikemas dan akan diserahkan ke kurir hari ini.',
                'attachments' => null,
                'edited_at' => null,
                'created_at' => $now->copy()->subMinutes(5),
                'updated_at' => $now->copy()->subMinutes(5),
                'deleted_at' => null,
            ],
        ]);

        $announcementSubject = 'Pengumuman Kebijakan Marketplace';
        $announcement = DB::table('conversations')->where('subject', $announcementSubject)->first();
        $announcementPayload = [
            'type' => 'announcement',
            'store_id' => null,
            'order_id' => null,
            'subject' => $announcementSubject,
            'target_role' => 'seller',
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'updated_at' => $now,
            'deleted_at' => null,
        ];
        if ($announcement) {
            DB::table('conversations')->where('id', $announcement->id)->update($announcementPayload);
            $announcementId = $announcement->id;
        } else {
            $announcementId = DB::table('conversations')->insertGetId([...$announcementPayload, 'created_at' => $now]);
        }
        DB::table('conversation_participants')->updateOrInsert(
            ['conversation_id' => $announcementId, 'user_id' => $store->user_id],
            ['last_read_at' => null, 'joined_at' => $now, 'left_at' => null, 'is_muted' => false]
        );
        DB::table('chat_messages')->where('conversation_id', $announcementId)->delete();
        DB::table('chat_messages')->insert([
            'conversation_id' => $announcementId,
            'sender_id' => $adminId,
            'message_type' => 'text',
            'message' => 'Mohon seller memastikan seluruh informasi produk dan stok selalu diperbarui.',
            'attachments' => null,
            'edited_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);
    }
}
