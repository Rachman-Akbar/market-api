<?php

declare(strict_types=1);

namespace App\Domains\Cart\Domain\ValueObjects;

use DomainException;

final class Quantity
{
    private const MIN = 1;
    private const MAX = 999;

    public function __construct(private readonly int $value)
    {
        if ($value < self::MIN) {
            throw new DomainException('Quantity minimal adalah 1.');
        }

        if ($value > self::MAX) {
            throw new DomainException('Quantity maksimal adalah 999.');
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value());
    }
}
