<?php

namespace App\Domains\Catalog\Banner\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class BannerModel extends Model
{
    protected $table = 'banners';

    protected $fillable = ['store_id', 'image_url', 'sort_order', 'is_active'];

    protected $casts = [
        'store_id'  => 'integer',
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];
}
