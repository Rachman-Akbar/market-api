<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Wrapper around DemoStoreSeeder for the Budi demo seller.
 */
final class BudiSellerPanelSeeder extends Seeder
{
    public function run(): void
    {
        DemoStoreSeeder::run($this, [
            'user_id' => SeederIds::SUPER_ADMIN,
            'email' => 'budi@gmail.com',
            'name' => 'Budi Administrator',
            'store_name' => 'budi marketplace lab',
            'store_slug' => 'budi-marketplace-lab',
            'prefix' => 'BUDI',
            'avatar_seed' => 'budi@gmail.com',
            'count' => 50,
        ]);
    }
}
