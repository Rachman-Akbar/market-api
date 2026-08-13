<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Mission\Domain\Repositories;

use App\Domains\Engagement\Mission\Infrastructure\Persistence\Models\MissionModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MissionRepositoryInterface
{
    public function paginate(array $filters, int $perPage, bool $admin): LengthAwarePaginator;

    public function find(int $id): ?MissionModel;

    public function save(MissionModel $model): MissionModel;

    public function delete(MissionModel $model): bool;
}
