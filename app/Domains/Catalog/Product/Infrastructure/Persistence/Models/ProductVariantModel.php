<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;

final class ProductVariantModel extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'store_id', // Ditambahkan agar data store_id dapat disimpan langsung ke tabel varian
        'sku',
        'name',
        'price',
        'stock',
        'is_default',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'store_id' => 'integer', // Ditambahkan cast integer
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_default' => 'boolean',
    ];

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