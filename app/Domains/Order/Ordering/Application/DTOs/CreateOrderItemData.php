<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Application\DTOs;

final readonly class CreateOrderItemData
{
    public function __construct(
        public int $productId,
        public string $productName,
        public ?string $sku,
        public int $quantity,
        public float $unitPrice,
        public string $currency = 'IDR',
    ) {
    }

    public static function fromArray(array $item): self
    {
        return new self(
            productId: (int) $item['product_id'],
            productName: (string) ($item['product_name'] ?? $item['name'] ?? 'Product'),
            sku: isset($item['sku']) ? (string) $item['sku'] : null,
            quantity: (int) $item['quantity'],
            unitPrice: (float) ($item['unit_price'] ?? $item['price']),
            currency: (string) ($item['currency'] ?? 'IDR'),
        );
    }

    public function stockPayload(): array
    {
        return [
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
        ];
    }
}
