<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $storeIds = DB::table('stores')->pluck('id', 'slug');
        $productIds = DB::table('products')->pluck('id', 'slug');
        $categoryIds = DB::table('categories')->pluck('id', 'slug');

        $banners = [
            ['store' => 'sari-nusantara', 'name' => 'pilihan sari nusantara', 'seed' => 'banner-sari-1', 'sort' => 10],
            ['store' => 'sari-nusantara', 'name' => 'belanja rumah lebih nyaman', 'seed' => 'banner-sari-2', 'sort' => 20],
            ['store' => 'raka-teknologi', 'name' => 'teknologi untuk setiap hari', 'seed' => 'banner-raka-1', 'sort' => 10],
        ];

        foreach ($banners as $banner) {
            $storeId = $storeIds[$banner['store']] ?? null;

            if (! $storeId) {
                continue;
            }

            $creator = $banner['store'] === 'sari-nusantara' ? SeederIds::SELLER_ONE : SeederIds::SELLER_TWO;
            DB::table('banners')->updateOrInsert(
                ['store_id' => $storeId, 'name' => $banner['name']],
                [
                    'image_url' => 'https://picsum.photos/seed/'.$banner['seed'].'/1600/500',
                    'sort_order' => $banner['sort'],
                    'is_active' => true,
                    'created_by' => $creator,
                    'updated_by' => $creator,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
        }

        $promotions = [
            [
                'store_id' => null,
                'name' => 'promo marketplace pilihan minggu ini',
                'image_url' => 'https://picsum.photos/seed/promotion-platform-1/1600/500',
                'mobile_image_url' => 'https://picsum.photos/seed/promotion-platform-1-mobile/900/900',
                'click_action' => 'category',
                'target_id' => $categoryIds['sate'] ?? null,
                'target_url' => null,
                'sort_order' => 10,
                'is_active' => true,
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'submitted_at' => $now,
                'approved_at' => $now,
                'approved_by' => SeederIds::SUPER_ADMIN,
                'created_by' => SeederIds::SUPER_ADMIN,
            ],
            [
                'store_id' => $storeIds['raka-teknologi'] ?? null,
                'name' => 'speaker bluetooth pilihan raka',
                'image_url' => 'https://picsum.photos/seed/promotion-store-approved/1600/500',
                'mobile_image_url' => 'https://picsum.photos/seed/promotion-store-approved-mobile/900/900',
                'click_action' => 'product',
                'target_id' => $productIds['speaker-bluetooth-mini'] ?? null,
                'target_url' => null,
                'sort_order' => 20,
                'is_active' => true,
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'submitted_at' => $now->copy()->subDay(),
                'approved_at' => $now,
                'approved_by' => SeederIds::SUPER_ADMIN,
                'created_by' => SeederIds::SELLER_TWO,
            ],
            [
                'store_id' => $storeIds['sari-nusantara'] ?? null,
                'name' => 'sate madura promo seller pending',
                'image_url' => 'https://picsum.photos/seed/promotion-store-pending/1600/500',
                'mobile_image_url' => null,
                'click_action' => 'product',
                'target_id' => $productIds['sate-ayam-madura'] ?? null,
                'target_url' => null,
                'sort_order' => 30,
                'is_active' => true,
                'approval_status' => 'pending',
                'rejection_reason' => null,
                'submitted_at' => $now,
                'approved_at' => null,
                'approved_by' => null,
                'created_by' => SeederIds::SELLER_ONE,
            ],
        ];

        foreach ($promotions as $promotion) {
            if ($promotion['store_id'] !== null && ! DB::table('stores')->where('id', $promotion['store_id'])->exists()) {
                continue;
            }

            DB::table('promotions')->updateOrInsert(
                ['name' => $promotion['name']],
                [
                    ...$promotion,
                    'updated_by' => $promotion['created_by'],
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
        }

        $vouchers = [
            [
                'store_id' => null,
                'voucher_scope' => 'platform',
                'code' => 'welcome10',
                'name' => 'diskon pengguna baru',
                'discount_target' => 'product',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'min_spend' => 100000,
                'max_discount' => 50000,
                'created_by' => SeederIds::SUPER_ADMIN,
            ],
            [
                'store_id' => null,
                'voucher_scope' => 'platform',
                'code' => 'gratisongkir',
                'name' => 'gratis ongkir platform',
                'discount_target' => 'shipping',
                'discount_type' => 'percentage',
                'discount_value' => 100,
                'min_spend' => 150000,
                'max_discount' => 30000,
                'created_by' => SeederIds::SUPER_ADMIN,
            ],
            [
                'store_id' => $storeIds['sari-nusantara'] ?? null,
                'voucher_scope' => 'store',
                'code' => 'sarihemat25',
                'name' => 'hemat belanja toko sari',
                'discount_target' => 'product',
                'discount_type' => 'fixed',
                'discount_value' => 25000,
                'min_spend' => 100000,
                'max_discount' => null,
                'created_by' => SeederIds::SELLER_ONE,
            ],
            [
                'store_id' => $storeIds['raka-teknologi'] ?? null,
                'voucher_scope' => 'store',
                'code' => 'rakaongkir100',
                'name' => 'gratis ongkir toko raka',
                'discount_target' => 'shipping',
                'discount_type' => 'percentage',
                'discount_value' => 100,
                'min_spend' => 150000,
                'max_discount' => 25000,
                'min_items' => 3,
                'min_distinct_products' => null,
                'terms' => 'Berlaku minimal 3 item pada toko Raka Teknologi.',
                'created_by' => SeederIds::SELLER_TWO,
            ],
            [
                'store_id' => null,
                'voucher_scope' => 'platform',
                'code' => 'gamefun10',
                'name' => 'voucher hadiah bermain game',
                'discount_target' => 'product',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'min_spend' => 75000,
                'max_discount' => 25000,
                'min_items' => 2,
                'min_distinct_products' => 1,
                'terms' => 'Dapat diperoleh dengan menyelesaikan misi harian bermain game di aplikasi. Berlaku untuk belanja minimal Rp75.000 dalam 1 transaksi.',
                'created_by' => SeederIds::SUPER_ADMIN,
            ],
        ];

        foreach ($vouchers as $voucher) {
            if ($voucher['voucher_scope'] === 'store' && ! $voucher['store_id']) {
                continue;
            }

            DB::table('vouchers')->updateOrInsert(
                ['code' => $voucher['code']],
                [
                    ...$voucher,
                    'image' => 'https://picsum.photos/seed/voucher-'.$voucher['code'].'/800/500',
                    'starts_at' => $now->copy()->subWeek(),
                    'ends_at' => $now->copy()->addMonths(6),
                    'usage_limit' => 1000,
                    'used_count' => 0,
                    'is_active' => true,
                    'updated_by' => $voucher['created_by'],
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
        }
    }
}
