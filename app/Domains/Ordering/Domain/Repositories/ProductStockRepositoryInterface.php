<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\Repositories;

interface ProductStockRepositoryInterface
{
    /** @param array<int, array{product_id:int, quantity:int}> $items */
    public function assertProductsAreAvailable(array $items): void;

    /** @param array<int, array{product_id:int, quantity:int}> $items */
    public function decreaseMany(array $items): void;

    /** @param array<int, array{product_id:int, quantity:int}> $items */
    public function increaseMany(array $items): void;
}
