<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PlannerKanbanSeeder extends Seeder
{
    public function run(): void
    {
        $requiredTables = ['schedules', 'stores'];

        $missingTables = array_values(array_filter(
            $requiredTables,
            static fn (string $table): bool => ! Schema::hasTable($table)
        ));

        if ($missingTables !== []) {
            if ($this->command) {
                $this->command->warn('PlannerKanbanSeeder dilewati karena migration belum lengkap. Tabel belum ada: '.implode(', ', $missingTables).'.');
            }

            return;
        }

        $now = now();
        $owners = [
            SeederIds::SUPER_ADMIN,
            SeederIds::SELLER_ONE,
            SeederIds::SELLER_TWO,
        ];

        $kanbanTitles = [
            ['title' => 'Foto katalog produk baru', 'description' => 'Ambil foto produk unggulan untuk etalase dan promosi.', 'type' => 'task', 'status' => 'todo', 'priority' => 'normal', 'label' => 'Produk', 'assignee' => 'Tim Produk', 'date_offset' => 1],
            ['title' => 'Restock bahan baku kardus', 'description' => 'Cek stok gudang lalu pesan ulang ke supplier.', 'type' => 'restock', 'status' => 'todo', 'priority' => 'high', 'label' => 'Operasional', 'assignee' => 'Gudang', 'date_offset' => 3],
            ['title' => 'Packing pesanan pelanggan', 'description' => 'Packing ulang pesanan yang menunggu pengiriman.', 'type' => 'task', 'status' => 'in_progress', 'priority' => 'urgent', 'label' => 'Pesanan', 'assignee' => 'Tim Gudang', 'date_offset' => 0],
            ['title' => 'Kirim batch ke ekspedisi', 'description' => 'Serahkan paket harian ke kurir pickup.', 'type' => 'shipment', 'status' => 'in_progress', 'priority' => 'normal', 'label' => 'Pengiriman', 'assignee' => 'Admin Toko', 'date_offset' => 0],
            ['title' => 'Cek laporan penjualan mingguan', 'description' => 'Review omzet dan komparasi penjualan minggu lalu.', 'type' => 'reminder', 'status' => 'done', 'priority' => 'low', 'label' => 'Finance', 'assignee' => 'Pemilik', 'date_offset' => -1, 'completed' => true, 'proof_note' => 'Omzet naik 12% dibanding minggu sebelumnya, semua pesanan terkirim tepat waktu.', 'proof_files' => ['https://picsum.photos/seed/schedule-report/800/500']],
            ['title' => 'Update stok produk di katalog', 'description' => 'Sinkronkan stok katalog dengan data gudang.', 'type' => 'task', 'status' => 'done', 'priority' => 'normal', 'label' => 'Produk', 'assignee' => 'Tim Produk', 'date_offset' => -2, 'completed' => true, 'proof_note' => 'Stok sudah disesuaikan semua varian.', 'proof_files' => []],
        ];

        foreach (DB::table('stores')->whereIn('user_id', $owners)->get() as $store) {
            foreach ($kanbanTitles as $index => $card) {
                $uid = (string) $store->user_id;
                $date = $now->copy()->addDays((int) $card['date_offset'])->toDateString();
                $isCompleted = (bool) ($card['completed'] ?? false);

                DB::table('schedules')->updateOrInsert(
                    [
                        'user_id' => $uid,
                        'store_id' => $store->id,
                        'title' => $card['title'],
                    ],
                    [
                        'description' => $card['description'] ?? null,
                        'type' => $card['type'],
                        'status' => $card['status'],
                        'position' => $index,
                        'assignee' => $card['assignee'] ?? null,
                        'label' => $card['label'] ?? null,
                        'priority' => $card['priority'],
                        'color' => '',
                        'date' => $date,
                        'start_time' => null,
                        'end_time' => null,
                        'is_all_day' => true,
                        'is_completed' => $isCompleted,
                        'completed_at' => $isCompleted ? $now->copy()->subHours(6)->toDateTimeString() : null,
                        'completion_proof' => $isCompleted && ($card['proof_note'] ?? null)
                            ? json_encode(['note' => $card['proof_note'], 'files' => $card['proof_files'] ?? []])
                            : null,
                        'metadata' => json_encode(['seeded_by' => 'planner_kanban']),
                        'is_active' => true,
                        'created_by' => SeederIds::SUPER_ADMIN,
                        'updated_by' => SeederIds::SUPER_ADMIN,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]
                );
            }
        }
    }
}
