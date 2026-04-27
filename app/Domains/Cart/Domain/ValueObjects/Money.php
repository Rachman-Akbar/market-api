<?php

declare(strict_types=1);

namespace App\Domains\Cart\Domain\ValueObjects;

use DomainException;

final class Money
{
    public function __construct(private readonly int $amount)
    {
        if ($amount < 0) {
            throw new DomainException('Nominal uang tidak boleh negatif.');
        }
    }

    public static function fromInt(int $amount): self
    {
        return new self($amount);
    }

    public function value(): int
    {
        return $this->amount;
    }

    public function add(self $other): self
    {
        return new self($this->amount + $other->value());
    }

    public function multiply(int $multiplier): self
    {
        if ($multiplier < 0) {
            throw new DomainException('Multiplier uang tidak boleh negatif.');
        }

        return new self($this->amount * $multiplier);
    }
}
