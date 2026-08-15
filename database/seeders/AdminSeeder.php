<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $password = Hash::make('12345678');
        $users = [
            [
                'id' => SeederIds::SUPER_ADMIN,
                'name' => 'Budi Administrator',
                'email' => 'budi@gmail.com',
                'roles' => ['super_admin', 'admin', 'seller', 'buyer'],
            ],
            [
                'id' => SeederIds::CATALOG_ADMIN,
                'name' => 'Rina Catalog Admin',
                'email' => 'rina.admin@gmail.com',
                'roles' => ['admin'],
            ],
            [
                'id' => SeederIds::OPERATIONS_ADMIN,
                'name' => 'Dimas Operations Admin',
                'email' => 'dimas.admin@gmail.com',
                'roles' => ['admin'],
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
                    'created_by' => $user['id'] === SeederIds::SUPER_ADMIN ? null : SeederIds::SUPER_ADMIN,
                    'updated_by' => $user['id'] === SeederIds::SUPER_ADMIN ? null : SeederIds::SUPER_ADMIN,
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
    }
}
