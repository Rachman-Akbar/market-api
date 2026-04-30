<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Stores\Infrastructure\Persistence\Models\StoreModel;

final class ProductModel extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'store_id',
        'category_id',
        'seller_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'thumbnail',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(StoreModel::class, 'store_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImageModel::class, 'product_id');
    }
}
