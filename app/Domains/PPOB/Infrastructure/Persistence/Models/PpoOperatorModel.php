<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpoOperatorModel extends Model
{
    use SoftDeletes;

    protected $table = 'ppob_operators';

    protected $attributes = [
        'provider_name' => 'IAK',
        'is_active' => true,
    ];

    protected $fillable = [
        'name',
        'slug',
        'category',
        'brand',
        'operator_prefix',
        'provider_name',
        'icon_url',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function products()
    {
        return $this->hasMany(PpoProductModel::class, 'operator_id');
    }

    public function pricingRules()
    {
        return $this->hasMany(PpoPricingRuleModel::class, 'operator_id');
    }
}
