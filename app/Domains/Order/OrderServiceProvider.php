<?php

declare(strict_types=1);

namespace App\Domains\Order;

use App\Domains\Order\Addresses\Domain\Repositories\AddressRepositoryInterface;
use App\Domains\Order\Addresses\Infrastructure\Persistence\Repositories\EloquentAddressRepository;
use App\Domains\Order\Cart\Application\Readers\ProductForCartReaderInterface;
use App\Domains\Order\Cart\Infrastructure\Persistence\Readers\EloquentProductForCartReader;
use App\Domains\Order\Cart\Domain\Repositories\CartRepositoryInterface;
use App\Domains\Order\Cart\Infrastructure\Persistence\Repositories\EloquentCartRepository;
use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Repositories\EloquentOrderRepository;
use App\Domains\Order\Payment\Domain\Repositories\PaymentRepositoryInterface;
use App\Domains\Order\Payment\Infrastructure\Persistence\Repositories\EloquentPaymentRepository;
use App\Domains\Order\Wishlist\Domain\Repositories\WishlistRepositoryInterface;
use App\Domains\Order\Wishlist\Infrastructure\Persistence\Repositories\EloquentWishlistRepository;
use App\Domains\Order\Voucher\Domain\Repositories\VoucherRepositoryInterface;
use App\Domains\Order\Voucher\Infrastructure\Persistence\Repositories\EloquentVoucherRepository;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Interface ke Implementation secara global untuk Bounded Context Order
        $this->app->bind(CartRepositoryInterface::class, EloquentCartRepository::class);
        $this->app->bind(ProductForCartReaderInterface::class, EloquentProductForCartReader::class);

        $this->app->bind(AddressRepositoryInterface::class, EloquentAddressRepository::class);
        $this->app->bind(WishlistRepositoryInterface::class, EloquentWishlistRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, EloquentPaymentRepository::class);

        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);

        $this->app->bind(VoucherRepositoryInterface::class, EloquentVoucherRepository::class);

    }

    public function boot(): void
    {
        // Daftarkan routing atau config khusus Order jika ada di sini
    }
}
