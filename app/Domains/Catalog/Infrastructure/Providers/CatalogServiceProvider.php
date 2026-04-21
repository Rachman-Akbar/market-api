<?php

namespace App\Domains\Catalog\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Repositories\EloquentProductRepository;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class
        );
    }
}
