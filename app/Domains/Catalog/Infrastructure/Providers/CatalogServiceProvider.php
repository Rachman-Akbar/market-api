<?php

namespace App\Domains\Catalog\Infrastructure\Providers;

use App\Domains\Catalog\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Repositories\EloquentStoreRepository;
use App\Domains\Catalog\Domain\Repositories\BannerRepositoryInterface;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Repositories\EloquentBannerRepository;
use App\Domains\Catalog\Infrastructure\Persistence\Repositories\EloquentCatalogGroupRepository;
use App\Domains\Catalog\Infrastructure\Persistence\Repositories\EloquentCategoryRepository;
use App\Domains\Catalog\Infrastructure\Persistence\Repositories\EloquentProductRepository;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class
        );

        $this->app->bind(
            CategoryRepositoryInterface::class,
            EloquentCategoryRepository::class
        );

        $this->app->bind(
            CatalogGroupRepositoryInterface::class,
            EloquentCatalogGroupRepository::class
        );

        $this->app->bind(
            BannerRepositoryInterface::class,
            EloquentBannerRepository::class
        );

        $this->app->bind(
            StoreRepositoryInterface::class,
            EloquentStoreRepository::class
        );
    }
}
