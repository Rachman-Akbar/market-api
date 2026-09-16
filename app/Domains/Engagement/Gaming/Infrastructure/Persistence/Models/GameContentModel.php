<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Gaming\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class GameContentModel extends Model
{
    use SoftDeletes;

    protected $table = 'game_contents';

    protected $fillable = [
        'game_type',
        'title',
        'difficulty',
        'payload',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_active' => 'boolean',
        'created_by' => 'string',
        'updated_by' => 'string',
        'deleted_at' => 'datetime',
    ];
}
