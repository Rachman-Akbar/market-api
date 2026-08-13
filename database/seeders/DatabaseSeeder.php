<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminSeeder::class,
            SellerSeeder::class,
            CatalogSeeder::class,
            ProductSeeder::class,
            MarketingSeeder::class,
            MarketplaceFakerSeeder::class,
            AdvancedMarketplaceSeeder::class,
        ]);
    }
}
