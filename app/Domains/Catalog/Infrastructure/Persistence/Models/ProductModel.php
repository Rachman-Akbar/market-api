<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Domains\Stores\Infrastructure\Persistence\Models\StoreModel;

final class ProductModel extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'store_id',
        'primary_category_id',
        'seller_id',
        'name',
        'slug',
        'sku',
        'description',
        'short_description',
        'brand',
        'weight_gram',
        'price',
        'stock',
        'thumbnail',
        'status',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'store_id' => 'integer',
        'primary_category_id' => 'integer',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'weight_gram' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(StoreModel::class, 'store_id');
    }

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'primary_category_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            CategoryModel::class,
            'product_categories',
            'product_id',
            'category_id'
        )
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImageModel::class, 'product_id');
    }
}