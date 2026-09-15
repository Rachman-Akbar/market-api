<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Banner, promotion, voucher, voucher pengguna, dan misi.
 */
final class DemoMarketingSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Demo: membangun banner, promo, voucher & misi...');

        $adminId = DemoIds::SUPER_ADMIN;
        $now = now()->toDateTimeString();
        $official = DemoState::storeId('marketku-official');

        foreach ([
            'sari-nusantara', 'raka-teknologi', 'kopi-nusantara', 'purnama-batik',
            'ananda-stationery', 'sentra-operasional',
        ] as $slug) {
            DemoState::storeId($slug);
        }

        // ── Banners (platform + per toko) ────────────────────────────────────
        if ($official) {
            $banners = [
                // [store_slug, name, seed, sort]
                ['marketku-official', 'Shopee Serba Bisa ala Marketku', 'hero-1', 1],
                ['marketku-official', 'Promo Kategori Elektronik', 'hero-2', 2],
                ['marketku-official', 'Batik Pilihan Pengrajin Nusantara', 'hero-3', 3],
                ['sari-nusantara', 'Sebulan Kemerdekaan Belanja Sari Nusantara', 'sari', 1],
                ['raka-teknologi', 'Gadget Gathering Raka Cell', 'raka', 1],
                ['kopi-nusantara', 'Launch Kopi Musang Kintamani', 'kopi', 1],
                ['purnama-batik', 'Batik Tulis Pilihan Oktober', 'purnama', 1],
            ];
            foreach ($banners as [$slug, $name, $seed, $sortOrder]) {
                $storeId = DemoState::storeId($slug);
                if (! $storeId) {
                    continue;
                }
                DB::table('banners')->insertOrIgnore([
                    'store_id' => $storeId,
                    'name' => $name,
                    'image_url' => 'https://picsum.photos/seed/banner-'.$seed.'/1200/480',
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ── Promotions ───────────────────────────────────────────────────────
        $promotions = [
            // [store_slug|null, name, action, target_id|null, sort, approval]
            [[null, 'Tampil Promo Utama Kategori Makanan', 'category', null, 1, 'approved']],
            [[null, 'Semarak HUT RI: Diskon Pengiriman', 'url', null, 2, 'approved']],
            [['raka-teknologi', 'Promo Elektronik Spesial Raka', 'product', null, 3, 'approved']],
            [['sari-nusantara', 'Gratis Ongkir Sari Nusantara', 'url', null, 4, 'pending']],
            [['purnama-batik', 'Batik Tulis Diskon 20%', 'product', null, 5, 'pending']],
            [['kopi-nusantara', 'Kopi Isi Ulang', 'url', null, 6, 'rejected']],
        ];
        foreach ($promotions as [$row]) {
            [$storeSlug, $name, $action, $targetId, $sortOrder, $approval] = $row;
            $storeId = $storeSlug ? DemoState::storeId($storeSlug) : $official;

            $submitted = now()->subDays(random_int(10, 40))->toDateTimeString();
            $owner = $storeSlug ? DemoState::storeOwner($storeSlug) : null;
            $data = [
                'store_id' => $storeId,
                'name' => $name,
                'image_url' => 'https://picsum.photos/seed/promo-'.$sortOrder.'/1200/480',
                'mobile_image_url' => 'https://picsum.photos/seed/promo-m-'.$sortOrder.'/480/480',
                'click_action' => $action,
                'target_id' => $targetId,
                'target_url' => $action === 'url' ? 'https://marketku.test/promo/'.$sortOrder : null,
                'sort_order' => $sortOrder,
                'is_active' => $approval === 'approved',
                'approval_status' => $approval,
                'rejection_reason' => $approval === 'rejected' ? 'Gambar tidak sesuai rasio banner yang diminta.' : null,
                'submitted_at' => $submitted,
                'approved_at' => $approval === 'approved' ? now()->subDays(random_int(3, 30))->toDateTimeString() : null,
                'approved_by' => $approval === 'approved' ? $adminId : null,
                'created_by' => $owner ?: $adminId,
                'updated_by' => $adminId,
                'created_at' => $submitted,
                'updated_at' => $now,
            ];
            DB::table('promotions')->insert($data);
        }

        // ── Vouchers ─────────────────────────────────────────────────────────
        $voucherData = [
            // [store_slug|null, code, name, scope, target, type, value, min_spend, max_discount, limit]
            [[null, 'WELCOME20', 'Voucher Baru Bergabung 20%', 'platform', 'product', 'percentage', 20, 50000, 20000, 100]],
            [[null, 'HEMAT5RB', 'Potongan Rp5.000 s/d 30 Juni', 'platform', 'product', 'fixed', 5000, 25000, null, 200]],
            [[null, 'GRATISONGKIR', 'Gratis Ongkir 10km pertama', 'platform', 'shipping', 'percentage', 100, 75000, 15000, 150]],
            [[null, 'SETIA10', 'Diskon 10% untuk member setia', 'platform', 'product', 'percentage', 10, 100000, 30000, 100]],
            [['sari-nusantara', 'SARINUS12', 'Diskon 12% Toko Sari Nusantara', 'store', 'product', 'percentage', 12, 40000, 15000, 50]],
            [['kopi-nusantara', 'KOPIPAGI', 'Diskon 8% Rumah Kopi Nusantara', 'store', 'product', 'percentage', 8, 30000, 10000, 50]],
        ];

        $voucherIds = [];
        foreach ($voucherData as [$row]) {
            [$storeSlug, $code, $name, $scope, $target, $type, $value, $minSpend, $maxDiscount, $limit] = $row;
            $storeId = $storeSlug ? DemoState::storeId($storeSlug) : null;

            $voucherIds[$code] = DB::table('vouchers')->insertGetId([
                'store_id' => $storeId,
                'voucher_scope' => $scope,
                'code' => $code,
                'name' => $name,
                'image' => 'https://picsum.photos/seed/voucher-'.strtolower($code).'/400/240',
                'discount_target' => $target,
                'discount_type' => $type,
                'discount_value' => $value,
                'min_spend' => $minSpend,
                'min_items' => 1,
                'min_distinct_products' => 1,
                'terms' => $type === 'percentage'
                    ? 'Berlaku untuk produk tertentu, maksimal diskon Rp'.number_format((float) ($maxDiscount ?? 0)).'.'
                    : 'Potongan langsung, minimal belanja Rp'.number_format((float) $minSpend).'.',
                'max_discount' => $maxDiscount,
                'starts_at' => now()->subDays(60)->toDateTimeString(),
                'ends_at' => now()->addDays(60)->toDateTimeString(),
                'usage_limit' => $limit,
                'used_count' => 0,
                'is_active' => true,
                'created_by' => $storeSlug ? (DemoState::storeOwner($storeSlug) ?: $adminId) : $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ── User vouchers (beberapa buyer klaim) ────────────────────────────
        $claimed = [
            [DemoIds::BUYER_2, 'WELCOME20', 'claim'],
            [DemoIds::BUYER_3, 'HEMAT5RB', 'referral'],
            [DemoIds::BUYER_4, 'GRATISONGKIR', 'claim'],
            [DemoIds::BUYER_5, 'SETIA10', 'membership'],
            [DemoIds::BUYER_6, 'SARINUS12', 'claim'],
            [DemoIds::BUYER_7, 'KOPIPAGI', 'claim'],
            [DemoIds::BUYER_8, 'WELCOME20', 'claim'],
        ];
        foreach ($claimed as [$userId, $code, $source]) {
            $voucherId = $voucherIds[$code] ?? null;
            if (! $voucherId) {
                continue;
            }
            DB::table('user_vouchers')->insertOrIgnore([
                'user_id' => $userId,
                'voucher_id' => $voucherId,
                'source_type' => $source,
                'source_id' => 'demo-'.$source.'-1',
                'status' => 'available',
                'claimed_at' => now()->subDays(random_int(1, 20))->toDateTimeString(),
                'used_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ── Missions + progress ──────────────────────────────────────────────
        $missions = [
            ['Misi Order Pertama', 'MISI-ORDER-1', 'order_completed', 1, 'Selesaikan 1 transaksi dalam 30 hari.'],
            ['Pembeli Aktif', 'MISI-ORDER-5', 'order_completed', 5, 'Selesaikan 5 transaksi dalam 30 hari.'],
            ['Periset Handal', 'MISI-REVIEW-3', 'review_submitted', 3, 'Tulis 3 ulasan untuk pesanan yang sudah selesai.'],
            ['Kolektor Wishlist', 'MISI-WISHLIST-10', 'wishlist_added', 10, 'Simpan 10 produk ke wishlist favoritmu.'],
        ];

        $missionIds = [];
        foreach ($missions as [$name, $code, $event, $target, $desc]) {
            $missionIds[$code] = DB::table('missions')->insertGetId([
                'name' => $name,
                'code' => $code,
                'description' => $desc,
                'event_type' => $event,
                'target_value' => $target,
                'conditions' => json_encode(['minimum_order_value' => 0]),
                'starts_at' => now()->subDays(60)->toDateTimeString(),
                'ends_at' => now()->addDays(60)->toDateTimeString(),
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $missionSpecs = [
            'MISI-ORDER-1' => 1,
            'MISI-ORDER-5' => 5,
            'MISI-REVIEW-3' => 3,
            'MISI-WISHLIST-10' => 10,
        ];

        foreach (DemoIds::BUYERS as $idx => $userId) {
            foreach ($missionIds as $code => $missionId) {
                $target = $missionSpecs[$code];
                $progress = $idx <= 1
                    ? ($idx === 1 ? $target : min($target, 1))
                    : random_int(0, $target);
                $completed = $progress >= $target;

                DB::table('mission_user_progress')->insert([
                    'mission_id' => $missionId,
                    'user_id' => $userId,
                    'progress_value' => $completed ? $target : $progress,
                    'status' => $completed ? 'completed' : 'in_progress',
                    'completed_at' => $completed ? now()->subDays(random_int(1, 15))->toDateTimeString() : null,
                    'rewarded_at' => $completed ? now()->subDays(random_int(1, 10))->toDateTimeString() : null,
                    'reward_voucher_id' => $completed && $code === 'MISI-ORDER-1' ? ($voucherIds['WELCOME20'] ?? null) : null,
                    'metadata' => json_encode(['progress_at' => now()->toDateTimeString()]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->command?->info('Demo marketing selesai: '.count($voucherIds).' voucher, ter-klaim '.count($claimed).'x.');
    }
}