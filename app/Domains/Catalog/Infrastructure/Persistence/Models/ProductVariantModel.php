<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductVariantModel extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price',
        'stock',
        'is_default',
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'is_default' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            ProductModel::class,
            'product_id'
        );
    }

    public function values(): HasMany
    {
        return $this->hasMany(
            ProductVariantValueModel::class,
            'variant_id'
        );
    }
}