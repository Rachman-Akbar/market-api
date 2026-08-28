<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Repositories;

use App\Domains\PPOB\Domain\Entities\PpoFinanceEntry;

interface PpoFinanceRepositoryInterface
{
    public function createUniquely(array $data): ?PpoFinanceEntry;

    public function getByReferenceId(string $referenceId): array;
}
