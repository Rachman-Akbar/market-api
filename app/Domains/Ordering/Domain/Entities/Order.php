<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\Entities;

use App\Domains\Ordering\Domain\ValueObjects\Money;
use App\Domains\Ordering\Domain\ValueObjects\OrderNumber;
use App\Domains\Ordering\Domain\ValueObjects\OrderStatus;
use App\Domains\Ordering\Domain\ValueObjects\PaymentStatus;
use App\Domains\Ordering\Domain\ValueObjects\ShippingAddress;
use DateTimeInterface;
use DomainException;

final class Order
{
    /**
     * @param array<int, OrderItem> $items
     * @param array<int, OrderStatusHistory> $histories
     */
    public function __construct(
        private ?int $id,
        private OrderNumber $orderNumber,
        private int $userId,
        private OrderStatus $status,
        private PaymentStatus $paymentStatus,
        private ShippingAddress $shippingAddress,
        private array $items,
        private Money $subtotal,
        private Money $shippingCost,
        private Money $discountTotal,
        private Money $taxTotal,
        private Money $grandTotal,
        private ?string $notes = null,
        private ?string $paymentMethod = null,
        private array $histories = [],
        private ?DateTimeInterface $createdAt = null,
        private ?DateTimeInterface $updatedAt = null,
    ) {
        if ($userId <= 0) {
            throw new DomainException('Order user id is required.');
        }

        if ($items === []) {
            throw new DomainException('Order must have at least one item.');
        }
    }

    /** @param array<int, OrderItem> $items */
    public static function create(
        OrderNumber $orderNumber,
        int $userId,
        ShippingAddress $shippingAddress,
        array $items,
        Money $subtotal,
        ?string $notes = null,
        ?string $paymentMethod = null,
    ): self {
        $zero = Money::zero($subtotal->currency());
        $status = OrderStatus::pending();

        return new self(
            id: null,
            orderNumber: $orderNumber,
            userId: $userId,
            status: $status,
            paymentStatus: PaymentStatus::unpaid(),
            shippingAddress: $shippingAddress,
            items: $items,
            subtotal: $subtotal,
            shippingCost: $zero,
            discountTotal: $zero,
            taxTotal: $zero,
            grandTotal: $subtotal,
            notes: $notes,
            paymentMethod: $paymentMethod,
            histories: [OrderStatusHistory::create(null, $status, 'Order created', $userId)],
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function markPersisted(int $id): void
    {
        $this->id = $id;
    }

    public function orderNumber(): OrderNumber
    {
        return $this->orderNumber;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function paymentStatus(): PaymentStatus
    {
        return $this->paymentStatus;
    }

    public function shippingAddress(): ShippingAddress
    {
        return $this->shippingAddress;
    }

    /** @return array<int, OrderItem> */
    public function items(): array
    {
        return $this->items;
    }

    public function subtotal(): Money
    {
        return $this->subtotal;
    }

    public function shippingCost(): Money
    {
        return $this->shippingCost;
    }

    public function discountTotal(): Money
    {
        return $this->discountTotal;
    }

    public function taxTotal(): Money
    {
        return $this->taxTotal;
    }

    public function grandTotal(): Money
    {
        return $this->grandTotal;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function paymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /** @return array<int, OrderStatusHistory> */
    public function histories(): array
    {
        return $this->histories;
    }

    public function createdAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function changeStatus(OrderStatus $newStatus, ?string $note = null, ?int $changedBy = null): void
    {
        if ($this->status->equals($newStatus)) {
            return;
        }

        if ($this->status->isFinal()) {
            throw new DomainException('Final order status cannot be changed.');
        }

        $oldStatus = $this->status;
        $this->status = $newStatus;
        $this->histories[] = OrderStatusHistory::create($oldStatus, $newStatus, $note, $changedBy);
    }

    public function belongsTo(int $userId): bool
    {
        return $this->userId === $userId;
    }
}
