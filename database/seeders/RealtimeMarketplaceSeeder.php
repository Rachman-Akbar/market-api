<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class RealtimeMarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::transaction(function () use ($now): void {
            $this->refreshOrders($now);
            $this->refreshFinance($now);
            $this->refreshInventory($now);
            $this->refreshCommunication($now);
            $this->refreshNotifications($now);
        });
    }

    private function refreshOrders(CarbonInterface $now): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        $orders = DB::table('orders')
            ->where('order_number', 'like', 'FKR-%')
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'order_number', 'status', 'payment_status']);

        foreach ($orders as $index => $order) {
            $createdAt = $now->copy()->subMinutes(2 + (($index * 43) % 10080));
            $candidateUpdatedAt = $order->status === 'completed'
                ? $createdAt->copy()->addMinutes(90)
                : $createdAt->copy()->addMinutes(15);
            $updatedAt = $candidateUpdatedAt->greaterThan($now) ? $now->copy() : $candidateUpdatedAt;

            DB::table('orders')->where('id', $order->id)->update([
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            if (Schema::hasTable('sub_orders')) {
                DB::table('sub_orders')->where('order_id', $order->id)->update([
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);
            }

            if (Schema::hasTable('payments') && $order->payment_status === 'paid') {
                $candidatePaidAt = $createdAt->copy()->addMinutes(5);
                $paidAt = $candidatePaidAt->greaterThan($now) ? $now->copy() : $candidatePaidAt;
                DB::table('payments')->where('order_number', $order->order_number)->update([
                    'created_at' => $paidAt,
                    'updated_at' => $paidAt,
                ]);
            }
        }
    }

    private function refreshFinance(CarbonInterface $now): void
    {
        if (! Schema::hasTable('financial_transactions')) {
            return;
        }

        $rows = DB::table('financial_transactions')
            ->where(function ($query): void {
                $query->where('reference_number', 'like', 'FIN-DEMO-%')
                    ->orWhere('reference_number', 'like', 'FIN-SEED-%');
            })
            ->orderBy('id')
            ->get(['id', 'type', 'status']);

        foreach ($rows as $index => $row) {
            $occurredAt = $now->copy()->subHours(1 + ($index * 6));
            $update = [
                'occurred_at' => $occurredAt,
                'updated_at' => $now,
            ];

            if (in_array($row->type, ['receivable', 'payable'], true) && $row->status !== 'paid') {
                $update['due_date'] = $now->copy()->addDays($row->type === 'receivable' ? 14 : 21)->toDateString();
                $update['settled_at'] = null;
            }

            if ($row->status === 'paid') {
                $candidateSettledAt = $occurredAt->copy()->addMinutes(30);
                $update['settled_at'] = $candidateSettledAt->greaterThan($now) ? $now->copy() : $candidateSettledAt;
            }

            DB::table('financial_transactions')->where('id', $row->id)->update($update);
        }

        if (Schema::hasTable('financial_payment_histories')) {
            $histories = DB::table('financial_payment_histories')->orderByDesc('id')->limit(50)->get(['id']);

            foreach ($histories as $index => $history) {
                $paidAt = $now->copy()->subMinutes(20 + ($index * 55));
                DB::table('financial_payment_histories')->where('id', $history->id)->update([
                    'paid_at' => $paidAt,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function refreshInventory(CarbonInterface $now): void
    {
        if (Schema::hasTable('stock_movements')) {
            $movements = DB::table('stock_movements')
                ->where(function ($query): void {
                    $query->where('reference_type', 'seeder')
                        ->orWhere('movement_key', 'like', 'seed-%');
                })
                ->orderByDesc('id')
                ->limit(100)
                ->get(['id']);

            foreach ($movements as $index => $movement) {
                DB::table('stock_movements')->where('id', $movement->id)->update([
                    'occurred_at' => $now->copy()->subMinutes(10 + ($index * 35)),
                    'updated_at' => $now,
                ]);
            }
        }

        if (Schema::hasTable('raw_material_stock_movements')) {
            $movements = DB::table('raw_material_stock_movements')
                ->where('reference_type', 'seed')
                ->orderByDesc('id')
                ->limit(100)
                ->get(['id']);

            foreach ($movements as $index => $movement) {
                DB::table('raw_material_stock_movements')->where('id', $movement->id)->update([
                    'occurred_at' => $now->copy()->subMinutes(15 + ($index * 40)),
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function refreshCommunication(CarbonInterface $now): void
    {
        if (! Schema::hasTable('chat_messages')) {
            return;
        }

        $messages = DB::table('chat_messages')->orderByDesc('id')->limit(30)->get(['id', 'conversation_id']);

        foreach ($messages as $index => $message) {
            $createdAt = $now->copy()->subMinutes(1 + ($index * 4));
            DB::table('chat_messages')->where('id', $message->id)->update([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        if (Schema::hasTable('conversations')) {
            $conversationIds = $messages->pluck('conversation_id')->unique()->values();
            foreach ($conversationIds as $index => $conversationId) {
                DB::table('conversations')->where('id', $conversationId)->update([
                    'updated_at' => $now->copy()->subMinutes($index * 3),
                ]);
            }
        }
    }

    private function refreshNotifications(CarbonInterface $now): void
    {
        if (! Schema::hasTable('admin_notifications') || ! Schema::hasTable('users')) {
            return;
        }

        $adminIds = DB::table('users')
            ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->whereIn('roles.name', ['super_admin', 'admin'])
            ->where('users.is_active', true)
            ->whereNull('users.deleted_at')
            ->distinct()
            ->pluck('users.id');

        if ($adminIds->isEmpty()) {
            return;
        }

        $store = Schema::hasTable('stores')
            ? DB::table('stores')->where('is_active', true)->whereNull('deleted_at')->orderBy('id')->first()
            : null;
        $buyerId = DB::table('users')
            ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('roles.name', 'buyer')
            ->where('users.is_active', true)
            ->whereNull('users.deleted_at')
            ->value('users.id');
        $order = Schema::hasTable('orders') ? DB::table('orders')->orderByDesc('created_at')->first() : null;

        $definitions = [
            ['module' => 'order', 'type' => 'testing_order_recent', 'title' => 'Pesanan testing terbaru', 'message' => $order ? 'Pesanan '.$order->order_number.' tersedia untuk pengujian alur terbaru.' : 'Data pesanan testing terbaru tersedia.', 'reference_type' => 'testing', 'reference_id' => 'realtime-order', 'url' => '/admin/orders'],
            ['module' => 'seller', 'type' => 'testing_seller_activity', 'title' => 'Aktivitas seller testing', 'message' => 'Data toko, etalase, produk, dan stok telah disegarkan dengan waktu terkini.', 'reference_type' => 'testing', 'reference_id' => 'realtime-seller', 'url' => '/admin/stores'],
            ['module' => 'finance', 'type' => 'testing_finance_recent', 'title' => 'Keuangan testing diperbarui', 'message' => 'Hutang, piutang, pembayaran parsial, dan histori menggunakan timeline relatif terhadap waktu seeder.', 'reference_type' => 'testing', 'reference_id' => 'realtime-finance', 'url' => '/admin/receivables-payables'],
        ];

        foreach ($adminIds as $adminIndex => $adminId) {
            foreach ($definitions as $index => $definition) {
                $createdAt = $now->copy()->subMinutes(2 + ($index * 7) + $adminIndex);
                DB::table('admin_notifications')->updateOrInsert(
                    [
                        'user_id' => $adminId,
                        'type' => $definition['type'],
                        'reference_type' => $definition['reference_type'],
                        'reference_id' => $definition['reference_id'],
                    ],
                    [
                        'actor_id' => $buyerId,
                        'store_id' => $store?->id,
                        'module' => $definition['module'],
                        'title' => $definition['title'],
                        'message' => $definition['message'],
                        'url' => $definition['url'],
                        'meta' => json_encode(['seeded' => true, 'realtime' => true], JSON_UNESCAPED_UNICODE),
                        'read_at' => null,
                        'is_active' => true,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                        'deleted_at' => null,
                    ]
                );
            }
        }
    }
}
