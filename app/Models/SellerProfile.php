<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Stores\Infrastructure\Persistence\Models\StoreModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SellerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'status',
        'verified_at',
        'suspended_at',
        'rejected_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(StoreModel::class);
    }
}