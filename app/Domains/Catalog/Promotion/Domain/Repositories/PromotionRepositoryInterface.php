<?php

namespace App\Domains\Catalog\Promotion\Domain\Repositories;

use App\Domains\Catalog\Promotion\Domain\Entities\Promotion;

interface PromotionRepositoryInterface
{
    public function getAllActive(): array;
    public function findById(int $id): ?Promotion;
    public function save(Promotion $promotion): Promotion;
    public function delete(int $id): bool;
}
