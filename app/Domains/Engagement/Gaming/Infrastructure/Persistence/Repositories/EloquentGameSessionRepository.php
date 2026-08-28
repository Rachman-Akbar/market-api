<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Gaming\Infrastructure\Persistence\Repositories;

use App\Domains\Engagement\Gaming\Domain\Repositories\GameSessionRepositoryInterface;
use App\Domains\Engagement\Gaming\Infrastructure\Persistence\Models\GameSessionModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentGameSessionRepository implements GameSessionRepositoryInterface
{
    public function findByUserTypeAndSession(string $userId, string $gameType, string $sessionId): ?GameSessionModel
    {
        return GameSessionModel::where('user_id', $userId)
            ->where('game_type', $gameType)
            ->where('session_id', $sessionId)
            ->first();
    }

    public function create(array $data): GameSessionModel
    {
        return GameSessionModel::create($data);
    }

    public function countUserSessionsToday(string $userId, string $gameType): int
    {
        return GameSessionModel::where('user_id', $userId)
            ->where('game_type', $gameType)
            ->where('validation_status', 'accepted')
            ->where('created_at', '>=', now()->startOfDay())
            ->count();
    }

    public function userSessions(string $userId, string $gameType, int $perPage = 20): LengthAwarePaginator
    {
        return GameSessionModel::where('user_id', $userId)
            ->where('game_type', $gameType)
            ->where('validation_status', 'accepted')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function leaderboard(string $gameType, int $limit = 20): array
    {
        return GameSessionModel::where('game_type', $gameType)
            ->where('validation_status', 'accepted')
            ->where('is_active', true)
            ->with('user:id,name,avatar')
            ->orderByDesc('score')
            ->orderBy('duration_seconds')
            ->limit($limit)
            ->get()
            ->map(fn (GameSessionModel $m) => [
                'user_id' => $m->user_id,
                'user_name' => $m->user?->name,
                'user_avatar' => $m->user?->avatar,
                'score' => $m->score,
                'correct_count' => $m->correct_count,
                'total_questions' => $m->total_questions,
                'duration_seconds' => $m->duration_seconds,
                'difficulty' => $m->difficulty,
                'created_at' => $m->created_at?->toDateTimeString(),
            ])
            ->all();
    }

    public function stats(string $userId, string $gameType): array
    {
        $row = GameSessionModel::where('user_id', $userId)
            ->where('game_type', $gameType)
            ->where('validation_status', 'accepted')
            ->selectRaw('
                COUNT(*) as plays,
                MAX(score) as best_score,
                COALESCE(SUM(score), 0) as total_score,
                COALESCE(AVG(score), 0) as avg_score
            ')
            ->first();

        return [
            'plays' => (int) ($row->plays ?? 0),
            'best_score' => (int) ($row->best_score ?? 0),
            'total_score' => (int) ($row->total_score ?? 0),
            'avg_score' => (float) ($row->avg_score ?? 0),
        ];
    }
}
