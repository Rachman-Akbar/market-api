<?php

namespace App\Domains\Catalog\Promotion\Application\Queries;

use App\Domains\Catalog\Promotion\Domain\Repositories\PromotionRepositoryInterface;
use App\Domains\Catalog\Promotion\Application\Dtos\PromotionData;

class GetPromotionQuery
{
    public function __construct(
        private PromotionRepositoryInterface $repository
    ) {}

    public function execute(): array
    {
        $entities = $this->repository->getAllActive();

        return array_map(function ($entity) {
            return new PromotionData(
                id: $entity->id,
                imageUrl: $entity->imageUrl,
                mobileImageUrl: $entity->mobileImageUrl,
                clickAction: $entity->clickAction,
                targetId: $entity->targetId,
                targetUrl: $entity->targetUrl,
                sortOrder: $entity->sortOrder,
                isActive: $entity->isActive
            );
        }, $entities);
    }
}
