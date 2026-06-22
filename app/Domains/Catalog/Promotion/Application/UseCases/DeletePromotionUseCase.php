<?php

namespace App\Domains\Catalog\Promotion\Application\UseCases;

use App\Domains\Catalog\Promotion\Domain\Repositories\PromotionRepositoryInterface;

class DeletePromotionUseCase
{
    public function __construct(
        private PromotionRepositoryInterface $repository
    ) {}

    public function execute(int $id): bool
    {
        if (!$this->repository->findById($id)) {
            throw new \Exception("Promosi tidak ditemukan.");
        }

        return $this->repository->delete($id);
    }
}
