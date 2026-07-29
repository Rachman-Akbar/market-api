<?php

declare(strict_types=1);

namespace App\Domains\Template\Cart\Infrastructure\Providers;

use App\Domains\Template\Cart\Domain\Repositories\CartRepositoryInterface;
use App\Domains\Template\Cart\Domain\Repositories\ProductForCartReaderInterface;
use App\Domains\Template\Cart\Infrastructure\Persistence\Readers\EloquentProductForCartReader;
use App\Domains\Template\Cart\Infrastructure\Persistence\Repositories\EloquentCartRepository;
use Illuminate\Support\ServiceProvider;

final class CartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CartRepositoryInterface::class, EloquentCartRepository::class);
        $this->app->bind(ProductForCartReaderInterface::class, EloquentProductForCartReader::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/routes.php');
    }
}
