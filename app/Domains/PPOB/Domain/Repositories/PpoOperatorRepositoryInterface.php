<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Repositories;

use App\Domains\PPOB\Domain\Entities\PpoOperator;

interface PpoOperatorRepositoryInterface
{
    public function findById(int $id): ?PpoOperator;

    public function getActiveByCategory(?string $category = null): array;

    public function create(array $data): PpoOperator;

    public function update(int $id, array $data): PpoOperator;

    public function delete(int $id): void;
}
