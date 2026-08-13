<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $permissions = [
            ['name' => 'users.view', 'description' => 'Melihat pengguna', 'is_active' => true],
            ['name' => 'users.create', 'description' => 'Membuat pengguna', 'is_active' => true],
            ['name' => 'users.update', 'description' => 'Memperbarui pengguna', 'is_active' => true],
            ['name' => 'users.delete', 'description' => 'Menghapus pengguna', 'is_active' => true],
            ['name' => 'roles.manage', 'description' => 'Mengelola role dan permission', 'is_active' => true],
            ['name' => 'catalog.view', 'description' => 'Melihat katalog', 'is_active' => true],
            ['name' => 'catalog.manage', 'description' => 'Mengelola katalog', 'is_active' => true],
            ['name' => 'products.manage', 'description' => 'Mengelola produk', 'is_active' => true],
            ['name' => 'stores.manage', 'description' => 'Mengelola toko', 'is_active' => true],
            ['name' => 'banners.manage', 'description' => 'Mengelola banner toko', 'is_active' => true],
            ['name' => 'promotions.manage', 'description' => 'Mengelola promosi global', 'is_active' => true],
            ['name' => 'vouchers.manage', 'description' => 'Mengelola voucher', 'is_active' => true],
            ['name' => 'orders.view', 'description' => 'Melihat pesanan', 'is_active' => true],
            ['name' => 'orders.manage', 'description' => 'Mengelola pesanan', 'is_active' => true],
            ['name' => 'checkout.create', 'description' => 'Membuat checkout', 'is_active' => true],
            ['name' => 'reports.export', 'description' => 'Mengekspor laporan', 'is_active' => true],
            ['name' => 'finance.manage', 'description' => 'Mengelola pemasukan, pengeluaran, hutang, dan piutang', 'is_active' => true],
            ['name' => 'stock.manage', 'description' => 'Mengelola stok dan riwayat pergerakan barang', 'is_active' => true],
            ['name' => 'showcases.manage', 'description' => 'Mengelola etalase produk toko', 'is_active' => true],
            ['name' => 'tickets.create', 'description' => 'Membuat dan membalas Help', 'is_active' => true],
            ['name' => 'tickets.manage', 'description' => 'Mengelola seluruh Help', 'is_active' => true],
            ['name' => 'missions.manage', 'description' => 'Mengelola misi dan reward voucher', 'is_active' => true],
            ['name' => 'missions.participate', 'description' => 'Mengikuti misi pengguna', 'is_active' => true],
            ['name' => 'reviews.create', 'description' => 'Membuat dan mengelola review milik sendiri', 'is_active' => true],
            ['name' => 'reviews.manage', 'description' => 'Memoderasi seluruh review produk', 'is_active' => true],
            ['name' => 'chat.use', 'description' => 'Menggunakan percakapan marketplace', 'is_active' => true],
            ['name' => 'announcements.manage', 'description' => 'Mengirim announcement marketplace', 'is_active' => true],
            ['name' => 'promotion_payments.submit', 'description' => 'Mengajukan pembayaran promosi seller', 'is_active' => true],
            ['name' => 'promotion_payments.review', 'description' => 'Menyetujui atau menolak pembayaran promosi', 'is_active' => true],
            ['name' => 'legacy.dashboard', 'description' => 'Dashboard legacy', 'is_active' => false],
        ];

        DB::table('permissions')->upsert(
            array_map(fn (array $permission): array => [
                ...$permission,
                'created_at' => $now,
                'updated_at' => $now,
            ], $permissions),
            ['name'],
            ['description', 'is_active', 'updated_at']
        );

        $roles = [
            ['name' => 'super_admin', 'description' => 'Akses penuh seluruh sistem', 'is_active' => true],
            ['name' => 'admin', 'description' => 'Administrator operasional', 'is_active' => true],
            ['name' => 'seller', 'description' => 'Pemilik dan pengelola toko', 'is_active' => true],
            ['name' => 'buyer', 'description' => 'Pembeli marketplace', 'is_active' => true],
            ['name' => 'legacy_operator', 'description' => 'Role transisi yang sudah tidak digunakan', 'is_active' => false],
        ];

        DB::table('roles')->upsert(
            array_map(fn (array $role): array => [
                ...$role,
                'created_at' => $now,
                'updated_at' => $now,
            ], $roles),
            ['name'],
            ['description', 'is_active', 'updated_at']
        );

        $roleIds = DB::table('roles')->pluck('id', 'name');
        $permissionIds = DB::table('permissions')->pluck('id', 'name');
        $activePermissionNames = array_column(
            array_values(array_filter($permissions, fn (array $permission): bool => $permission['is_active'])),
            'name'
        );
        $assignments = [
            'super_admin' => $activePermissionNames,
            'admin' => [
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
                'roles.manage',
                'catalog.view',
                'catalog.manage',
                'products.manage',
                'stores.manage',
                'banners.manage',
                'promotions.manage',
                'vouchers.manage',
                'orders.view',
                'orders.manage',
                'reports.export',
                'finance.manage',
                'stock.manage',
                'showcases.manage',
                'tickets.create',
                'tickets.manage',
                'missions.manage',
                'missions.participate',
                'reviews.create',
                'reviews.manage',
                'chat.use',
                'announcements.manage',
                'promotion_payments.review',
            ],
            'seller' => [
                'catalog.view',
                'products.manage',
                'stores.manage',
                'banners.manage',
                'promotions.manage',
                'vouchers.manage',
                'orders.view',
                'orders.manage',
                'finance.manage',
                'stock.manage',
                'showcases.manage',
                'tickets.create',
                'missions.participate',
                'reviews.create',
                'chat.use',
                'promotion_payments.submit',
            ],
            'buyer' => [
                'catalog.view',
                'orders.view',
                'checkout.create',
                'tickets.create',
                'missions.participate',
                'reviews.create',
                'chat.use',
            ],
        ];

        foreach ($assignments as $roleName => $permissionNames) {
            $roleId = $roleIds[$roleName] ?? null;

            if (! $roleId) {
                continue;
            }

            $rows = collect($permissionNames)
                ->filter(fn (string $name): bool => isset($permissionIds[$name]))
                ->map(fn (string $name): array => [
                    'role_id' => $roleId,
                    'permission_id' => $permissionIds[$name],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            DB::table('role_permissions')->where('role_id', $roleId)->delete();

            if ($rows !== []) {
                DB::table('role_permissions')->insert($rows);
            }
        }
    }
}
