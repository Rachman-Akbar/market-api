<?php

namespace App\Domains\Catalog\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\EloquentProductRepository;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /**
         * Repository Binding
         * Interface -> Implementation
         */
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}