<?php

namespace App\Domains\Catalog\Banner\Application\Queries;

use App\Domains\Catalog\Banner\Application\DTOs\BannerData;
use App\Domains\Catalog\Banner\Domain\Repositories\BannerRepositoryInterface;

final class GetBannerQuery
{
    public function __construct(
        private BannerRepositoryInterface $repository
    ) {}

    /**
     * @return array<int, BannerData>
     */
    public function execute(): array
    {
        $banners = $this->repository->all();

        return array_map(
            fn ($banner) => new BannerData(
                id: $banner->id(),
                title: $banner->title(),
                imageUrl: $banner->imageUrl(),
                linkUrl: $banner->linkUrl(),
                isActive: $banner->isActive()
            ),
            $banners
        );
    }
}
