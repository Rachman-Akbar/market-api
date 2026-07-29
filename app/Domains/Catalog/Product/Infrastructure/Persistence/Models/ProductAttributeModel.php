<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class ProductAttributeModel extends Model
{
    protected $table = 'product_attributes';

    protected $fillable = [
        'name',
        'slug',
        'type',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): string => Str::lower(trim((string) $value))
        );
    }

    public function productValues()
    {
        return $this->hasMany(ProductAttributeValueModel::class, 'attribute_id');
    }

    public function variantValues()
    {
        return $this->hasMany(ProductVariantValueModel::class, 'attribute_id');
    }
}
