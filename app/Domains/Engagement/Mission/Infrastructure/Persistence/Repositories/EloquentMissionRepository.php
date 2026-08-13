<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Mission\Infrastructure\Persistence\Repositories;

use App\Domains\Engagement\Mission\Domain\Repositories\MissionRepositoryInterface;
use App\Domains\Engagement\Mission\Infrastructure\Persistence\Models\MissionModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class EloquentMissionRepository implements MissionRepositoryInterface
{
    public function paginate(array $filters, int $perPage, bool $admin): LengthAwarePaginator
    {
        return MissionModel::query()
            ->with('voucher:id,code,name,discount_type,discount_value,starts_at,ends_at,is_active')
            ->withCount('progresses')
            ->when(! $admin, fn (Builder $query) => $query->active()->where('starts_at', '<=', now())->where('ends_at', '>=', now()))
            ->when(! empty($filters['event_type']), fn (Builder $query) => $query->where('event_type', $filters['event_type']))
            ->when(isset($filters['is_active']), fn (Builder $query) => $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)))
            ->when(trim((string) ($filters['search'] ?? '')) !== '', function (Builder $query) use ($filters): void {
                $search = trim((string) $filters['search']);
                $query->where(fn (Builder $query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            })
            ->latest('starts_at')
            ->paginate($perPage);
    }

    public function find(int $id): ?MissionModel
    {
        return MissionModel::query()->with('voucher')->withCount('progresses')->find($id);
    }

    public function save(MissionModel $model): MissionModel
    {
        $model->save();

        return $model->refresh()->load('voucher')->loadCount('progresses');
    }

    public function delete(MissionModel $model): bool
    {
        return (bool) $model->delete();
    }
}
