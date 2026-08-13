<?php

declare(strict_types=1);

namespace App\Domains\Seller\Showcase\Infrastructure\Persistence\Models;

use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductModel;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;
use App\Domains\Shared\Infrastructure\Persistence\Concerns\HasActiveStatus;
use App\Domains\Shared\Infrastructure\Persistence\Concerns\TracksUserChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ShowcaseModel extends Model
{
    use HasActiveStatus;
    use SoftDeletes;
    use TracksUserChanges;

    protected $table = 'showcases';

    protected $fillable = [
        'store_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'store_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(StoreModel::class, 'store_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(ProductModel::class, 'showcase_products', 'showcase_id', 'product_id')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }
}
