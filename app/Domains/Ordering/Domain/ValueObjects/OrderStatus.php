<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

final readonly class OrderStatus implements JsonSerializable
{
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const PROCESSING = 'processing';
    public const SHIPPED = 'shipped';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';

    public function __construct(private string $value)
    {
        if (! in_array($value, self::values(), true)) {
            throw new InvalidArgumentException("Invalid order status [{$value}].");
        }
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function values(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::PROCESSING,
            self::SHIPPED,
            self::DELIVERED,
            self::CANCELLED,
        ];
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $status): bool
    {
        return $this->value() === $status->value();
    }

    public function isFinal(): bool
    {
        return in_array($this->value(), [self::DELIVERED, self::CANCELLED], true);
    }

    public function jsonSerialize(): string
    {
        return $this->value();
    }
}
