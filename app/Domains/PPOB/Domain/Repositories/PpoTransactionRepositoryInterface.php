<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Repositories;

use App\Domains\PPOB\Domain\Entities\PpoTransaction;

interface PpoTransactionRepositoryInterface
{
    public function findById(int $id): ?PpoTransaction;

    public function findByReferenceId(string $referenceId): ?PpoTransaction;

    public function findByTrId(string $trId): ?PpoTransaction;

    public function create(array $data): PpoTransaction;

    public function update(int $id, array $data): PpoTransaction;

    public function getByUser(string $userId, array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
}
