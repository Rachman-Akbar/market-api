<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpoPricingRuleModel extends Model
{
    use SoftDeletes;

    protected $table = 'ppob_pricing_rules';

    protected $fillable = [
        'level',
        'category',
        'operator_id',
        'product_id',
        'margin_type',
        'margin_value',
        'admin_fee_type',
        'admin_fee_value',
        'commission_type',
        'commission_value',
        'min_selling_price',
        'max_selling_price',
        'priority',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'operator_id' => 'integer',
        'product_id' => 'integer',
        'margin_value' => 'decimal:2',
        'admin_fee_value' => 'decimal:2',
        'commission_value' => 'decimal:2',
        'min_selling_price' => 'decimal:2',
        'max_selling_price' => 'decimal:2',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];
}
