<?php

declare(strict_types=1);

namespace App\Domains\Seller\Showcase\Infrastructure\Persistence\Repositories;

use App\Domains\Seller\Showcase\Domain\Repositories\ShowcaseRepositoryInterface;
use App\Domains\Seller\Showcase\Infrastructure\Persistence\Models\ShowcaseModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EloquentShowcaseRepository implements ShowcaseRepositoryInterface
{
    public function paginate(array $filters, int $perPage, ?int $storeId): LengthAwarePaginator
    {
        return ShowcaseModel::query()
            ->with(['store:id,name', 'products:id,store_id,name,slug,thumbnail,status,is_active'])
            ->withCount('products')
            ->when($storeId !== null, fn (Builder $query) => $query->where('store_id', $storeId))
            ->when(isset($filters['is_active']), fn (Builder $query) => $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)))
            ->when(trim((string) ($filters['search'] ?? '')) !== '', function (Builder $query) use ($filters): void {
                $search = trim((string) $filters['search']);
                $query->where(fn (Builder $query) => $query->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"));
            })
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate($perPage);
    }

    public function find(int $id, ?int $storeId): ?ShowcaseModel
    {
        return ShowcaseModel::query()
            ->with(['store:id,name', 'products:id,store_id,name,slug,thumbnail,status,is_active'])
            ->when($storeId !== null, fn (Builder $query) => $query->where('store_id', $storeId))
            ->find($id);
    }

    public function save(ShowcaseModel $model, array $productIds): ShowcaseModel
    {
        return DB::transaction(function () use ($model, $productIds): ShowcaseModel {
            $model->save();
            $sync = [];

            foreach (array_values(array_unique(array_map('intval', $productIds))) as $index => $productId) {
                $sync[$productId] = ['sort_order' => $index];
            }

            $model->products()->sync($sync);

            return $model->refresh()->load(['store:id,name', 'products:id,store_id,name,slug,thumbnail,status,is_active'])->loadCount('products');
        });
    }

    public function delete(ShowcaseModel $model): bool
    {
        return (bool) $model->delete();
    }
}
