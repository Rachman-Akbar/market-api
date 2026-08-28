<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Repositories;

interface PpoTransactionLogRepositoryInterface
{
    public function create(array $data): void;
}
