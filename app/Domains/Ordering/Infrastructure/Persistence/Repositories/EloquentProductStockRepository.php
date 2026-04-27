<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Persistence\Repositories;

use App\Domains\Ordering\Domain\Repositories\ProductStockRepositoryInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final class EloquentProductStockRepository implements ProductStockRepositoryInterface
{
    public function assertProductsAreAvailable(array $items): void
    {
        foreach ($items as $item) {
            $product = DB::table('products')
                ->where('id', (int) $item['product_id'])
                ->lockForUpdate()
                ->first();

            if (! $product) {
                throw new DomainException("Product [{$item['product_id']}] not found.");
            }

            if ((int) $product->stock < (int) $item['quantity']) {
                throw new DomainException("Insufficient stock for product [{$item['product_id']}].");
            }
        }
    }

    public function decreaseMany(array $items): void
    {
        foreach ($items as $item) {
            DB::table('products')
                ->where('id', (int) $item['product_id'])
                ->decrement('stock', (int) $item['quantity']);
        }
    }

    public function increaseMany(array $items): void
    {
        foreach ($items as $item) {
            DB::table('products')
                ->where('id', (int) $item['product_id'])
                ->increment('stock', (int) $item['quantity']);
        }
    }
}
