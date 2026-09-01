<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Wrapper around DemoStoreSeeder for the Ahmad "Market Akbar" demo seller,
 * plus its unique schedules and admin fee configs.
 */
final class MarketAkbarSeeder extends Seeder
{
    private const MARKET_AKBAR_USER_ID = '00000000-0000-4000-8000-000000000301';

    public function run(): void
    {
        $storeId = DemoStoreSeeder::run($this, [
            'user_id' => self::MARKET_AKBAR_USER_ID,
            'email' => 'market.akbar@gmail.com',
            'name' => 'Ahmad Market Akbar',
            'store_name' => 'Market Akbar',
            'store_slug' => 'market-akbar',
            'prefix' => 'AKBAR',
            'avatar_seed' => 'market.akbar@gmail.com',
            'count' => 50,
        ]);

        $this->createSchedules($storeId);
        $this->createAdminFeeConfigs();
    }

    private function createSchedules(int $storeId): void
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
            ['title' => 'Buka Toko', 'type' => 'task', 'priority' => 'low', 'date' => $now->toDateString(), 'start_time' => '08:00:00', 'end_time' => '08:30:00', 'is_all_day' => false],
        ];

        foreach ($schedules as $schedule) {
            $createdAt = $now->copy()->subDays(rand(0, 5));

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
                'is_all_day' => $schedule['is_all_day'] ?? false,
                'is_completed' => $schedule['is_completed'] ?? false,
                'completed_at' => ($schedule['is_completed'] ?? false) ? $createdAt->copy()->addHour() : null,
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

    private function createAdminFeeConfigs(): void
    {
        if (! Schema::hasTable('admin_fee_configs')) {
            return;
        }

        $now = now();
        $adminId = DB::table('users')->where('id', SeederIds::SUPER_ADMIN)->value('id')
            ?? DB::table('users')->orderBy('created_at')->value('id');

        $configs = [
            ['name' => 'Default Marketplace Fee', 'code' => 'default', 'percentage' => 5.00, 'fixed_amount' => 0, 'category_slug' => null],
            ['name' => 'Fee Makanan & Minuman', 'code' => 'food-beverage', 'percentage' => 4.00, 'fixed_amount' => 500, 'category_slug' => 'makanan'],
            ['name' => 'Fee Elektronik', 'code' => 'electronics', 'percentage' => 6.00, 'fixed_amount' => 0, 'category_slug' => 'elektronik'],
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
                    'description' => 'Konfigurasi fee untuk '.$config['name'],
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
        }
    }
}
