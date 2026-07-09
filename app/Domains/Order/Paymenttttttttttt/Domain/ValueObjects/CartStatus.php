<?php

declare(strict_types=1);

namespace App\Domains\Cart\Domain\ValueObjects;

use DomainException;

enum CartStatus: string
{
    case ACTIVE = 'active';
    case CHECKED_OUT = 'checked_out';
    case ABANDONED = 'abandoned';

    public function isEditable(): bool
    {
        return $this === self::ACTIVE;
    }

    public static function fromDatabase(string $value): self
    {
        return self::tryFrom($value)
            ?? throw new DomainException("Status cart tidak valid: {$value}");
    }
}
