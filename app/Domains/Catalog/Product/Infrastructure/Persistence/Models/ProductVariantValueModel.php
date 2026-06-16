<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductVariantValueModel extends Model
{
    protected $table = 'product_variant_values';

    protected $fillable = [
        'variant_id',
        'attribute_id',
        'value',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariantModel::class,
            'variant_id'
        );
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(
            ProductAttributeModel::class,
            'attribute_id'
        );
    }
}
