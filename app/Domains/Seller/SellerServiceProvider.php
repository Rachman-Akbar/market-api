<?php

declare(strict_types=1);

namespace App\Domains\Seller;

use Illuminate\Support\ServiceProvider;
use App\Domains\Seller\Stores\Domain\Repositories\StoreRepositoryInterface;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Repositories\EloquentStoreRepository;

final class SellerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Hubungkan Interface dengan Implementasi Eloquent-nya agar Laravel bisa melakukan Dependency Injection
        $this->app->bind(StoreRepositoryInterface::class,EloquentStoreRepository::class);
    }
}