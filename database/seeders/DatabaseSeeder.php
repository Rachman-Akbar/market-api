<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orkestrasi seluruh data demo Marketku.
 *
 * Urutan penting: (1) Role & permission, (2) user/toko/alamat, (3) katalog,
 * (4) marketing, (5) produk + inventori, (6) transaksi/komersial, (7) PPOB.
 * Semua akun memakai password DemoIds::PASSWORD ('12345678').
 */
final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(DemoUsersAndStoresSeeder::class);
        $this->call(DemoCatalogSeeder::class);
        $this->call(DemoMarketingSeeder::class);
        $this->call(DemoProductsAndInventorySeeder::class);
        $this->call(DemoCommerceSeeder::class);
        $this->call(PpobCatalogSeeder::class);
        $this->call(GameContentSeeder::class);
        $this->call(DailyMissionSeeder::class);
    }
}