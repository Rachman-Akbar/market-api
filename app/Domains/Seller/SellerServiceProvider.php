<?php

declare(strict_types=1);

namespace App\Domains\Seller;

use App\Domains\Seller\Stores\Application\ReadModels\ProductCatalogReaderInterface;
use App\Domains\Seller\Stores\Application\ReadModels\StoreCatalogReaderInterface;
use Illuminate\Support\ServiceProvider;
use App\Domains\Seller\Stores\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Repositories\EloquentStoreRepository;
use App\Domains\Seller\Stores\Infrastructure\ReadModels\EloquentProductCatalogReader;
use App\Domains\Seller\Stores\Infrastructure\ReadModels\Store\EloquentStoreCatalogReader;

final class SellerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Hubungkan Interface dengan Implementasi Eloquent-nya agar Laravel bisa melakukan Dependency Injection
        $this->app->bind(StoreRepositoryInterface::class,EloquentStoreRepository::class);
        $this->app->bind(StoreCatalogReaderInterface::class, EloquentStoreCatalogReader::class);
        $this->app->bind(ProductCatalogReaderInterface::class, EloquentProductCatalogReader::class);

    }
}
