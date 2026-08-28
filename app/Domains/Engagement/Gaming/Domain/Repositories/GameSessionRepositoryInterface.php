<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Gaming\Domain\Repositories;

use App\Domains\Engagement\Gaming\Infrastructure\Persistence\Models\GameSessionModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GameSessionRepositoryInterface
{
    public function findByUserTypeAndSession(string $userId, string $gameType, string $sessionId): ?GameSessionModel;

    public function create(array $data): GameSessionModel;

    public function countUserSessionsToday(string $userId, string $gameType): int;

    public function userSessions(string $userId, string $gameType, int $perPage = 20): LengthAwarePaginator;

    public function leaderboard(string $gameType, int $limit = 20): array;

    public function stats(string $userId, string $gameType): array;
}
