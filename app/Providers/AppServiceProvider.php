<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Auth;
use App\Domains\Identity\Infrastructure\Firebase\FirebaseTokenVerifier;

// ✅ IMPORT INTERFACE
use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Domain\Repositories\CatalogGroupRepositoryInterface;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Domain\Repositories\StoreRepositoryInterface;

// ✅ IMPORT IMPLEMENTATION
use App\Domains\Catalog\Infrastructure\Persistence\Repositories\EloquentProductRepository;
use App\Domains\Catalog\Infrastructure\Persistence\Repositories\EloquentCatalogGroupRepository;
use App\Domains\Catalog\Infrastructure\Persistence\Repositories\EloquentCategoryRepository;
use App\Domains\Catalog\Infrastructure\Persistence\Repositories\EloquentStoreRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Firebase
        $this->app->bind(FirebaseTokenVerifier::class, function ($app) {
            $auth = $app->make(Auth::class);
            return new FirebaseTokenVerifier($auth);
        });

        // ✅ PRODUCT
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class
        );

        // ✅ ENTITY
        $this->app->bind(
            CatalogGroupRepositoryInterface::class,
            EloquentCatalogGroupRepository::class
        );

        // ✅ CATEGORY
        $this->app->bind(
            CategoryRepositoryInterface::class,
            EloquentCategoryRepository::class
        );
        // ✅ STORE
        $this->app->bind(
            StoreRepositoryInterface::class,
            EloquentStoreRepository::class
        );
    }

    public function boot(): void {}
}
