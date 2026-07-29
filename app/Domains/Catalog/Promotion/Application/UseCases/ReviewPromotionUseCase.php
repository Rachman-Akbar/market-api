<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Promotion\Application\UseCases;

use App\Domains\Catalog\Promotion\Application\Dtos\PromotionData;
use App\Domains\Catalog\Promotion\Domain\Repositories\PromotionRepositoryInterface;
use InvalidArgumentException;

final class ReviewPromotionUseCase
{
    public function __construct(private PromotionRepositoryInterface $repository) {}

    public function approve(int $id, string $adminId): PromotionData
    {
        if (! $this->repository->findById($id, true)) {
            throw new InvalidArgumentException('Promosi tidak ditemukan.');
        }

        return PromotionData::fromArray($this->repository->approve($id, $adminId)->toArray());
    }

    public function reject(int $id, string $reason, string $adminId): PromotionData
    {
        if (! $this->repository->findById($id, true)) {
            throw new InvalidArgumentException('Promosi tidak ditemukan.');
        }

        return PromotionData::fromArray($this->repository->reject($id, $reason, $adminId)->toArray());
    }
}
