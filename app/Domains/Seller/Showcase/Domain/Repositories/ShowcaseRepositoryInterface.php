<?php

declare(strict_types=1);

namespace App\Domains\Seller\Showcase\Domain\Repositories;

use App\Domains\Seller\Showcase\Infrastructure\Persistence\Models\ShowcaseModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ShowcaseRepositoryInterface
{
    public function paginate(array $filters, int $perPage, ?int $storeId): LengthAwarePaginator;

    public function find(int $id, ?int $storeId): ?ShowcaseModel;

    public function save(ShowcaseModel $model, array $productIds): ShowcaseModel;

    public function delete(ShowcaseModel $model): bool;
}
