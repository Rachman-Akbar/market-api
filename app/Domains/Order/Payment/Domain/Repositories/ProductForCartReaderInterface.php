<?php

declare(strict_types=1);

namespace App\Domains\Cart\Domain\Repositories;

interface ProductForCartReaderInterface
{
    /**
     * @return array{id:int,name:string,price:int,image:?string,stock:?int,is_active:bool}|null
     */
    public function findForCart(int $productId): ?array;
}
