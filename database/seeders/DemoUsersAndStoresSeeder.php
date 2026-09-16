<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * User, toko, alamat, dan pengaturan pengiriman untuk seluruh data demo.
 *
 * Semua akun memakai password DemoIds::PASSWORD ('12345678') agar mudah
 * di-login saat demo. Email pemilik mengikuti nama tokonya.
 */
final class DemoUsersAndStoresSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Demo: membuat user, toko, alamat & pengaturan pengiriman...');

        $roles = [
            'super_admin' => DB::table('roles')->where('name', 'super_admin')->value('id'),
            'admin' => DB::table('roles')->where('name', 'admin')->value('id'),
            'seller' => DB::table('roles')->where('name', 'seller')->value('id'),
            'buyer' => DB::table('roles')->where('name', 'buyer')->value('id'),
        ];

        $now = now()->toDateTimeString();

        // ── Admin platform ───────────────────────────────────────────────────
        $this->createUser(DemoIds::SUPER_ADMIN, 'admin@marketku.id', 'Bagas Administrator',
            [($roles['super_admin'] ?? 0), ($roles['admin'] ?? 0)], $now);
        $this->createUser(DemoIds::ADMIN_CATALOG, 'citra.admin@marketku.id', 'Citra Kusuma',
            [($roles['admin'] ?? 0), ($roles['buyer'] ?? 0)], $now);

        // ── 6 Seller (5 fokus berjualan + 1 operasional) ────────────────────
        $sellers = [
            [
                'id' => DemoIds::SELLER_SARI, 'name' => 'Sari Nurjanah',
                'email' => 'sarinusantara@gmail.com',
                'store' => [
                    'name' => 'Toko Sari Nusantara', 'slug' => 'sari-nusantara',
                    'short' => 'Makanan ringan & oleh-oleh khas Nusantara.',
                    'desc' => 'Toko Sari Nusantara menjual aneka keripik, kue kering, dan oleh-oleh khas Nusantara. Bahan pilihan, dikemas higienis, dan diproduksi setiap hari sehingga selalu segar.',
                    'phone' => '081234561101', 'email' => 'sarinusantara@gmail.com',
                    'city' => 'Malang', 'province' => 'Jawa Timur',
                    'address' => 'Jl. Ijen Nirwana No. 17, Kota Malang',
                    'lat' => -7.97784054, 'lng' => 112.63107955,
                    'flat' => 12000, 'free' => 5, 'logo' => 'mall',
                    'store_type' => 'power_merchant',
                    'open_days' => 'Senin s/d Minggu', 'open_time' => '07:00:00', 'close_time' => '20:00:00',
                    'ig' => 'sarinusantara',
                ],
            ],
            [
                'id' => DemoIds::SELLER_RAKA, 'name' => 'Raka Wibawa',
                'email' => 'rakateknologi@gmail.com',
                'store' => [
                    'name' => 'Raka Cell & Elektronik', 'slug' => 'raka-teknologi',
                    'short' => 'Gadget, aksesori & elektronik rumah.',
                    'desc' => 'Raka Cell & Elektronik menyediakan perangkat elektronik dan aksesori gadget original bergaransi. Pengiriman cepat dengan packing aman.',
                    'phone' => '081234561102', 'email' => 'rakateknologi@gmail.com',
                    'city' => 'Jakarta Pusat', 'province' => 'DKI Jakarta',
                    'address' => 'Jl. Jend. Sudirman Kav. 21, Jakarta Pusat',
                    'lat' => -6.20876340, 'lng' => 106.84559900,
                    'flat' => 15000, 'free' => 8, 'logo' => 'electron',
                    'store_type' => 'power_merchant',
                    'open_days' => 'Senin s/d Sabtu', 'open_time' => '08:00:00', 'close_time' => '21:00:00',
                    'ig' => 'rakaceltelektronik',
                ],
            ],
            [
                'id' => DemoIds::SELLER_KOPI, 'name' => 'Bima Anggara',
                'email' => 'kopinusantara@gmail.com',
                'store' => [
                    'name' => 'Rumah Kopi Nusantara', 'slug' => 'kopi-nusantara',
                    'short' => 'Kopi nusantara sangrai harian.',
                    'desc' => 'Rumah Kopi Nusantara menyajikan biji kopi pilihan dari Gayo, Kintamani, Toraja, dan Jawa yang disangrai setiap hari agar aromanya selalu prima.',
                    'phone' => '081234561103', 'email' => 'kopinusantara@gmail.com',
                    'city' => 'Yogyakarta', 'province' => 'DI Yogyakarta',
                    'address' => 'Jl. Kaliurang KM 5.5, Sleman, Yogyakarta',
                    'lat' => -7.76108750, 'lng' => 110.37390850,
                    'flat' => 10000, 'free' => 4, 'logo' => 'coffee',
                    'open_days' => 'Senin s/d Minggu', 'open_time' => '06:00:00', 'close_time' => '22:00:00',
                    'ig' => 'rumahkopinusantara',
                ],
            ],
            [
                'id' => DemoIds::SELLER_PURNAMA, 'name' => 'Laras Purnama',
                'email' => 'purnamabatik@gmail.com',
                'store' => [
                    'name' => 'Purnama Batik & Fashion', 'slug' => 'purnama-batik',
                    'short' => 'Batik tulis, cap & fashion khas Solo.',
                    'desc' => 'Purnama Batik & Fashion mengangkat kain batik dari pengrajin Solo dan Pekalongan. Tersedia batik tulis, cap, dan printing dengan desain eksklusif.',
                    'phone' => '081234561104', 'email' => 'purnamabatik@gmail.com',
                    'city' => 'Surakarta', 'province' => 'Jawa Tengah',
                    'address' => 'Jl. Slamet Riyadi No. 328, Surakarta',
                    'lat' => -7.57548870, 'lng' => 110.82347310,
                    'flat' => 13000, 'free' => 6, 'logo' => 'batik',
                    'open_days' => 'Senin s/d Minggu', 'open_time' => '08:00:00', 'close_time' => '21:00:00',
                    'ig' => 'purnamabatik',
                ],
            ],
            [
                'id' => DemoIds::SELLER_ANANDA, 'name' => 'Nanda Saputra',
                'email' => 'anandastationery@gmail.com',
                'store' => [
                    'name' => 'Ananda Stationery & Kreatif', 'slug' => 'ananda-stationery',
                    'short' => 'Alat tulis, kertas & perlengkapan kreatif.',
                    'desc' => 'Ananda Stationery & Kreatif menyediakan alat tulis, buku catatan, dan perlengkapan kerajinan untuk kebutuhan sekolah, kantor, dan hobi.',
                    'phone' => '081234561105', 'email' => 'anandastationery@gmail.com',
                    'city' => 'Bandung', 'province' => 'Jawa Barat',
                    'address' => 'Jl. Braga No. 12, Kota Bandung',
                    'lat' => -6.91746390, 'lng' => 107.60912260,
                    'flat' => 11000, 'free' => 5, 'logo' => 'pen',
                    'open_days' => 'Senin s/d Sabtu', 'open_time' => '08:00:00', 'close_time' => '19:00:00',
                    'ig' => 'anandastationery',
                ],
            ],
            [
                'id' => DemoIds::SELLER_OPS, 'name' => 'Oki Pratama',
                'email' => 'sentraoperasional@gmail.com',
                'store' => [
                    'name' => 'Sentra Operasional Mandiri', 'slug' => 'sentra-operasional',
                    'short' => 'Dapur produksi makanan siap saji dengan pencatatan operasional lengkap.',
                    'desc' => 'Sentra Operasional Mandiri adalah dapur produksi makanan siap saji yang mencatat seluruh operasional toko: stok bahan baku, HPP produksi, pengeluaran kas, hingga piutang atas pesanan khusus.',
                    'phone' => '081234561106', 'email' => 'sentraoperasional@gmail.com',
                    'city' => 'Bekasi', 'province' => 'Jawa Barat',
                    'address' => 'Jl. Ahmad Yani No. 88, Bekasi Timur',
                    'lat' => -6.23826960, 'lng' => 106.99153020,
                    'flat' => 14000, 'free' => 7, 'logo' => 'kitchen',
                    'open_days' => 'Senin s/d Minggu', 'open_time' => '05:00:00', 'close_time' => '22:00:00',
                    'ig' => 'sentraoperasionalmandiri',
                ],
            ],
        ];

        $storeIds = [];
        foreach ($sellers as $i => $seller) {
            $this->createUser($seller['id'], $seller['email'], $seller['name'],
                [($roles['seller'] ?? 0), ($roles['buyer'] ?? 0)], $now, $now);

            $storeId = $this->createStore($seller['id'], $seller['store'], $now);
            $storeIds[$seller['store']['slug']] = $storeId;
            DemoState::storeOwner($seller['store']['slug'], $seller['id']);
            $this->createStoreDetailsAndAddress($storeId, $seller['store'], $seller['name'], $seller['store']['phone'], $now);
        }

        // Toko platform milik admin (untuk banner & promo platform).
        $storeIds['marketku-official'] = $this->createStore(DemoIds::SUPER_ADMIN, [
            'name' => 'Marketku Official Store', 'slug' => 'marketku-official',
            'short' => 'Toko resmi platform Marketku.',
            'desc' => 'Toko resmi platform Marketku untuk banner, promo, dan voucher platform.',
            'phone' => '081234560000', 'email' => 'official@marketku.id',
            'city' => 'Jakarta Selatan', 'province' => 'DKI Jakarta',
            'address' => 'Gedung Marketku Tower, SCBD Lot 9, Jakarta Selatan',
            'lat' => -6.22502110, 'lng' => 106.80739860,
            'flat' => 0, 'free' => 0, 'logo' => 'marketku',
            'store_type' => 'official',
            'open_days' => 'Senin s/d Minggu', 'open_time' => '00:00:00', 'close_time' => '23:59:59',
            'ig' => 'marketku.id',
        ], $now, false);
        DemoState::storeOwner('marketku-official', DemoIds::SUPER_ADMIN);

        // ── 10 Buyer ────────────────────────────────────────────────────────
        $buyers = [
            [DemoIds::BUYER_1, 'Angga Firmansyah', 'angga.firmansyah@gmail.com'],
            [DemoIds::BUYER_2, 'Dewi Lestari', 'dewi.lestari@gmail.com'],
            [DemoIds::BUYER_3, 'Rizky Ramadhan', 'rizky.ramadhan@gmail.com'],
            [DemoIds::BUYER_4, 'Putri Ayu Anindya', 'putri.ayu@gmail.com'],
            [DemoIds::BUYER_5, 'Fajar Nugraha', 'fajar.nugraha@gmail.com'],
            [DemoIds::BUYER_6, 'Salsa Nadia Putri', 'salsa.nadia@gmail.com'],
            [DemoIds::BUYER_7, 'Dimas Aditya', 'dimas.aditya@gmail.com'],
            [DemoIds::BUYER_8, 'Intan Permata', 'intan.permata@gmail.com'],
            [DemoIds::BUYER_9, 'Galih Prakoso', 'galih.prakoso@gmail.com'],
            [DemoIds::BUYER_10, 'Nadia Salsabila', 'nadia.salsabila@gmail.com'],
        ];

        $addressTemplates = [
            // [province, city, district, subdistrict, postal, address, label, recipient, phone, lat, lng]
            ['DKI Jakarta', 'Jakarta Selatan', 'Kebayoran Baru', 'Senayan', '12190', 'Jl. Senayan Raya No. 12', 'Rumah', 'Angga Firmansyah', '081200111001', -6.22502110, 106.80739860],
            ['DKI Jakarta', 'Jakarta Timur', 'Matraman', 'Pal Meriam', '13140', 'Jl. Salemba Raya No. 45', 'Rumah', 'Dewi Lestari', '081200111002', -6.19839570, 106.86094180],
            ['Jawa Barat', 'Depok', 'Beji', 'Kemiri Muka', '16423', 'Jl. Margonda Raya No. 77', 'Kantor', 'Rizky Ramadhan', '081200111003', -6.41023070, 106.83170140],
            ['Jawa Barat', 'Bogor', 'Bogor Tengah', 'Pabaton', '16121', 'Jl. Siliwangi No. 23', 'Rumah', 'Putri Ayu Anindya', '081200111004', -6.59480300, 106.79601800],
            ['Jawa Barat', 'Bandung', 'Coblong', 'Dago', '40135', 'Jl. Ir. H. Juanda No. 56', 'Rumah', 'Fajar Nugraha', '081200111005', -6.89052280, 107.60462700],
            ['DKI Jakarta', 'Jakarta Utara', 'Kelapa Gading', 'Kelapa Gading Barat', '14240', 'Boulevard Raya No. 101', 'Kantor', 'Salsa Nadia Putri', '081200111006', -6.16037280, 106.90552610],
            ['Jawa Tengah', 'Semarang', 'Semarang Tengah', 'Pekunden', '50135', 'Jl. Pemuda No. 150', 'Rumah', 'Dimas Aditya', '081200111007', -6.97168690, 110.41732260],
            ['DI Yogyakarta', 'Sleman', 'Depok', 'Caturtunggal', '55281', 'Jl. Kaliurang KM 4.5', 'Rumah', 'Intan Permata', '081200111008', -7.77005660, 110.40787660],
            ['Jawa Timur', 'Surabaya', 'Genteng', 'Genteng', '60275', 'Jl. Pemuda No. 88', 'Kantor', 'Galih Prakoso', '081200111009', -7.26149240, 112.75096510],
            ['Jawa Timur', 'Malang', 'Klojen', 'Bareng', '65116', 'Jl. Ijen No. 9', 'Rumah', 'Nadia Salsabila', '081200111010', -7.97783810, 112.62294230],
        ];

        foreach ($buyers as $i => [$id, $name, $email]) {
            $this->createUser($id, $email, $name, [($roles['buyer'] ?? 0)], $now, $now);
            $tpl = $addressTemplates[$i] ?? $addressTemplates[0];
            $this->createBuyerAddress($id, $tpl, $now, $i === 0);
        }

        DemoState::storeIds($storeIds);

        $this->command?->info('Demo users & stores selesai: '.count($sellers).' toko seller + 1 toko official, 10 buyer.');
    }

    private function createUser(string $id, string $email, string $name, array $roleIds, string $now, ?string $updatedAt = null): void
    {
        $password = Hash::make(DemoIds::PASSWORD);

        DB::table('users')->insert([
            'id' => $id,
            'email' => $email,
            'password' => $password,
            'has_set_password' => true,
            'name' => $name,
            'avatar' => 'https://picsum.photos/seed/'.md5($id).'/160/160',
            'is_email_verified' => true,
            'is_active' => true,
            'banned_at' => null,
            'created_by' => DemoIds::SUPER_ADMIN,
            'updated_by' => DemoIds::SUPER_ADMIN,
            'created_at' => $now,
            'updated_at' => $updatedAt ?? $now,
        ]);

        foreach (array_filter($roleIds) as $roleId) {
            DB::table('user_roles')->insert([
                'user_id' => $id,
                'role_id' => $roleId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function createStore(string $userId, array $s, string $now, bool $withDetails = true): int
    {
        $id = DB::table('stores')->insertGetId([
            'user_id' => $userId,
            'name' => $s['name'],
            'slug' => $s['slug'],
            'description' => $s['desc'],
            'short_description' => $s['short'],
            'phone' => $s['phone'],
            'email' => $s['email'],
            'city' => $s['city'],
            'province' => $s['province'],
            'address' => $s['address'],
            'status' => 'approved',
            'is_active' => true,
            'store_type' => $s['store_type'] ?? 'regular',
            'logo' => 'https://picsum.photos/seed/store-'.$s['slug'].'/160/160',
            'banner_url' => 'https://picsum.photos/seed/banner-'.$s['slug'].'/1200/400',
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($withDetails) {
            // Alamat toko.
            DB::table('addresses')->insert([
                'store_id' => $id,
                'country' => 'Indonesia',
                'province' => $s['province'],
                'city_or_regency' => $s['city'],
                'district' => $s['city'],
                'subdistrict' => 'Pusat',
                'postal_code' => $this->postalOf($s['city']),
                'full_address' => $s['address'],
                'notes' => 'Alamat toko',
                'label' => 'Toko',
                'recipient_name' => $s['name'],
                'phone_number' => $s['phone'],
                'latitude' => $s['lat'],
                'longitude' => $s['lng'],
                'is_primary' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $id;
    }

    private function createStoreDetailsAndAddress(int $storeId, array $s, string $ownerName, string $ownerPhone, string $now): void
    {
        DB::table('store_details')->insert([
            'store_id' => $storeId,
            'owner_name' => $ownerName,
            'owner_phone' => $ownerPhone,
            'description' => $s['desc'],
            'shipping_policy' => 'Pesanan dikirim setiap hari sebelum jam '.substr($s['close_time'], 0, 2).':00 WIB. Packing rapi dengan bubble wrap.',
            'return_policy' => 'Barang dapat dikembalikan maksimal 2 x 24 jam setelah diterima apabila rusak/keliru.',
            'open_days' => $s['open_days'],
            'open_time' => $s['open_time'],
            'close_time' => $s['close_time'],
            'whatsapp_url' => 'https://wa.me/62'.substr($s['phone'], 1),
            'instagram_url' => 'https://instagram.com/'.$s['ig'],
            'tiktok_url' => 'https://tiktok.com/@'.$s['ig'],
            'website_url' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('shipping_settings')->insert([
            'store_id' => $storeId,
            'store_latitude' => $s['lat'],
            'store_longitude' => $s['lng'],
            'free_shipping_max_distance' => $s['free'],
            'default_flat_rate' => $s['flat'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function createBuyerAddress(string $userId, array $tpl, string $now, bool $primary): void
    {
        [$province, $city, $district, $subdistrict, $postal, $address, $label, $recipient, $phone, $lat, $lng] = $tpl;

        DB::table('addresses')->insert([
            'user_id' => $userId,
            'country' => 'Indonesia',
            'province' => $province,
            'city_or_regency' => $city,
            'district' => $district,
            'subdistrict' => $subdistrict,
            'postal_code' => $postal,
            'full_address' => $address,
            'notes' => null,
            'label' => $label,
            'recipient_name' => $recipient,
            'phone_number' => $phone,
            'latitude' => $lat,
            'longitude' => $lng,
            'komerce_destination_id' => 'KMB-'.substr($postal, 0, 4),
            'is_primary' => $primary,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('carts')->insertOrIgnore([
            'user_id' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('wishlists')->insert([
            'id' => $userId,
            'user_id' => $userId,
            'name' => 'utama',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function postalOf(string $city): string
    {
        return match ($city) {
            'Jakarta Pusat' => '10270',
            'Jakarta Selatan' => '12190',
            'Malang' => '65116',
            'Yogyakarta' => '55281',
            'Surakarta' => '57112',
            'Bandung' => '40115',
            'Bekasi' => '17148',
            default => '10110',
        };
    }
}
