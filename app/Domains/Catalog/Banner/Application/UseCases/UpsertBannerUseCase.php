<?php

namespace App\Domains\Catalog\Banner\Application\UseCases;

use App\Domains\Catalog\Banner\Domain\Repositories\BannerRepositoryInterface;
use App\Domains\Catalog\Banner\Domain\Entities\Banner;
use App\Domains\Catalog\Banner\Application\Dtos\BannerData;
use Exception;

class UpsertBannerUseCase
{
    public function __construct(private BannerRepositoryInterface $repository) {}

    public function execute(BannerData $dto, ?int $id = null): BannerData
    {
        if ($id && !$this->repository->findById($id)) {
            throw new Exception("Banner toko tidak ditemukan.");
        }

        $entity = new Banner(
            id: $id,
            storeId: $dto->storeId,
            imageUrl: $dto->imageUrl,
            sortOrder: $dto->sortOrder,
            isActive: $dto->isActive
        );

        $savedEntity = $this->repository->save($entity);
        return BannerData::fromArray($savedEntity->toArray());
    }
}
