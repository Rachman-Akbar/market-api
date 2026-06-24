<?php

declare(strict_types=1);

namespace App\Domains\Order;

use App\Domains\Order\Cart\Application\Readers\ProductForCartReaderInterface;
use App\Domains\Order\Cart\Infrastructure\Persistence\Readers\EloquentProductForCartReader;
use App\Domains\Order\Cart\Domain\Repositories\CartRepositoryInterface;
use App\Domains\Order\Cart\Infrastructure\Persistence\Repositories\EloquentCartRepository;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Interface ke Implementation secara global untuk Bounded Context Order
        $this->app->bind(CartRepositoryInterface::class, EloquentCartRepository::class);
        $this->app->bind(ProductForCartReaderInterface::class, EloquentProductForCartReader::class);
    }

    public function boot(): void
    {
        // Daftarkan routing atau config khusus Order jika ada di sini
    }
}
