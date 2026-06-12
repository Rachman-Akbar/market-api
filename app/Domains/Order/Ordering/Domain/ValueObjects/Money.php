<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

final readonly class Money implements JsonSerializable
{
    public function __construct(
        private float $amount,
        private string $currency = 'IDR',
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }

        if (trim($currency) === '') {
            throw new InvalidArgumentException('Currency cannot be empty.');
        }
    }

    public static function zero(string $currency = 'IDR'): self
    {
        return new self(0.0, $currency);
    }

    public function amount(): float
    {
        return round($this->amount, 2);
    }

    public function currency(): string
    {
        return strtoupper($this->currency);
    }

    public function add(self $money): self
    {
        $this->assertSameCurrency($money);

        return new self($this->amount() + $money->amount(), $this->currency());
    }

    public function multiply(int $multiplier): self
    {
        if ($multiplier < 0) {
            throw new InvalidArgumentException('Multiplier cannot be negative.');
        }

        return new self($this->amount() * $multiplier, $this->currency());
    }

    public function toDatabase(): string
    {
        return number_format($this->amount(), 2, '.', '');
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount(),
            'currency' => $this->currency(),
            'formatted' => $this->currency() . ' ' . number_format($this->amount(), 2, '.', ','),
        ];
    }

    private function assertSameCurrency(self $money): void
    {
        if ($this->currency() !== $money->currency()) {
            throw new InvalidArgumentException('Cannot calculate money with different currencies.');
        }
    }
}
