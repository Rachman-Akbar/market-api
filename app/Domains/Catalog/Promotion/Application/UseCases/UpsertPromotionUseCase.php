<?php

namespace App\Domains\Catalog\Promotion\Application\UseCases;

use App\Domains\Catalog\Promotion\Domain\Repositories\PromotionRepositoryInterface;
use App\Domains\Catalog\Promotion\Domain\Entities\Promotion as PromotionEntity;
use App\Domains\Catalog\Promotion\Application\Dtos\PromotionData;

class UpsertPromotionUseCase
{
    public function __construct(
        private PromotionRepositoryInterface $repository
    ) {}

    public function execute(PromotionData $dto, ?int $id = null): PromotionData
    {
        if ($id && !$this->repository->findById($id)) {
            throw new \Exception("Promosi tidak ditemukan.");
        }

        $entity = new PromotionEntity(
            id: $id,
            imageUrl: $dto->imageUrl,
            mobileImageUrl: $dto->mobileImageUrl,
            clickAction: $dto->clickAction,
            targetId: $dto->targetId,
            targetUrl: $dto->targetUrl,
            sortOrder: $dto->sortOrder,
            isActive: $dto->isActive
        );

        $savedEntity = $this->repository->save($entity);

        return PromotionData::fromArray($savedEntity->toArray());
    }
}
