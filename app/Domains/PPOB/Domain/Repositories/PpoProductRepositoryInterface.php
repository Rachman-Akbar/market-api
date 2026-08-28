<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Repositories;

use App\Domains\PPOB\Domain\Entities\PpoProduct;

interface PpoProductRepositoryInterface
{
    public function findById(int $id): ?PpoProduct;

    public function findByProviderCode(string $providerProductCode): ?PpoProduct;

    public function getAvailableByCategory(string $category, ?int $operatorId = null): array;

    public function getActiveByOperator(int $operatorId): array;

    public function create(array $data): PpoProduct;

    public function update(int $id, array $data): PpoProduct;

    public function delete(int $id): void;
}
