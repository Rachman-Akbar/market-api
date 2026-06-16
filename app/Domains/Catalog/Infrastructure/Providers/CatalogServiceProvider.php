<?php

namespace App\Domains\Catalog\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;

use App\Domains\Catalog\Banner\Domain\Repositories\BannerRepositoryInterface;
use App\Domains\Catalog\Banner\Infrastructure\Persistence\Repositories\EloquentBannerRepository;

use App\Domains\Catalog\Category\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\CatalogGroup\Domain\Repositories\CatalogGroupRepositoryInterface;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Repositories\EloquentCategoryRepository;
use App\Domains\Catalog\CatalogGroup\Infrastructure\Persistence\Repositories\EloquentCatalogGroupRepository;

use App\Domains\Catalog\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductVariantRepositoryInterface;
use App\Domains\Catalog\Product\Domain\Repositories\ProductAttributeRepositoryInterface;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Repositories\EloquentProductRepository;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Repositories\EloquentProductAttributeRepository;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Repositories\EloquentProductVariantRepository;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(CatalogGroupRepositoryInterface::class, EloquentCatalogGroupRepository::class);
        $this->app->bind(BannerRepositoryInterface::class, EloquentBannerRepository::class);
        $this->app->bind(ProductAttributeRepositoryInterface::class, EloquentProductAttributeRepository::class);
        $this->app->bind(ProductVariantRepositoryInterface::class, EloquentProductVariantRepository::class);
    }
}
