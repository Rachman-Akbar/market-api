<?php

declare(strict_types=1);

namespace App\Domains\Cart\Domain\Entities;

use App\Domains\Cart\Domain\ValueObjects\CartStatus;
use App\Domains\Cart\Domain\ValueObjects\Money;
use App\Domains\Cart\Domain\ValueObjects\Quantity;
use DomainException;

final class Cart
{
    /** @var array<int, CartItem> */
    private array $items = [];

    /** @param CartItem[] $items */
    public function __construct(
        private readonly ?int $id,
        private readonly string $userId,
        private readonly ?string $activeUserId,
        private CartStatus $status,
        array $items = [],
    ) {
        if (trim($userId) === '') {
            throw new DomainException('User ID cart wajib diisi.');
        }

        foreach ($items as $item) {
            $this->items[$item->productId()] = $item;
        }
    }

    public static function newActive(string $userId): self
    {
        return new self(null, $userId, $userId, CartStatus::ACTIVE, []);
    }

    public function id(): ?int { return $this->id; }
    public function userId(): string { return $this->userId; }
    public function activeUserId(): ?string { return $this->activeUserId; }
    public function status(): CartStatus { return $this->status; }

    /** @return CartItem[] */
    public function items(): array
    {
        return array_values($this->items);
    }

    public function findItem(int $productId): ?CartItem
    {
        return $this->items[$productId] ?? null;
    }

    public function currentQuantityForProduct(int $productId): int
    {
        return $this->findItem($productId)?->quantity()->value() ?? 0;
    }

    public function addItem(
        int $productId,
        Quantity $quantity,
        Money $priceSnapshot,
        string $productNameSnapshot,
        ?string $productImageSnapshot = null,
    ): void {
        $this->assertEditable();

        $existing = $this->findItem($productId);

        if ($existing !== null) {
            $existing->increase($quantity);
            $existing->refreshSnapshot($priceSnapshot, $productNameSnapshot, $productImageSnapshot);
            return;
        }

        $this->items[$productId] = new CartItem(
            id: null,
            cartId: $this->id,
            productId: $productId,
            quantity: $quantity,
            priceSnapshot: $priceSnapshot,
            productNameSnapshot: $productNameSnapshot,
            productImageSnapshot: $productImageSnapshot,
        );
    }

    public function updateItemQuantity(int $productId, Quantity $quantity): void
    {
        $this->assertEditable();

        $item = $this->findItem($productId);

        if ($item === null) {
            throw new DomainException('Item tidak ditemukan di cart.');
        }

        $item->changeQuantity($quantity);
    }

    public function removeItem(int $productId): void
    {
        $this->assertEditable();
        unset($this->items[$productId]);
    }

    public function clear(): void
    {
        $this->assertEditable();
        $this->items = [];
    }

    public function totalQuantity(): int
    {
        return array_reduce(
            $this->items(),
            static fn (int $carry, CartItem $item): int => $carry + $item->quantity()->value(),
            0,
        );
    }

    public function subtotal(): Money
    {
        return array_reduce(
            $this->items(),
            static fn (Money $carry, CartItem $item): Money => $carry->add($item->subtotal()),
            Money::fromInt(0),
        );
    }

    private function assertEditable(): void
    {
        if (! $this->status->isEditable()) {
            throw new DomainException('Cart tidak bisa diubah karena statusnya bukan active.');
        }
    }
}
