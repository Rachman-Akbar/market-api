<?php

namespace App\Domains\Catalog\Banner\Domain\Repositories;

use App\Domains\Catalog\Banner\Domain\Entities\Banner;

interface BannerRepositoryInterface
{
    public function getByStoreId(int $storeId): array;
    public function findById(int $id): ?Banner;
    public function save(Banner $banner): Banner;
    public function delete(int $id): bool;
}
