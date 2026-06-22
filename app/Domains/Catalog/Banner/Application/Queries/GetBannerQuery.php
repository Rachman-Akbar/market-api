<?php

namespace App\Domains\Catalog\Banner\Application\Queries;

use App\Domains\Catalog\Banner\Domain\Repositories\BannerRepositoryInterface;
use App\Domains\Catalog\Banner\Application\Dtos\BannerData;

class GetBannerQuery
{
    public function __construct(private BannerRepositoryInterface $repository) {}

    public function execute(int $storeId): array
    {
        $entities = $this->repository->getByStoreId($storeId);

        return array_map(fn($entity) => BannerData::fromArray($entity->toArray()), $entities);
    }
}
