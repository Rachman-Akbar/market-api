<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryModel extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'catalog_group_id',
        'name',
        'slug',
        'description',
        'image_url',
        'cover_image_url',
        'is_active',
    ];

    protected $casts = [
        'catalog_group_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function catalogGroup(): BelongsTo
    {
        return $this->belongsTo(CatalogGroupModel::class, 'catalog_group_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'category_id');
    }
}