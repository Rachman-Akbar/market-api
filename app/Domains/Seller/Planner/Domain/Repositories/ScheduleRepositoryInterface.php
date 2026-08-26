<?php

declare(strict_types=1);

namespace App\Domains\Seller\Planner\Domain\Repositories;

use App\Domains\Seller\Planner\Domain\Entities\Schedule;

interface ScheduleRepositoryInterface
{
    public function findById(int $id): ?Schedule;

    public function getByUser(string $userId, array $filters = [], int $perPage = 20): mixed;

    public function getByDateRange(string $userId, string $fromDate, string $toDate): array;

    public function getGridData(string $userId, int $year, int $month): array;

    public function create(array $data): Schedule;

    public function update(int $id, array $data): Schedule;

    public function delete(int $id): void;

    public function markComplete(int $id): Schedule;
}
