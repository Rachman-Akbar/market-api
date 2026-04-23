<?php

namespace App\Domains\Catalog\Application\UseCases\Banner;

use App\Domains\Catalog\Domain\Repositories\BannerRepositoryInterface;
use App\Domains\Catalog\Application\DTOs\BannerData;

final class GetBannerUseCase
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