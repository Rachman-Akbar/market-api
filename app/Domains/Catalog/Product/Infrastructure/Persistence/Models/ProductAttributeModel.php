<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

final class ProductAttributeModel extends Model
{
    protected $table = 'product_attributes';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'type',
    ];

    public function productValues()
    {
        return $this->hasMany(ProductAttributeValueModel::class, 'attribute_id');
    }

    public function variantValues()
    {
        return $this->hasMany(ProductVariantValueModel::class, 'attribute_id');
    }
}
