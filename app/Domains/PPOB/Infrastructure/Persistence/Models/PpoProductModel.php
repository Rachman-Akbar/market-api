<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpoProductModel extends Model
{
    use SoftDeletes;

    protected $table = 'ppob_products';

    protected $fillable = [
        'operator_id',
        'category',
        'product_type',
        'provider_product_code',
        'name',
        'brand',
        'nominal',
        'provider_price',
        'admin_fee',
        'commission',
        'margin',
        'selling_price',
        'status',
        'is_available',
        'icon_url',
        'metadata',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'operator_id' => 'integer',
        'provider_price' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'commission' => 'decimal:2',
        'margin' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function operator()
    {
        return $this->belongsTo(PpoOperatorModel::class, 'operator_id');
    }

    public function transactions()
    {
        return $this->hasMany(PpoTransactionModel::class, 'product_id');
    }
}
