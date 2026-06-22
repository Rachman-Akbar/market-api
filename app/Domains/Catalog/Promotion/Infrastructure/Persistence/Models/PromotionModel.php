<?php

namespace App\Domains\Catalog\Promotion\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionModel extends Model
{
    protected $table = 'promotions';

    protected $fillable = [
        'image_url',
        'mobile_image_url',
        'click_action',
        'target_id',
        'target_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'target_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
