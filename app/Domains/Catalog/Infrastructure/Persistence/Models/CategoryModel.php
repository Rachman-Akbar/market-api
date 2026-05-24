<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class CategoryModel extends Model
{
    use HasRecursiveRelationships;

    protected $table = 'categories';

    protected $fillable = [
        'catalog_group_id',
        'parent_id',
        'name',
        'slug',
        'full_slug',
        'description',
        'image_url',
        'icon_url',
        'cover_image_url',
        'level',
        'sort_order',
        'is_active',
        'is_visible_in_menu',
    ];

    protected $casts = [
        'catalog_group_id'    => 'integer',
        'parent_id'           => 'integer',
        'level'               => 'integer',
        'sort_order'          => 'integer',
        'is_active'           => 'boolean',
        'is_visible_in_menu'  => 'boolean',
    ];

    public function catalogGroup(): BelongsTo
    {
        return $this->belongsTo(CatalogGroupModel::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id'
        )
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()
            ->with('childrenRecursive');
    }

    /**
     * IMPORTANT
     * Untuk include descendants products
     */
    public function descendants()
    {
        return $this->descendantsAndSelf();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductModel::class,
            'product_categories',
            'category_id',
            'product_id'
        );
    }
}