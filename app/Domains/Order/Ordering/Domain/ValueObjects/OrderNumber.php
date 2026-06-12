<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

final readonly class OrderNumber implements JsonSerializable
{
    public function __construct(private string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('Order number cannot be empty.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value();
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
