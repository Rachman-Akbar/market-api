<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class SellerSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $password = Hash::make('12345678');
        $users = [
            [
                'id' => SeederIds::SELLER_ONE,
                'name' => 'Sari Lestari',
                'email' => 'sari@gmail.com',
                'roles' => ['seller', 'buyer'],
            ],
            [
                'id' => SeederIds::SELLER_TWO,
                'name' => 'Raka Wibowo',
                'email' => 'raka@gmail.com',
                'roles' => ['seller', 'buyer'],
            ],
            [
                'id' => SeederIds::BUYER_ONE,
                'name' => 'Andi Pratama',
                'email' => 'andi@gmail.com',
                'roles' => ['buyer'],
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['id' => $user['id']],
                [
                    'firebase_uid' => null,
                    'email' => strtolower($user['email']),
                    'password' => $password,
                    'name' => $user['name'],
                    'avatar' => null,
                    'is_email_verified' => true,
                    'is_active' => true,
                    'banned_at' => null,
                    'created_by' => SeederIds::SUPER_ADMIN,
                    'updated_by' => SeederIds::SUPER_ADMIN,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
        }

        $roleIds = DB::table('roles')->pluck('id', 'name');

        foreach ($users as $user) {
            DB::table('user_roles')->where('user_id', $user['id'])->delete();
            $rows = collect($user['roles'])
                ->filter(fn (string $role): bool => isset($roleIds[$role]))
                ->map(fn (string $role): array => [
                    'user_id' => $user['id'],
                    'role_id' => $roleIds[$role],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            if ($rows !== []) {
                DB::table('user_roles')->insert($rows);
            }
        }

        $stores = [
            [
                'user_id' => SeederIds::SELLER_ONE,
                'name' => 'sari nusantara',
                'slug' => 'sari-nusantara',
                'description' => 'Produk rumah, makanan, dan gaya hidup pilihan.',
                'short_description' => 'Produk pilihan dari seller terpercaya.',
                'phone' => '081234567801',
                'email' => 'toko.sari@gmail.com',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'address' => 'Jl. Kemang Raya No. 10',
                'status' => 'approved',
                'is_active' => true,
                'logo' => 'https://picsum.photos/seed/sari-nusantara-logo/300/300',
                'banner_url' => 'https://picsum.photos/seed/sari-nusantara-banner/1600/500',
            ],
            [
                'user_id' => SeederIds::SELLER_TWO,
                'name' => 'raka teknologi',
                'slug' => 'raka-teknologi',
                'description' => 'Perangkat teknologi dan aksesori untuk kebutuhan harian.',
                'short_description' => 'Teknologi praktis dengan harga bersahabat.',
                'phone' => '081234567802',
                'email' => 'toko.raka@gmail.com',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'address' => 'Jl. Asia Afrika No. 25',
                'status' => 'approved',
                'is_active' => true,
                'logo' => 'https://picsum.photos/seed/raka-teknologi-logo/300/300',
                'banner_url' => 'https://picsum.photos/seed/raka-teknologi-banner/1600/500',
            ],
        ];

        foreach ($stores as $store) {
            DB::table('stores')->updateOrInsert(
                ['user_id' => $store['user_id']],
                [
                    ...$store,
                    'created_by' => SeederIds::SUPER_ADMIN,
                    'updated_by' => SeederIds::SUPER_ADMIN,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
        }

        $storeRows = DB::table('stores')->whereIn('user_id', [SeederIds::SELLER_ONE, SeederIds::SELLER_TWO])->get();

        foreach ($storeRows as $store) {
            DB::table('store_details')->updateOrInsert(
                ['store_id' => $store->id],
                [
                    'owner_name' => $store->user_id === SeederIds::SELLER_ONE ? 'Sari Lestari' : 'Raka Wibowo',
                    'owner_phone' => $store->phone,
                    'description' => 'Toko aktif untuk pengujian marketplace.',
                    'shipping_policy' => 'Pesanan diproses maksimal dua hari kerja setelah pembayaran terverifikasi.',
                    'return_policy' => 'Pengembalian diterima maksimal tujuh hari setelah barang diterima.',
                    'open_days' => 'senin-sabtu',
                    'open_time' => '09:00:00',
                    'close_time' => '18:00:00',
                    'whatsapp_url' => null,
                    'instagram_url' => null,
                    'tiktok_url' => null,
                    'website_url' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('addresses')->updateOrInsert(
                ['store_id' => $store->id],
                [
                    'user_id' => $store->user_id,
                    'country' => 'Indonesia',
                    'province' => $store->province,
                    'city_or_regency' => $store->city,
                    'district' => 'Kecamatan Utama',
                    'subdistrict' => 'Kelurahan Utama',
                    'postal_code' => '12345',
                    'full_address' => $store->address,
                    'notes' => 'Alamat operasional toko',
                    'label' => 'Toko',
                    'recipient_name' => $store->name,
                    'phone_number' => $store->phone,
                    'latitude' => -6.20000000,
                    'longitude' => 106.81666600,
                    'komerce_destination_id' => null,
                    'is_primary' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('shipping_settings')->updateOrInsert(
                ['store_id' => $store->id],
                [
                    'store_latitude' => -6.20000000,
                    'store_longitude' => 106.81666600,
                    'free_shipping_max_distance' => 5,
                    'default_flat_rate' => 15000,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
