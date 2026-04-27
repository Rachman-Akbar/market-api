<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Providers;

use App\Domains\Ordering\Domain\Repositories\CartForOrderReaderInterface;
use App\Domains\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Ordering\Domain\Repositories\ProductStockRepositoryInterface;
use App\Domains\Ordering\Infrastructure\Persistence\Readers\EloquentCartForOrderReader;
use App\Domains\Ordering\Infrastructure\Persistence\Repositories\EloquentOrderRepository;
use App\Domains\Ordering\Infrastructure\Persistence\Repositories\EloquentProductStockRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class OrderingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(CartForOrderReaderInterface::class, EloquentCartForOrderReader::class);
        $this->app->bind(ProductStockRepositoryInterface::class, EloquentProductStockRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api/v1')
            ->name('api.v1.')
            ->group(__DIR__ . '/../../Presentation/routes.php');

        $this->loadMigrationsFrom(__DIR__ . '/../Persistence/Migrations');
    }
}
