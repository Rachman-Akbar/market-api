<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Mission\Infrastructure\Persistence\Models;

use App\Domains\Identity\User\Domain\Entities\User;
use App\Domains\Order\Voucher\Domain\Entities\Voucher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MissionUserProgressModel extends Model
{
    protected $table = 'mission_user_progress';

    protected $fillable = [
        'mission_id',
        'user_id',
        'progress_value',
        'status',
        'completed_at',
        'rewarded_at',
        'reward_voucher_id',
        'metadata',
    ];

    protected $casts = [
        'mission_id' => 'integer',
        'progress_value' => 'integer',
        'completed_at' => 'datetime',
        'rewarded_at' => 'datetime',
        'reward_voucher_id' => 'integer',
        'metadata' => 'array',
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(MissionModel::class, 'mission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rewardVoucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'reward_voucher_id');
    }
}
