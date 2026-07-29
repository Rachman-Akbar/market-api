<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $groups = [
            ['name' => 'makanan dan minuman', 'slug' => 'makanan-dan-minuman', 'is_active' => true],
            ['name' => 'rumah dan gaya hidup', 'slug' => 'rumah-dan-gaya-hidup', 'is_active' => true],
            ['name' => 'elektronik', 'slug' => 'elektronik', 'is_active' => true],
        ];

        foreach ($groups as $group) {
            DB::table('catalog_groups')->updateOrInsert(
                ['slug' => $group['slug']],
                [
                    ...$group,
                    'created_by' => SeederIds::SUPER_ADMIN,
                    'updated_by' => SeederIds::SUPER_ADMIN,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );
        }

        $groupIds = DB::table('catalog_groups')->pluck('id', 'slug');
        $categoryIds = [];
        $categories = [
            ['key' => 'makanan', 'group' => 'makanan-dan-minuman', 'parent' => null, 'name' => 'makanan', 'slug' => 'makanan', 'level' => 1, 'sort' => 10],
            ['key' => 'makanan-siap-saji', 'group' => 'makanan-dan-minuman', 'parent' => 'makanan', 'name' => 'makanan siap saji', 'slug' => 'makanan-siap-saji', 'level' => 2, 'sort' => 10],
            ['key' => 'sate', 'group' => 'makanan-dan-minuman', 'parent' => 'makanan-siap-saji', 'name' => 'sate', 'slug' => 'sate', 'level' => 3, 'sort' => 10],
            ['key' => 'nasi-goreng', 'group' => 'makanan-dan-minuman', 'parent' => 'makanan-siap-saji', 'name' => 'nasi goreng', 'slug' => 'nasi-goreng', 'level' => 3, 'sort' => 20],
            ['key' => 'bakso', 'group' => 'makanan-dan-minuman', 'parent' => 'makanan-siap-saji', 'name' => 'bakso', 'slug' => 'bakso', 'level' => 3, 'sort' => 30],
            ['key' => 'minuman', 'group' => 'makanan-dan-minuman', 'parent' => 'makanan', 'name' => 'minuman', 'slug' => 'minuman', 'level' => 2, 'sort' => 20],
            ['key' => 'kopi', 'group' => 'makanan-dan-minuman', 'parent' => 'minuman', 'name' => 'kopi', 'slug' => 'kopi', 'level' => 3, 'sort' => 10],
            ['key' => 'teh', 'group' => 'makanan-dan-minuman', 'parent' => 'minuman', 'name' => 'teh', 'slug' => 'teh', 'level' => 3, 'sort' => 20],
            ['key' => 'dekorasi-rumah', 'group' => 'rumah-dan-gaya-hidup', 'parent' => null, 'name' => 'dekorasi rumah', 'slug' => 'dekorasi-rumah', 'level' => 1, 'sort' => 10],
            ['key' => 'tekstil-rumah', 'group' => 'rumah-dan-gaya-hidup', 'parent' => 'dekorasi-rumah', 'name' => 'tekstil rumah', 'slug' => 'tekstil-rumah', 'level' => 2, 'sort' => 10],
            ['key' => 'selimut', 'group' => 'rumah-dan-gaya-hidup', 'parent' => 'tekstil-rumah', 'name' => 'selimut', 'slug' => 'selimut', 'level' => 3, 'sort' => 10],
            ['key' => 'sarung-bantal', 'group' => 'rumah-dan-gaya-hidup', 'parent' => 'tekstil-rumah', 'name' => 'sarung bantal', 'slug' => 'sarung-bantal', 'level' => 3, 'sort' => 20],
            ['key' => 'pencahayaan', 'group' => 'rumah-dan-gaya-hidup', 'parent' => 'dekorasi-rumah', 'name' => 'pencahayaan', 'slug' => 'pencahayaan', 'level' => 2, 'sort' => 20],
            ['key' => 'lampu-meja', 'group' => 'rumah-dan-gaya-hidup', 'parent' => 'pencahayaan', 'name' => 'lampu meja', 'slug' => 'lampu-meja', 'level' => 3, 'sort' => 10],
            ['key' => 'lampu-gantung', 'group' => 'rumah-dan-gaya-hidup', 'parent' => 'pencahayaan', 'name' => 'lampu gantung', 'slug' => 'lampu-gantung', 'level' => 3, 'sort' => 20],
            ['key' => 'perangkat-pintar', 'group' => 'elektronik', 'parent' => null, 'name' => 'perangkat pintar', 'slug' => 'perangkat-pintar', 'level' => 1, 'sort' => 10],
            ['key' => 'audio', 'group' => 'elektronik', 'parent' => 'perangkat-pintar', 'name' => 'audio', 'slug' => 'audio', 'level' => 2, 'sort' => 10],
            ['key' => 'speaker', 'group' => 'elektronik', 'parent' => 'audio', 'name' => 'speaker', 'slug' => 'speaker', 'level' => 3, 'sort' => 10],
            ['key' => 'headphone', 'group' => 'elektronik', 'parent' => 'audio', 'name' => 'headphone', 'slug' => 'headphone', 'level' => 3, 'sort' => 20],
            ['key' => 'aksesori-perangkat', 'group' => 'elektronik', 'parent' => 'perangkat-pintar', 'name' => 'aksesori perangkat', 'slug' => 'aksesori-perangkat', 'level' => 2, 'sort' => 20],
            ['key' => 'charger', 'group' => 'elektronik', 'parent' => 'aksesori-perangkat', 'name' => 'charger', 'slug' => 'charger', 'level' => 3, 'sort' => 10],
            ['key' => 'kabel-data', 'group' => 'elektronik', 'parent' => 'aksesori-perangkat', 'name' => 'kabel data', 'slug' => 'kabel-data', 'level' => 3, 'sort' => 20],
        ];

        foreach ($categories as $category) {
            $parentId = $category['parent'] ? ($categoryIds[$category['parent']] ?? null) : null;
            $parentFullSlug = $category['parent'] ? DB::table('categories')->where('id', $parentId)->value('full_slug') : null;
            $fullSlug = $parentFullSlug ? $parentFullSlug.'/'.$category['slug'] : $category['slug'];
            $imageUrl = $category['level'] === 3
                ? 'https://picsum.photos/seed/category-'.$category['slug'].'/800/800'
                : null;

            DB::table('categories')->updateOrInsert(
                ['full_slug' => $fullSlug],
                [
                    'catalog_group_id' => $groupIds[$category['group']],
                    'parent_id' => $parentId,
                    'parent_scope_id' => $parentId ?? 0,
                    'level' => $category['level'],
                    'sort_order' => $category['sort'],
                    'is_active' => true,
                    'is_visible_in_menu' => true,
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'image_url' => $imageUrl,
                    'icon_url' => null,
                    'created_by' => SeederIds::SUPER_ADMIN,
                    'updated_by' => SeederIds::SUPER_ADMIN,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );

            $categoryIds[$category['key']] = (int) DB::table('categories')->where('full_slug', $fullSlug)->value('id');
        }
    }
}
