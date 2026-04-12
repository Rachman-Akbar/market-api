<?php

namespace App\Domains\Identity\Domain\Entities;

final class Role
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}
}
