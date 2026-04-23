<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StoreModel extends Model
{
    protected $table = 'stores';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function detail(): HasOne
    {
        return $this->hasOne(StoreDetailModel::class, 'store_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'store_id');
    }
}