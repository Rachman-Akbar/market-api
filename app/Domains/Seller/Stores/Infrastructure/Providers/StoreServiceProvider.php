<?php

declare(strict_types=1);

namespace App\Domains\Stores\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Stores\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Stores\Infrastructure\Persistence\Repositories\EloquentStoreRepository;
use App\Domains\Stores\Application\ReadModels\Product\ProductCatalogReaderInterface;
use App\Domains\Stores\Infrastructure\ReadModels\Product\EloquentProductCatalogReader;
use App\Domains\Stores\Application\ReadModels\Store\StoreCatalogReaderInterface;
use App\Domains\Stores\Infrastructure\ReadModels\Store\EloquentStoreCatalogReader;

final class StoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            StoreRepositoryInterface::class,
            EloquentStoreRepository::class,
        );

        $this->app->bind(
            ProductCatalogReaderInterface::class,
            EloquentProductCatalogReader::class,
        );

        $this->app->bind(
            StoreCatalogReaderInterface::class,
            EloquentStoreCatalogReader::class,
        );
    }
}