<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\Services;

use App\Domains\Ordering\Domain\ValueObjects\OrderStatus;
use DomainException;

final class OrderStatusTransitionService
{
    private const ALLOWED = [
        OrderStatus::PENDING => [OrderStatus::CONFIRMED, OrderStatus::CANCELLED],
        OrderStatus::CONFIRMED => [OrderStatus::PROCESSING, OrderStatus::CANCELLED],
        OrderStatus::PROCESSING => [OrderStatus::SHIPPED, OrderStatus::CANCELLED],
        OrderStatus::SHIPPED => [OrderStatus::DELIVERED],
        OrderStatus::DELIVERED => [],
        OrderStatus::CANCELLED => [],
    ];

    public function assertCanTransition(OrderStatus $from, OrderStatus $to): void
    {
        if ($from->equals($to)) {
            return;
        }

        if (! in_array($to->value(), self::ALLOWED[$from->value()] ?? [], true)) {
            throw new DomainException("Cannot change order status from [{$from->value()}] to [{$to->value()}].");
        }
    }
}
