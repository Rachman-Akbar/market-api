<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class BannerModel extends Model
{
    protected $table = 'banners';

    protected $fillable = [
        'title',
        'image_url',
        'link_url',
        'is_active',
    ];
}
