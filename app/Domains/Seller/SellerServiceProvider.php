<?php

declare(strict_types=1);

namespace App\Domains\Seller;

use App\Domains\Seller\Stores\Application\ReadModels\ProductCatalogReaderInterface;
use App\Domains\Seller\Stores\Application\ReadModels\StoreCatalogReaderInterface;
use App\Domains\Seller\Stores\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Repositories\EloquentStoreRepository;
use App\Domains\Seller\Stores\Infrastructure\ReadModels\EloquentProductCatalogReader;
use App\Domains\Seller\Stores\Infrastructure\ReadModels\Store\EloquentStoreCatalogReader;
use App\Domains\Seller\Stores\Infrastructure\Middleware\EnsureSellerStoreAvailable;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class SellerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StoreRepositoryInterface::class, EloquentStoreRepository::class);
        $this->app->bind(StoreCatalogReaderInterface::class, EloquentStoreCatalogReader::class);
        $this->app->bind(ProductCatalogReaderInterface::class, EloquentProductCatalogReader::class);
    }

    public function boot(Router $router): void
    {
        $router->aliasMiddleware('seller.store.available', EnsureSellerStoreAvailable::class);
    }
}
