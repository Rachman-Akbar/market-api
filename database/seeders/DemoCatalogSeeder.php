<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Katalog platform: 6 kelompok produk + pohon kategori (3 level) +
 * atribut produk + konfigurasi biaya admin per kategori.
 */
final class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Demo: membangun katalog & kategori...');

        $adminId = DemoIds::SUPER_ADMIN;
        $now = now()->toDateTimeString();

        // ── Catalog groups ───────────────────────────────────────────────────
        $groups = [
            ['Makanan & Minuman', 'makanan-minuman', 10],
            ['Rumah Tangga', 'rumah-tangga', 20],
            ['Elektronik & Gadget', 'elektronik-gadget', 30],
            ['Fesyen & Batik', 'fesyen-batik', 40],
            ['Alat Tulis & Kerajinan', 'alat-tulis-kerajinan', 50],
            ['Kesehatan & Kecantikan', 'kesehatan-kecantikan', 60],
        ];

        $groupIds = [];
        foreach ($groups as [$name, $slug, $sort]) {
            $groupIds[$slug] = DB::table('catalog_groups')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ── Categories (nested: level -> children) ───────────────────────────
        // Struktur: slug => [name, [children]], anak berupa slug=>name atau slug=>[name,[children]]
        $tree = [
            'makanan-minuman' => [
                'makanan' => ['Makanan', 'makanan', [
                    'makanan-ringan' => ['Makanan Ringan', [
                        'keripik' => 'Keripik & Snack',
                        'kue-kering' => 'Kue Kering',
                        'cokelat' => 'Cokelat & Permen',
                    ]],
                    'makanan-siap-saji' => ['Makanan Siap Saji', [
                        'bakso' => 'Bakso',
                        'sate' => 'Sate',
                        'ayam-goreng' => 'Ayam Goreng',
                    ]],
                    'minuman' => ['Minuman', [
                        'kopi' => 'Kopi',
                        'teh' => 'Teh',
                        'sirup-jus' => 'Sirup & Jus',
                    ]],
                ]],
            ],
            'rumah-tangga' => [
                'rumah' => ['Rumah', 'rumah', [
                    'kebersihan' => ['Kebersihan', [
                        'pel-lap' => 'Pel & Lap',
                        'sapu-siku' => 'Sapu & Sikat',
                        'tempat-sampah' => 'Tempat Sampah',
                    ]],
                    'dekorasi' => ['Dekorasi', [
                        'lilin-aroma' => 'Lilin & Pengharum',
                        'vas-pot' => 'Vas & Pot',
                        'jam-dinding' => 'Jam Dinding',
                    ]],
                ]],
            ],
            'elektronik-gadget' => [
                'elektronik' => ['Elektronik', 'elektronik', [
                    'audio' => ['Audio', [
                        'speaker' => 'Speaker',
                        'headphone' => 'Headphone & Earphone',
                    ]],
                    'aksesori-gadget' => ['Aksesori Gadget', [
                        'charger-kabel' => 'Charger & Kabel',
                        'powerbank' => 'Powerbank',
                        'casing-pelindung' => 'Casing & Pelindung',
                    ]],
                    'perangkat-lain' => ['Perangkat Lain', [
                        'lampu-led' => 'Lampu LED',
                        'adaptor' => 'Adaptor & Converter',
                    ]],
                ]],
            ],
            'fesyen-batik' => [
                'pakaian-pria' => ['Pakaian Pria', 'pakaian-pria', [
                    'kemeja-pria' => ['Kemeja Pria', [
                        'kemeja-batik' => 'Kemeja Batik',
                        'kemeja-formal' => 'Kemeja Formal',
                    ]],
                    'kaos-pria' => ['Kaos Pria', [
                        'kaos-polos' => 'Kaos Polos',
                        'kaos-batik' => 'Kaos Batik',
                    ]],
                ]],
                'pakaian-wanita' => ['Pakaian Wanita', 'pakaian-wanita', [
                    'atasan-wanita' => ['Atasan Wanita', [
                        'blouse' => 'Blouse & Kemeja',
                        'kebaya' => 'Kebaya & Bahan Wanita',
                    ]],
                    'bawahan-wanita' => ['Bawahan Wanita', [
                        'rok' => 'Rok',
                        'celana-wanita' => 'Celana Wanita',
                    ]],
                ]],
                'batik-kain' => ['Batik & Kain', 'batik-kain', [
                    'batik' => ['Batik', [
                        'batik-tulis' => 'Batik Tulis',
                        'batik-cap' => 'Batik Cap',
                        'batik-printing' => 'Batik Printing',
                    ]],
                    'kain-tradisional' => ['Kain Tradisional', [
                        'kain-tenun' => 'Kain Tenun',
                        'kain-lurik' => 'Kain Lurik',
                    ]],
                ]],
            ],
            'alat-tulis-kerajinan' => [
                'alat-tulis' => ['Alat Tulis', 'alat-tulis', [
                    'menulis' => ['Menulis', [
                        'pulpen' => 'Pulpen',
                        'pensil' => 'Pensil & Pensil Warna',
                        'spidol' => 'Spidol & Highlighter',
                    ]],
                    'kertas-buku' => ['Kertas & Buku', [
                        'buku-catatan' => 'Buku Catatan',
                        'kertas-hvs' => 'Kertas HVS',
                        'kertas-kado' => 'Kertas Kado',
                    ]],
                ]],
                'kerajinan' => ['Kerajinan', 'kerajinan', [
                    'stiker-tape' => ['Stiker & Tape', [
                        'stiker' => 'Stiker',
                        'washi-tape' => 'Washi Tape',
                    ]],
                    'bahan-kerajinan' => ['Bahan Kerajinan', [
                        'papercraft' => 'Kertas Kerajinan (Papercraft)',
                        'pita-manik' => 'Pita & Manik',
                    ]],
                ]],
            ],
            'kesehatan-kecantikan' => [
                'kesehatan' => ['Kesehatan', 'kesehatan', [
                    'perawatan-tubuh' => ['Perawatan Tubuh', [
                        'sabun-body-care' => 'Sabun & Body Care',
                        'suplemen' => 'Suplemen & Vitamin',
                    ]],
                ]],
                'kecantikan' => ['Kecantikan', 'kecantikan', [
                    'perawatan-kulit' => ['Perawatan Kulit', [
                        'serum-skincare' => 'Serum & Skincare',
                        'sunscreen' => 'Sunscreen',
                    ]],
                    'perawatan-rambut' => ['Perawatan Rambut', [
                        'shampoo' => 'Shampoo',
                        'minyak-rambut' => 'Minyak Rambut',
                    ]],
                ]],
            ],
        ];

        $leafIds = [];
        foreach ($tree as $groupSlug => $roots) {
            $groupId = $groupIds[$groupSlug];
            $sort = 10;
            foreach ($roots as $rootSlug => [$rootName, $rootSlugName, $l2s]) {
                $rootId = DB::table('categories')->insertGetId([
                    'catalog_group_id' => $groupId,
                    'parent_id' => null,
                    'parent_scope_id' => 0,
                    'level' => 1,
                    'sort_order' => $sort,
                    'is_active' => true,
                    'is_visible_in_menu' => true,
                    'name' => $rootName,
                    'slug' => $rootSlugName,
                    'full_slug' => $rootSlugName,
                    'image_url' => null,
                    'icon_url' => null,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $sort += 10;

                $childSort = 10;
                foreach ($l2s as $l2Slug => [$l2Name, $leaves]) {
                    $l2Id = DB::table('categories')->insertGetId([
                        'catalog_group_id' => $groupId,
                        'parent_id' => $rootId,
                        'parent_scope_id' => $rootId,
                        'level' => 2,
                        'sort_order' => $childSort,
                        'is_active' => true,
                        'is_visible_in_menu' => true,
                        'name' => $l2Name,
                        'slug' => $l2Slug,
                        'full_slug' => $rootSlugName.'/'.$l2Slug,
                        'image_url' => null,
                        'icon_url' => null,
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $childSort += 10;

                    $leafSort = 10;
                    foreach ($leaves as $leafSlug => $leafName) {
                        $leafIds[$leafSlug] = DB::table('categories')->insertGetId([
                            'catalog_group_id' => $groupId,
                            'parent_id' => $l2Id,
                            'parent_scope_id' => $l2Id,
                            'level' => 3,
                            'sort_order' => $leafSort,
                            'is_active' => true,
                            'is_visible_in_menu' => true,
                            'name' => $leafName,
                            'slug' => $leafSlug,
                            'full_slug' => $rootSlugName.'/'.$l2Slug.'/'.$leafSlug,
                            'created_by' => $adminId,
                            'updated_by' => $adminId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                        $leafSort += 10;
                    }
                }
            }
        }

        DemoState::set('category_leaf_ids', $leafIds);

        // ── Admin fee configs ────────────────────────────────────────────────
        $feeCategories = ['keripik', 'kue-kering', 'bakso', 'sate', 'ayam-goreng', 'kopi', 'teh', 'speaker', 'headphone',
            'charger-kabel', 'powerbank', 'kemeja-batik', 'kaos-batik', 'blouse', 'batik-tulis', 'batik-cap',
            'pulpen', 'pensil', 'buku-catatan', 'stiker', 'serum-skincare', 'shampoo'];
        $sort = 10;
        foreach ($feeCategories as $slug) {
            $categoryId = $leafIds[$slug] ?? null;
            DB::table('admin_fee_configs')->insert([
                'category_id' => $categoryId,
                'name' => 'Biaya layanan '.ucfirst(str_replace('-', ' ', $slug)),
                'code' => 'fee-'.$slug,
                'percentage' => 2.5,
                'fixed_amount' => 0,
                'min_fee' => 500,
                'max_fee' => 25000,
                'is_active' => true,
                'description' => 'Biaya layanan platform untuk transaksi kategori ini.',
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $sort += 10;
        }

        // ── Product attributes ───────────────────────────────────────────────
        $attributes = [
            ['name' => 'Warna', 'slug' => 'warna'],
            ['name' => 'Ukuran', 'slug' => 'ukuran'],
            ['name' => 'Kapasitas', 'slug' => 'kapasitas'],
            ['name' => 'Berat', 'slug' => 'berat'],
        ];
        foreach ($attributes as $attr) {
            DB::table('product_attributes')->insertOrIgnore([
                'name' => $attr['name'],
                'slug' => $attr['slug'],
                'type' => 'select',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command?->info('Demo catalog selesai: '.count($groupIds).' group, '.count($leafIds).' kategori leaf.');
    }
}