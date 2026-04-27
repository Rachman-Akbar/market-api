<?php

declare(strict_types=1);

namespace App\Domains\Cart\Infrastructure\Services;

use App\Domains\Cart\Domain\Repositories\ProductForCartReaderInterface;
use DomainException;

final readonly class CartStockValidator
{
    public function __construct(private ProductForCartReaderInterface $products)
    {
    }

    /**
     * @return array{id:int,name:string,price:int,image:?string,stock:?int,is_active:bool}
     */
    public function ensureProductAvailable(int $productId, int $targetQuantity): array
    {
        if ($targetQuantity < 1) {
            throw new DomainException('Quantity minimal adalah 1.');
        }

        $product = $this->products->findForCart($productId);

        if ($product === null) {
            throw new DomainException('Produk tidak ditemukan.');
        }

        if (! ($product['is_active'] ?? true)) {
            throw new DomainException('Produk sedang tidak aktif.');
        }

        if ((int) $product['price'] < 0) {
            throw new DomainException('Harga produk tidak valid.');
        }

        $stock = $product['stock'] ?? null;

        if ($stock !== null && (int) $stock < $targetQuantity) {
            throw new DomainException('Stok produk tidak mencukupi.');
        }

        return $product;
    }
}
