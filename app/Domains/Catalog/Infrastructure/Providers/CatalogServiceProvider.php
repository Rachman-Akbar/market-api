<?php

namespace App\Domains\Catalog\Infrastructure\Providers;

use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Repositories\EloquentCatalogGroupRepository;
use App\Domains\Catalog\Infrastructure\Persistence\Repositories\EloquentCategoryRepository;
use App\Domains\Catalog\Infrastructure\Persistence\Repositories\EloquentProductRepository;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class,
            CategoryRepositoryInterface::class,
            EloquentCategoryRepository::class,
            CatalogGroupRepositoryInterface::class,
            EloquentCatalogGroupRepository::class
        );
    }
}
