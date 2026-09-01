<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Wrapper around DemoStoreSeeder for the Akbar Fahlevy demo seller,
 * plus its unique schedules.
 *
 * IDEMPOTENT: re-running never duplicates existing data.
 */
final class AkbarFahlevySellerSeeder extends Seeder
{
    private const EMAIL = 'akbarfahlevy39@gmail.com';

    public function run(): void
    {
        $storeId = DemoStoreSeeder::run($this, [
            'user_id' => '00000000-0000-4000-8000-000000000302',
            'email' => self::EMAIL,
            'name' => 'Mochammad Rachman Akbar Fahlevy',
            'store_name' => 'Akbar Fahlevy Store',
            'store_slug' => 'akbar-fahlevy-store',
            'prefix' => 'FAHLEVY',
            'avatar_seed' => 'akbarfahlevy39@gmail.com',
            'count' => 30,
        ]);

        $this->ensureSchedules($storeId);
    }

    private function ensureSchedules(int $storeId): void
    {
        if (! Schema::hasTable('schedules')) {
            return;
        }

        $now = now();
        $sellerId = DB::table('stores')->where('id', $storeId)->value('user_id');

        $schedules = [
            ['title' => 'Cek Stok Gudang', 'type' => 'task', 'priority' => 'high', 'date' => $now->toDateString(), 'start_time' => '09:00:00', 'end_time' => '11:00:00'],
            ['title' => 'Meeting Supplier', 'type' => 'meeting', 'priority' => 'high', 'date' => $now->copy()->addDay()->toDateString(), 'start_time' => '14:00:00', 'end_time' => '15:30:00'],
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
                'description' => 'Jadwal untuk '.$schedule['title'],
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
                'metadata' => json_encode(['seeded' => true], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_by' => $sellerId,
                'updated_by' => $sellerId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);
        }
    }
}
