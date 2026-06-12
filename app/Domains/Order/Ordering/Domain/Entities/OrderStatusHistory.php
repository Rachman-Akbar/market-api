<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\Entities;

use App\Domains\Ordering\Domain\ValueObjects\OrderStatus;
use DateTimeImmutable;
use DateTimeInterface;

final class OrderStatusHistory
{
    public function __construct(
        private ?int $id,
        private ?int $orderId,
        private ?OrderStatus $fromStatus,
        private OrderStatus $toStatus,
        private ?string $note = null,
        private ?string $changedBy = null,
        private ?DateTimeInterface $createdAt = null,
    ) {
        $this->createdAt ??= new DateTimeImmutable();
    }

    public static function create(
        ?OrderStatus $fromStatus,
        OrderStatus $toStatus,
        ?string $note = null,
        ?string $changedBy = null,
    ): self {
        return new self(
            id: null,
            orderId: null,
            fromStatus: $fromStatus,
            toStatus: $toStatus,
            note: $note,
            changedBy: $changedBy,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function markPersisted(int $id, int $orderId): void
    {
        $this->id = $id;
        $this->orderId = $orderId;
    }

    public function orderId(): ?int
    {
        return $this->orderId;
    }

    public function fromStatus(): ?OrderStatus
    {
        return $this->fromStatus;
    }

    public function toStatus(): OrderStatus
    {
        return $this->toStatus;
    }

    public function note(): ?string
    {
        return $this->note;
    }

    public function changedBy(): ?string
    {
        return $this->changedBy;
    }

    public function createdAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }
}
