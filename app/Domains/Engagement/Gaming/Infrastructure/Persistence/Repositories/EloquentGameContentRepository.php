<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Gaming\Infrastructure\Persistence\Repositories;

use App\Domains\Engagement\Gaming\Infrastructure\Persistence\Models\GameContentModel;
use Illuminate\Support\Collection;

final class EloquentGameContentRepository
{
    public const SUPPORTED_TYPES = [
        'quiz',
        'myth_fact',
        'trash_sort',
        'match_card',
        'arithmetic_kilat',
    ];

    /** @return Collection<int, GameContentModel> */
    public function contentByType(string $gameType, ?string $difficulty = null): Collection
    {
        $query = GameContentModel::query()
            ->where('game_type', $gameType)
            ->where('is_active', true);

        if ($difficulty !== null && $difficulty !== '') {
            $query->where('difficulty', $difficulty);
        }

        return $query->orderBy('id')->get();
    }
}
