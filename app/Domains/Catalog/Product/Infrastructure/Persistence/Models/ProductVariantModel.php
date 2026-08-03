<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Infrastructure\Persistence\Models;

use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class ProductVariantModel extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'store_id',
        'sku',
        'name',
        'price',
        'stock',
        'is_default',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'store_id' => 'integer',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_default' => 'boolean',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): string => trim((string) preg_replace('/\s+/u', ' ', (string) $value))
        );
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    public function store()
    {
        return $this->belongsTo(StoreModel::class, 'store_id');
    }

    public function values()
    {
        return $this->hasMany(ProductVariantValueModel::class, 'variant_id');
    }
}
