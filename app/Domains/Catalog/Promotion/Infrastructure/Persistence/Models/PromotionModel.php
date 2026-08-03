<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Promotion\Infrastructure\Persistence\Models;

use App\Domains\Shared\Infrastructure\Persistence\Concerns\HasActiveStatus;
use App\Domains\Shared\Infrastructure\Persistence\Concerns\TracksUserChanges;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

final class PromotionModel extends Model
{
    use HasActiveStatus;
    use SoftDeletes;
    use TracksUserChanges;

    protected $table = 'promotions';

    protected $fillable = [
        'store_id',
        'name',
        'image_url',
        'mobile_image_url',
        'click_action',
        'target_id',
        'target_url',
        'sort_order',
        'is_active',
        'approval_status',
        'rejection_reason',
        'submitted_at',
        'approved_at',
        'approved_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'store_id' => 'integer',
        'target_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(StoreModel::class, 'store_id');
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): string => trim((string) preg_replace('/\s+/u', ' ', (string) $value))
        );
    }
}
