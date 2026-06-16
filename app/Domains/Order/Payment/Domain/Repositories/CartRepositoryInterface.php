<?php

declare(strict_types=1);

namespace App\Domains\Cart\Domain\Repositories;

use App\Domains\Cart\Domain\Entities\Cart;

interface CartRepositoryInterface
{
    public function findActiveByUserId(string $userId, bool $lock = false): ?Cart;

    public function getOrCreateActiveByUserId(string $userId, bool $lock = false): Cart;

    public function save(Cart $cart): Cart;
}
