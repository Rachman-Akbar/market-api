<?php

declare(strict_types=1);

namespace App\Domains\Stores\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Domains\Stores\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Stores\Infrastructure\Persistence\Repositories\EloquentStoreRepository;

final class StoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            StoreRepositoryInterface::class,
            EloquentStoreRepository::class
        );
    }

    public function boot(): void
    {
        Route::prefix('api/v1')
            ->name('api.v1.')
            ->middleware('api')
            ->group(__DIR__ . '/../../Presentation/routes.php');
    }
}
