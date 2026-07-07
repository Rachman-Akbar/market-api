<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Category\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // 1. Tambahkan import ini
use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductModel; // 2. Tambahkan import ini

final class CategoryModel extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'catalog_group_id',
        'parent_id',
        'level',
        'sort_order',
        'is_active',
        'is_visible_in_menu',
        'name',
        'slug',
        'full_slug',
        'image_url',
        'icon_url',
    ];

    protected $casts = [
        'catalog_group_id' => 'integer',
        'parent_id' => 'integer',
        'level' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_visible_in_menu' => 'boolean',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childrenTree(): HasMany
    {
        return $this->children()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with('childrenTree');
    }

    /**
     * 3. Tambahkan method products() ini agar error bad method call selesai
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductModel::class,
            'product_categories', // Nama pivot table disamakan dengan ProductModel
            'category_id',        // Foreign key untuk category di pivot table
            'product_id'          // Foreign key untuk product di pivot table
        )->withPivot('is_primary');
    }
}
