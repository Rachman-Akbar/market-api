<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Repositories;

use App\Domains\PPOB\Domain\Entities\PpoPricingRule;

interface PpoPricingRuleRepositoryInterface
{
    public function findById(int $id): ?PpoPricingRule;

    public function findMostSpecific(
        ?int $productId,
        ?int $operatorId,
        ?string $category
    ): ?PpoPricingRule;

    public function getActive(): array;

    public function create(array $data): PpoPricingRule;

    public function update(int $id, array $data): PpoPricingRule;

    public function delete(int $id): void;
}
