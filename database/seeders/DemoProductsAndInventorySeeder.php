<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Katalog produk 5 toko seller + toko operasional.
 * Toko operasional juga mendapat data bahan baku, resep (product_materials),
 * HPP (product_costings), riwayat harga bahan (raw_material_cost_histories),
 * serta mutasi stok produk.
 */
final class DemoProductsAndInventorySeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Demo: membuat produk, varian, showcase & stok...');

        $adminId = DemoIds::SUPER_ADMIN;
        $now = now()->toDateTimeString();
        $leafIds = DemoState::get('category_leaf_ids', []);

        $productsByStore = [];
        $variantList = [];

        $catalog = [
            'sari-nusantara' => [
                1 => ['Keripik Singkong Balado', 'keripik-singkong-balado', 'keripik', 'Sari Nusantara', 'Keripik singkong gurih bumbu balado, tanpa pengawet.', [
                    'Original 100g' => [13000, 34, 8],
                    'Balado 100g' => [14000, 22, 8],
                ], true, true],
                2 => ['Keripik Pisang Cokelat', 'keripik-pisang-cokelat', 'keripik', 'Sari Nusantara', 'Keripik pisang renyah lapisan cokelat premium.', [
                    '125g' => [15000, 40, 6],
                ], true, false],
                3 => ['Kue Kering Nastar', 'kue-kering-nastar', 'kue-kering', 'Sari Nusantara', 'Nastar lembut dengan selai nanas asli, kemasan toples 250g.', [
                    'Toples 250g' => [42000, 12, 3],
                ], true, true],
                4 => ['Kue Kering Kastengel', 'kue-kering-kastengel', 'kue-kering', 'Sari Nusantara', 'Kastengel renyah taburan keju edam.', [
                    'Toples 250g' => [45000, 9, 3],
                ], false, false],
                5 => ['Cokelat Batik Mini', 'cokelat-batik-mini', 'cokelat', 'Sari Nusantara', 'Cokelat mini motif batik, cocok untuk hantaran.', [
                    'Isi 12 pcs' => [25000, 28, 5],
                ], false, false],
                6 => ['Permen Jahe Jadul', 'permen-jahe-jadul', 'cokelat', 'Sari Nusantara', 'Permen jahe legendaris, hangat di tenggorokan.', [
                    'Isi 20 pcs' => [8000, 50, 10],
                ], false, false],
                7 => ['Oleh-Oleh Pisang Goreng Pasir', 'pisang-goreng-pasir', 'kue-kering', 'Sari Nusantara', 'Bekal oleh-oleh khas, renyah dan manis.', [
                    'Box 500g' => [32000, 18, 4],
                ], false, true],
                8 => ['Sari Kelapa Muda Botol', 'sari-kelapa-muda', 'sirup-jus', 'Sari Nusantara', 'Minuman sari kelapa muda segar kemasan botol 300ml.', [
                    '300ml x 6' => [45000, 25, 5],
                ], false, false],
            ],
            'raka-teknologi' => [
                1 => ['Smart Speaker Mini Bass', 'smart-speaker-mini', 'speaker', 'Raka Audio', 'Speaker pintar bluetooth dengan bass solid dan mikrofon jernih.', [
                    'Hitam' => [249000, 15, 3],
                    'Putih' => [249000, 12, 3],
                ], true, true],
                2 => ['TWS Earbuds Pro', 'tws-earbuds-pro', 'headphone', 'Raka Audio', 'Earbuds nirkabel dengan noise reduction dan game mode.', [
                    'Putih' => [199000, 25, 5],
                    'Hitam' => [199000, 8, 5],
                ], true, false],
                3 => ['Headphone Over-Ear Studio', 'headphone-over-ear', 'headphone', 'Raka Audio', 'Headphone studio dengan driver 40mm dan busa empuk.', [
                    'Hitam' => [349000, 7, 2],
                ], false, false],
                4 => ['Kabel Data Type-C Nylon', 'kabel-data-typec', 'charger-kabel', 'Raka Audio', 'Kabel data type-c nylon tahan lama, fast charging.', [
                    '0.5m' => [25000, 60, 10],
                    '1m' => [30000, 45, 10],
                    '2m' => [40000, 30, 10],
                ], true, true],
                5 => ['Adaptor Charger 45W GaN', 'adaptor-charger-45w', 'charger-kabel', 'Raka Audio', 'Charger GaN cepat 45W dual port.', [
                    'Putih' => [185000, 20, 4],
                ], false, false],
                6 => ['Powerbank 10000mAh Slim', 'powerbank-10000', 'powerbank', 'Raka Audio', 'Powerbank tipis 10000mAh dengan indikator LED.', [
                    'Silver' => [159000, 18, 3],
                ], false, false],
                7 => ['Powerbank 20000mAh Fast', 'powerbank-20000', 'powerbank', 'Raka Audio', 'Powerbank 20000mAh dengan fast charging 22.5W.', [
                    'Hitam' => [259000, 10, 3],
                ], true, false],
                8 => ['Case HP Silikon Transparan', 'case-hp-silikon', 'casing-pelindung', 'Raka Audio', 'Case silikon anti gores, ringan dan pas di genggaman.', [
                    'Bening' => [35000, 50, 15],
                    'Hitam' => [35000, 15, 15],
                ], false, false],
            ],
            'kopi-nusantara' => [
                1 => ['Kopi Arabika Gayo 250g', 'kopi-arabika-gayo', 'kopi', 'Rumah Kopi Nusantara', 'Arabika Gayo rasa cokelat-karamel, full body.', [
                    'Biji' => [85000, 20, 4],
                    'Giling' => [85000, 16, 4],
                ], true, true],
                2 => ['Kopi Kintamani 250g', 'kopi-kintamani', 'kopi', 'Rumah Kopi Nusantara', 'Kintamani terang dengan aroma citrus khas dataran tinggi Bali.', [
                    'Biji' => [80000, 18, 4],
                    'Giling' => [80000, 5, 4],
                ], false, false],
                3 => ['Kopi Toraja 250g', 'kopi-toraja', 'kopi', 'Rumah Kopi Nusantara', 'Toraja dengan aftertaste rempah dan earthy.', [
                    'Biji' => [95000, 14, 3],
                ], true, false],
                4 => ['Kopi Lanang Jawa 250g', 'kopi-lanang-jawa', 'kopi', 'Rumah Kopi Nusantara', 'Kopi lanang robusta Java premium.', [
                    'Biji' => [120000, 10, 2],
                ], false, false],
                5 => ['Teh Melati Premium 50 Kantong', 'teh-melati-premium', 'teh', 'Rumah Kopi Nusantara', 'Teh melati pilihan dengan wangi bunga yang lembut.', [
                    '50 Kantong' => [55000, 30, 6],
                ], false, true],
                6 => ['Teh Sari Daun Gambir', 'teh-sari-gambir', 'teh', 'Rumah Kopi Nusantara', 'Teh herbal daun gambir berkhasiat.', [
                    'Box 25 Kantong' => [38000, 22, 5],
                ], false, false],
                7 => ['Kopi Tubruk Sachet', 'kopi-tubruk-sachet', 'kopi', 'Rumah Kopi Nusantara', 'Kopi tubruk siap seduh dalam sachet praktis.', [
                    'Box 20 Sachet' => [42000, 40, 8],
                ], false, false],
                8 => ['Sirup Gula Aren Premium', 'sirup-gula-aren', 'sirup-jus', 'Rumah Kopi Nusantara', 'Sirup gula aren asli untuk pendamping kopi dan es.', [
                    'Botol 500ml' => [35000, 26, 5],
                ], false, false],
            ],
            'purnama-batik' => [
                1 => ['Kemeja Batik Tulis Solo', 'kemeja-batik-tulis-solo', 'kemeja-batik', 'Purnama Batik', 'Kemeja batik tulis Sogan asli Solo, jahitan rapi.', [
                    'M' => [285000, 6, 2],
                    'L' => [285000, 12, 2],
                    'XL' => [285000, 4, 2],
                ], true, true],
                2 => ['Kemeja Batik Cap Mega Mendung', 'kemeja-batik-cap', 'kemeja-batik', 'Purnama Batik', 'Batik cap Mega Mendung Cirebon, warna tajam.', [
                    'L' => [195000, 15, 3],
                    'XL' => [195000, 8, 3],
                ], false, false],
                3 => ['Kaos Batik Printing Pria', 'kaos-batik-printing', 'kaos-batik', 'Purnama Batik', 'Kaos batik printing motif kontemporer, bahan cotton combed.', [
                    'S' => [75000, 20, 5],
                    'M' => [75000, 18, 5],
                    'L' => [75000, 6, 5],
                ], true, false],
                4 => ['Kaos Polos Katun Combed', 'kaos-polos-katun', 'kaos-polos', 'Purnama Batik', 'Kaos polos katun combed 30s, nyaman dipakai.', [
                    'M' => [65000, 30, 5],
                    'L' => [65000, 22, 5],
                    'XL' => [65000, 10, 5],
                ], false, false],
                5 => ['Blouse Batik Modern', 'blouse-batik-modern', 'blouse', 'Purnama Batik', 'Blouse batik modern flowy untuk acara formal dan santai.', [
                    'M' => [165000, 12, 3],
                    'L' => [165000, 9, 3],
                ], false, true],
                6 => ['Kebaya Encim Batik Pekalongan', 'kebaya-encim', 'kebaya', 'Purnama Batik', 'Kebaya encim dengan detail payet dan batik Pekalongan.', [
                    'M' => [385000, 5, 1],
                    'L' => [385000, 7, 1],
                ], false, false],
                7 => ['Kain Batik Tulis 2 Meter', 'kain-batik-tulis', 'batik-tulis', 'Purnama Batik', 'Kain batik tulis halus, cocok untuk busana custom.', [
                    '2 Meter' => [425000, 3, 1],
                ], true, true],
                8 => ['Kain Lurik Asli Klaten', 'kain-lurik-klaten', 'kain-lurik', 'Purnama Batik', 'Lurik tenun tangan asli Klaten, motif klasik.', [
                    '1.5 Meter' => [95000, 16, 3],
                ], false, false],
            ],
            'ananda-stationery' => [
                1 => ['Pulpen Gel JC Style 0.5 mm', 'pulpen-gel-jc', 'pulpen', 'Ananda', 'Pulpen gel tinta licin, pack isi 12.', [
                    'Pack 12 Hitam' => [36000, 45, 10],
                    'Pack 12 Biru' => [36000, 8, 10],
                ], true, true],
                2 => ['Pulpen Standard AE7', 'pulpen-standard-ae7', 'pulpen', 'Ananda', 'Pulpen standard klasik, tinta awet.', [
                    'Pack 10' => [28000, 35, 8],
                ], false, false],
                3 => ['Pensil Kayu 2B (Pack 12)', 'pensil-2b-pack', 'pensil', 'Ananda', 'Pensil kayu premium hitam 2B.', [
                    'Pack 12' => [24000, 40, 8],
                ], false, false],
                4 => ['Buku Catatan Hardcover A5', 'buku-catatan-a5', 'buku-catatan', 'Ananda', 'Buku catatan hardcover A5 80 halaman grid.', [
                    'Hitam' => [18000, 50, 12],
                    'Biru' => [18000, 42, 12],
                    'Merah' => [18000, 0, 5],
                ], true, true],
                5 => ['Kertas HVS A4 70gsm', 'kertas-hvs-a4', 'kertas-hvs', 'Ananda', 'Kertas HVS A4 70gsm, 1 rim 500 lembar.', [
                    '1 Rim' => [52000, 20, 5],
                ], false, false],
                6 => ['Kertas Kado Motif (Pack 10)', 'kertas-kado-motif', 'kertas-kado', 'Ananda', 'Kertas kado aneka motif premium ukuran 70x100cm.', [
                    'Pack 10' => [30000, 25, 6],
                ], false, false],
                7 => ['Stiker Label Aneka Ukuran', 'stiker-label', 'stiker', 'Ananda', 'Stiker label putih untuk barcode dan keperluan usaha.', [
                    '1 Pack 100 lbr' => [22000, 30, 8],
                ], false, false],
                8 => ['Washi Tape Set 10 pcs', 'washi-tape-10', 'washi-tape', 'Ananda', 'Washi tape motif 10 pcs untuk dekorasi scrapbook.', [
                    'Set 10' => [32000, 18, 4],
                ], false, false],
            ],
            'sentra-operasional' => [
                1 => ['Paket Nasi Ayam Goreng', 'nasi-ayam-goreng', 'ayam-goreng', 'Sentra Ops', 'Paket nasi ayam goreng lengkap: nasi, ayam, sambal, sayur.', [
                    'Reguler' => [18000, 5, 3],
                    'Plus Es Teh' => [22000, 7, 3],
                ], true, true],
                2 => ['Bakso Sapi Original 500g', 'bakso-sapi-500g', 'bakso', 'Sentra Ops', 'Bakso sapi tanpa pengawet, tekstur kenyal.', [
                    'Pack 500g' => [52000, 8, 3],
                    'Pack 1kg' => [98000, 4, 2],
                ], true, false],
                3 => ['Sate Ayam Maranggi (12 Tusuk)', 'sate-ayam-maranggi', 'sate', 'Sentra Ops', 'Sate ayam maranggi siap masak dengan bumbu lengkap.', [
                    '12 Tusuk' => [38000, 6, 2],
                    '24 Tusuk' => [72000, 3, 2],
                ], false, false],
            ],
        ];

        // ── Produk biasa: 5 toko seller ─────────────────────────────────────
        $storeIds = [
            'sari-nusantara' => DemoState::storeId('sari-nusantara'),
            'raka-teknologi' => DemoState::storeId('raka-teknologi'),
            'kopi-nusantara' => DemoState::storeId('kopi-nusantara'),
            'purnama-batik' => DemoState::storeId('purnama-batik'),
            'ananda-stationery' => DemoState::storeId('ananda-stationery'),
            'sentra-operasional' => DemoState::storeId('sentra-operasional'),
        ];

        foreach (['sari-nusantara', 'raka-teknologi', 'kopi-nusantara', 'purnama-batik', 'ananda-stationery'] as $storeSlug) {
            $storeId = $storeIds[$storeSlug];
            $products = $catalog[$storeSlug];
            $created = $this->insertProducts($storeId, $storeSlug, $products, $leafIds, $variantList, $adminId, $now);

            $this->createShowcases($storeId, $created, $adminId, $now);
            $productsByStore[$storeSlug] = $created;
        }

        // ── Toko operasional: produk + bahan baku + HPP ─────────────────────
        $opsStoreId = $storeIds['sentra-operasional'];
        $opsProducts = $catalog['sentra-operasional'];
        $createdOps = $this->insertProducts($opsStoreId, 'sentra-operasional', $opsProducts, $leafIds, $variantList, $adminId, $now);
        $productsByStore['sentra-operasional'] = $createdOps;
        $this->createShowcases($opsStoreId, $createdOps, $adminId, $now);

        $this->seedOpsInventory($opsStoreId, $createdOps, $adminId, $now);

        DemoState::set('products_by_store', $productsByStore);
        DemoState::set('variant_list', $variantList);

        $this->command?->info('Demo produk selesai: '.count($variantList).' varian produk terdaftar.');
    }

    /** @param array<int, array{0:string,1:string,2:string,3:string,4:string,5:array,6:bool,7:bool}> $products */
    private function insertProducts(int $storeId, string $storeSlug, array $products, array $leafIds, array &$variantList, string $adminId, string $now): array
    {
        $created = [];
        foreach ($products as $pRef => [$name, $slug, $catSlug, $brand, $short, $variants, $featured, $homepage]) {
            $categoryId = $leafIds[$catSlug] ?? null;

            $productId = DB::table('products')->insertGetId([
                'store_id' => $storeId,
                'primary_category_id' => $categoryId,
                'name' => $name,
                'slug' => $storeSlug.'-'.$slug,
                'description' => $short."\n\nDikemas dengan standar higienis dan pengiriman aman. Garansi toko berlaku sesuai ketentuan.",
                'brand' => $brand,
                'thumbnail' => 'https://picsum.photos/seed/'.$storeSlug.'-'.$slug.'/600/600',
                'status' => 'published',
                'is_active' => true,
                'allows_preorder' => false,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $isDefault = true;
            $vi = 1;
            foreach ($variants as $vName => [$price, $stock, $minStock]) {
                $code = substr(preg_replace('/[^A-Za-z0-9]/', '', $vName), 0, 6);
                $sku = strtoupper(substr($storeSlug, 0, 3)).'-'.sprintf('%04d', $pRef).'-'.$code.'-'.$vi;
                $variantId = DB::table('product_variants')->insertGetId([
                    'product_id' => $productId,
                    'store_id' => $storeId,
                    'sku' => $sku,
                    'name' => $vName,
                    'price' => $price,
                    'stock' => $stock,
                    'po_stock' => 0,
                    'stock_reserved' => 0,
                    'stock_booked' => 0,
                    'stock_preorder' => 0,
                    'max_order_qty' => max(5, (int) floor(($stock ?: 10) / 2)),
                    'min_stock' => $minStock,
                    'is_default' => $isDefault,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $isDefault = false;
                $vi++;

                $variantList[] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'store_id' => $storeId,
                    'store_slug' => $storeSlug,
                    'sku' => $sku,
                    'name' => $name.' - '.$vName,
                    'price' => $price,
                    'stock' => $stock,
                ];
            }

            // Gambar produk.
            $images = [
                ['seed-'.$storeSlug.'-'.$slug.'-1', 'Foto utama '.$name, 1, 1],
                ['seed-'.$storeSlug.'-'.$slug.'-2', 'Foto detail 2 '.$name, 0, 2],
                ['seed-'.$storeSlug.'-'.$slug.'-3', 'Foto detail 3 '.$name, 0, 3],
                ['seed-'.$storeSlug.'-'.$slug.'-4', 'Foto detail 4 '.$name, 0, 4],
            ];
            foreach ($images as [$seed, $alt, $isPrimary, $sort]) {
                DB::table('product_images')->insert([
                    'product_id' => $productId,
                    'url' => 'https://picsum.photos/seed/'.$seed.'/800/800',
                    'alt_text' => $alt,
                    'is_primary' => $isPrimary,
                    'sort_order' => $sort,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Kategori utama + atribut default.
            if ($categoryId) {
                DB::table('product_categories')->insert([
                    'product_id' => $productId,
                    'category_id' => $categoryId,
                    'is_primary' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $warnaAttr = DB::table('product_attributes')->where('slug', 'warna')->value('id');
            if ($warnaAttr) {
                DB::table('product_attribute_values')->insert([
                    'product_id' => $productId,
                    'attribute_id' => $warnaAttr,
                    'value' => 'default',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $created[$pRef] = $productId;
        }

        return $created;
    }

    private function createShowcases(int $storeId, array $productIds, string $adminId, string $now): void
    {
        $names = [['Terlaris', 'terlaris', 'Produk dengan penjualan terbanyak di toko ini.', 10], ['Baru', 'baru-masuk', 'Produk terbaru pilihan.', 20]];
        $products = array_values($productIds);

        foreach ($names as $i => [$name, $slug, $desc, $sort]) {
            $showcaseId = DB::table('showcases')->insertGetId([
                'store_id' => $storeId,
                'name' => $name,
                'slug' => $slug,
                'description' => $desc,
                'sort_order' => $sort,
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $picks = array_slice($products, 0, $i === 0 ? 4 : 3);
            foreach (array_values($picks) as $j => $productId) {
                DB::table('showcase_products')->insert([
                    'showcase_id' => $showcaseId,
                    'product_id' => $productId,
                    'sort_order' => ($j + 1) * 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function seedOpsInventory(int $storeId, array $products, string $adminId, string $now): void
    {
        $this->command?->info('Demo: menyiapkan bahan baku, HPP & costing toko operasional...');

        // raw_materials: [code, name, unit, stock, min_stock, avg_cost]
        $materials = [
            ['RM-BERAS', 'Beras Premium', 'kg', 40, 10, 12500],
            ['RM-AYAM', 'Ayam Potong Segar', 'kg', 18, 6, 32000],
            ['RM-BAKISAPI', 'Daging Sapi Giling', 'kg', 12, 4, 88000],
            ['RM-CABAI', 'Cabai Merah Keriting', 'kg', 6, 2, 28000],
            ['RM-BAWANG', 'Bawang Merah & Putih', 'kg', 10, 3, 30000],
            ['RM-MINYAK', 'Minyak Goreng', 'liter', 25, 8, 15000],
            ['RM-TELUR', 'Telur Ayam', 'kg', 15, 5, 26000],
            ['RM-TEPUNG', 'Tepung Terigu', 'kg', 12, 4, 11000],
            ['RM-BUMBU', 'Bumbu Dasar Racikan', 'kg', 8, 3, 20000],
            ['RM-KECAP', 'Kecap & Saos', 'botol', 14, 5, 18000],
        ];

        $materialIds = [];
        foreach ($materials as [$code, $name, $unit, $stock, $minStock, $avgCost]) {
            $materialId = DB::table('raw_materials')->insertGetId([
                'store_id' => $storeId,
                'code' => $code,
                'name' => $name,
                'unit' => $unit,
                'stock' => $stock,
                'minimum_stock' => $minStock,
                'average_cost' => $avgCost,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $materialIds[$code] = $materialId;
        }

        $this->writeMaterialMovements($storeId, $materialIds, $adminId, $now);
        $this->writeOpsCostings($storeId, $products, $materialIds, $adminId, $now);
    }

    private function writeMaterialMovements(int $storeId, array $materialIds, string $adminId, string $now): void
    {
        $start = now()->subDays(60)->startOfDay();
        foreach ($materialIds as $code => $id) {
            $name = DB::table('raw_materials')->where('id', $id)->value('name');
            $currentStock = (float) DB::table('raw_materials')->where('id', $id)->value('stock');
            $avgCost = (float) DB::table('raw_materials')->where('id', $id)->value('average_cost');

            $restockQty = (float) max(5, $currentStock + random_int(5, 12));
            $usageQty = max(1.5, round($restockQty - $currentStock, 1));
            $bal = 0;
            $lastMovementId = null;

            // Restok pembuka.
            $lastMovementId = $this->insertMaterialMovement($storeId, $id, 'restock', $restockQty, $restockQty, $avgCost, $avgCost * $restockQty, $start->copy(), $now, 'Opening restock '.$name);
            DB::table('raw_material_cost_histories')->insert([
                'store_id' => $storeId,
                'raw_material_id' => $id,
                'raw_material_stock_movement_id' => $lastMovementId,
                'old_average_cost' => 0,
                'new_average_cost' => $avgCost,
                'change_amount' => $avgCost,
                'change_percent' => 100,
                'direction' => 'up',
                'reference_type' => 'restock',
                'reference_number' => 'PBR-'.$id.'-01',
                'occurred_at' => $start->copy(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Penggunaan produksi (recorded setelah sebagian stok dipakai).
            $useTime = $start->copy()->addDays(random_int(15, 40));
            $newBal = $restockQty - $usageQty;
            $lastMovementId = $this->insertMaterialMovement($storeId, $id, 'usage', -$usageQty, $newBal, $avgCost, $avgCost * $usageQty, $useTime, $now, 'Pemakaian produksi '.$name);
            DB::table('raw_material_cost_histories')->insert([
                'store_id' => $storeId,
                'raw_material_id' => $id,
                'raw_material_stock_movement_id' => $lastMovementId,
                'old_average_cost' => $avgCost,
                'new_average_cost' => $avgCost,
                'change_amount' => 0,
                'change_percent' => 0,
                'direction' => 'neutral',
                'reference_type' => 'usage',
                'reference_number' => 'PKJ-'.$id.'-02',
                'occurred_at' => $useTime,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Restok pemeliharaan ke stok akhir == current stock.
            $topUp = $currentStock - $newBal;
            if ($topUp > 0) {
                $topUpTime = now()->subDays(random_int(1, 5))->startOfDay();
                $topUpMovementId = $this->insertMaterialMovement($storeId, $id, 'restock', $topUp, $currentStock, $avgCost, $avgCost * $topUp, $topUpTime, $now, 'Restok rutin '.$name);
                DB::table('raw_material_cost_histories')->insert([
                    'store_id' => $storeId,
                    'raw_material_id' => $id,
                    'raw_material_stock_movement_id' => $topUpMovementId,
                    'old_average_cost' => $avgCost,
                    'new_average_cost' => $avgCost,
                    'change_amount' => 0,
                    'change_percent' => 0,
                    'direction' => 'neutral',
                    'reference_type' => 'restock',
                    'reference_number' => 'PBR-'.$id.'-03',
                    'occurred_at' => $topUpTime,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function insertMaterialMovement(int $storeId, int $materialId, string $type, float $delta, float $balanceAfter, float $unitCost, float $totalCost, \Illuminate\Support\Carbon $occurredAt, string $now, string $notes): int
    {
        return DB::table('raw_material_stock_movements')->insertGetId([
            'store_id' => $storeId,
            'raw_material_id' => $materialId,
            'type' => $type,
            'quantity_delta' => $delta,
            'balance_after' => $balanceAfter,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'reference_type' => 'manual',
            'reference_number' => null,
            'notes' => $notes,
            'occurred_at' => $occurredAt,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function writeOpsCostings(int $storeId, array $products, array $materialIds, string $adminId, string $now): void
    {
        // Produk -> daftar bahan baku [code => qty, ...]
        $recipes = [
            $products[1] => ['RM-BERAS' => 0.2, 'RM-AYAM' => 0.25, 'RM-CABAI' => 0.03, 'RM-BAWANG' => 0.02, 'RM-MINYAK' => 0.1, 'RM-TELUR' => 0.05],
            $products[2] => ['RM-BAKISAPI' => 0.5, 'RM-BAWANG' => 0.03, 'RM-TEPUNG' => 0.08, 'RM-BUMBU' => 0.02, 'RM-TELUR' => 0.06],
            $products[3] => ['RM-AYAM' => 0.45, 'RM-BUMBU' => 0.05, 'RM-KECAP' => 0.03, 'RM-CABAI' => 0.04, 'RM-MINYAK' => 0.1],
        ];

        foreach ($recipes as $productId => $recipe) {
            $materialCost = 0;
            foreach ($recipe as $code => $qty) {
                $materialId = $materialIds[$code] ?? null;
                if (! $materialId) {
                    continue;
                }
                $unitCost = (float) DB::table('raw_materials')->where('id', $materialId)->value('average_cost');
                $totalCost = round($unitCost * $qty, 2);
                $materialCost += $totalCost;

                DB::table('product_materials')->insert([
                    'product_id' => $productId,
                    'raw_material_id' => $materialId,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $laborCost = round($materialCost * 0.2, 2);
            $overheadCost = round($materialCost * 0.12, 2);
            $hpp = round($materialCost + $laborCost + $overheadCost, 2);
            $sellingPrice = (float) DB::table('product_variants')->where('product_id', $productId)->where('is_default', true)->value('price');
            $margin = $sellingPrice ? round(($sellingPrice - $hpp) / $sellingPrice * 100, 2) : 0;

            DB::table('product_costings')->insert([
                'product_id' => $productId,
                'store_id' => $storeId,
                'material_cost' => $materialCost,
                'labor_cost' => $laborCost,
                'overhead_cost' => $overheadCost,
                'other_cost' => 0,
                'hpp' => $hpp,
                'margin_percent' => $margin,
                'suggested_price' => round($hpp * 1.35, -2),
                'selling_price' => $sellingPrice,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Contoh dampak kenaikan harga bahan baku terhadap HPP.
        $sampleProduct = $products[1];
        $ayamId = $materialIds['RM-AYAM'];
        $oldCost = 30000;
        $newCost = 32000;
        $oldHpp = 13500;
        $newHpp = 14500;

        $impactId = DB::table('product_costing_impacts')->insertGetId([
            'store_id' => $storeId,
            'product_id' => $sampleProduct,
            'raw_material_id' => $ayamId,
            'raw_material_cost_history_id' => null,
            'old_material_cost' => $oldCost,
            'new_material_cost' => $newCost,
            'old_hpp' => $oldHpp,
            'new_hpp' => $newHpp,
            'hpp_change_amount' => round($newHpp - $oldHpp, 2),
            'hpp_change_percent' => round(($newHpp - $oldHpp) / $oldHpp * 100, 2),
            'old_suggested_price' => 17000,
            'new_suggested_price' => 18000,
            'trigger_type' => 'raw_material_cost_change',
            'occurred_at' => now()->subDays(7)->startOfDay(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}