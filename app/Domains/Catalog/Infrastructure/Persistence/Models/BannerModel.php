<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Stores\Infrastructure\Persistence\Models\StoreModel;

final class BannerModel extends Model
{
    protected $table = 'banners';

    protected $fillable = [
        'store_id',
        'title',
        'subtitle',
        'image_url',
        'link_url',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(StoreModel::class, 'store_id');
    }
}
