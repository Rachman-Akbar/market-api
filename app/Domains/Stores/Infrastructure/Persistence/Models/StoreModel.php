<?php

declare(strict_types=1);

namespace App\Domains\Stores\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StoreModel extends Model
{
    protected $table = 'stores';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'short_description',
        'phone',
        'email',
        'city',
        'province',
        'address',
        'is_active',
        'logo',
        'banner_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function detail(): HasOne
    {
        return $this->hasOne(StoreDetailModel::class, 'store_id');
    }
}