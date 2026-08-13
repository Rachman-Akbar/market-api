<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Mission\Infrastructure\Persistence\Models;

use App\Domains\Order\Voucher\Domain\Entities\Voucher;
use App\Domains\Shared\Infrastructure\Persistence\Concerns\HasActiveStatus;
use App\Domains\Shared\Infrastructure\Persistence\Concerns\TracksUserChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class MissionModel extends Model
{
    use HasActiveStatus;
    use SoftDeletes;
    use TracksUserChanges;

    protected $table = 'missions';

    protected $fillable = [
        'voucher_id',
        'name',
        'code',
        'description',
        'event_type',
        'target_value',
        'conditions',
        'starts_at',
        'ends_at',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'voucher_id' => 'integer',
        'target_value' => 'integer',
        'conditions' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(MissionUserProgressModel::class, 'mission_id');
    }
}
