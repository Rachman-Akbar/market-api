<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Repositories;

use App\Domains\PPOB\Domain\Repositories\PpoTransactionLogRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionLogModel;

class EloquentPpoTransactionLogRepository implements PpoTransactionLogRepositoryInterface
{
    public function create(array $data): void
    {
        PpoTransactionLogModel::create($data);
    }
}
