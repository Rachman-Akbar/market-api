<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

final readonly class PaymentStatus implements JsonSerializable
{
    public const UNPAID = 'unpaid';
    public const PENDING = 'pending';
    public const PAID = 'paid';
    public const FAILED = 'failed';
    public const REFUNDED = 'refunded';

    public function __construct(private string $value)
    {
        if (! in_array($value, self::values(), true)) {
            throw new InvalidArgumentException("Invalid payment status [{$value}].");
        }
    }

    public static function unpaid(): self
    {
        return new self(self::UNPAID);
    }

    public static function values(): array
    {
        return [
            self::UNPAID,
            self::PENDING,
            self::PAID,
            self::FAILED,
            self::REFUNDED,
        ];
    }

    public function value(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value();
    }
}
