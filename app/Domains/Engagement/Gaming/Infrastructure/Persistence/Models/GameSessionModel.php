<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Gaming\Infrastructure\Persistence\Models;

use App\Domains\Identity\User\Domain\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GameSessionModel extends Model
{
    protected $table = 'game_sessions';

    protected $fillable = [
        'user_id',
        'game_type',
        'session_id',
        'difficulty',
        'score',
        'correct_count',
        'total_questions',
        'duration_seconds',
        'started_at',
        'completed_at',
        'answers',
        'validation_status',
        'validation_reason',
        'coins_awarded',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'user_id' => 'string',
        'score' => 'integer',
        'correct_count' => 'integer',
        'total_questions' => 'integer',
        'duration_seconds' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'answers' => 'array',
        'coins_awarded' => 'integer',
        'is_active' => 'boolean',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
