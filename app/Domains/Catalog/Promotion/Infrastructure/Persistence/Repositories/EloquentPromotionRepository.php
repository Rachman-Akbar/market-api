<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Promotion\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Promotion\Domain\Entities\Promotion;
use App\Domains\Catalog\Promotion\Domain\Repositories\PromotionRepositoryInterface;
use App\Domains\Catalog\Promotion\Infrastructure\Persistence\Mappers\PromotionMapper;
use App\Domains\Catalog\Promotion\Infrastructure\Persistence\Models\PromotionModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class EloquentPromotionRepository implements PromotionRepositoryInterface
{
    public function getAll(array $filters = [], bool $includeInactive = false): array
    {
        return $this->applyFilters(PromotionModel::query(), $filters, $includeInactive)
            ->when(! $includeInactive, function (Builder $query): void {
                $query->whereRaw('LOWER(TRIM(approval_status)) = ?', ['approved'])
                    ->where(function (Builder $visibilityQuery): void {
                        $visibilityQuery->whereNull('store_id')
                            ->orWhereHas('store', fn (Builder $storeQuery) => $storeQuery->publiclyAvailable());
                    });
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (PromotionModel $model): Promotion => PromotionMapper::toEntity($model))
            ->all();
    }

    public function getByStoreId(int $storeId, array $filters = [], bool $includeInactive = true): array
    {
        return $this->applyFilters(PromotionModel::query()->where('store_id', $storeId), $filters, $includeInactive)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (PromotionModel $model): Promotion => PromotionMapper::toEntity($model))
            ->all();
    }

    public function findById(int $id, bool $includeInactive = true): ?Promotion
    {
        $model = PromotionModel::query()
            ->when(! $includeInactive, fn (Builder $query) => $query->active()->whereRaw('LOWER(TRIM(approval_status)) = ?', ['approved']))
            ->find($id);

        return $model ? PromotionMapper::toEntity($model) : null;
    }

    public function nameExists(string $name, ?int $ignoreId = null): bool
    {
        return PromotionModel::withTrashed()
            ->whereRaw('LOWER(TRIM(name)) = ?', [Str::lower(trim($name))])
            ->when($ignoreId !== null, fn (Builder $query) => $query->where($query->getModel()->getQualifiedKeyName(), '!=', $ignoreId))
            ->exists();
    }

    public function save(Promotion $promotion): Promotion
    {
        $model = $promotion->id
            ? PromotionModel::query()->findOrFail($promotion->id)
            : new PromotionModel;

        $model->fill([
            'store_id' => $promotion->storeId,
            'promotion_payment_id' => $promotion->promotionPaymentId,
            'name' => $promotion->name,
            'image_url' => $promotion->imageUrl,
            'mobile_image_url' => $promotion->mobileImageUrl,
            'click_action' => $promotion->clickAction,
            'target_id' => $promotion->targetId,
            'target_url' => $promotion->targetUrl,
            'sort_order' => $promotion->sortOrder,
            'is_active' => $promotion->isActive,
            'approval_status' => $promotion->approvalStatus,
            'rejection_reason' => $promotion->rejectionReason,
            'submitted_at' => $promotion->submittedAt,
            'approved_at' => $promotion->approvedAt,
            'approved_by' => $promotion->approvedBy,
        ])->save();

        return PromotionMapper::toEntity($model->refresh());
    }

    public function approve(int $id, string $approvedBy): Promotion
    {
        $model = PromotionModel::query()->findOrFail($id);
        $model->forceFill([
            'approval_status' => 'approved',
            'rejection_reason' => null,
            'approved_at' => now(),
            'approved_by' => $approvedBy,
        ])->save();

        return PromotionMapper::toEntity($model->refresh());
    }

    public function reject(int $id, string $reason, string $updatedBy): Promotion
    {
        $model = PromotionModel::query()->findOrFail($id);
        $model->forceFill([
            'approval_status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_at' => null,
            'approved_by' => null,
            'updated_by' => $updatedBy,
        ])->save();

        return PromotionMapper::toEntity($model->refresh());
    }

    public function delete(int $id): bool
    {
        $model = PromotionModel::query()->find($id);

        return $model ? (bool) $model->delete() : false;
    }

    private function applyFilters(Builder $query, array $filters, bool $includeInactive): Builder
    {
        return $query
            ->when(! $includeInactive, fn (Builder $query) => $query->active())
            ->when(array_key_exists('is_active', $filters), fn (Builder $query) => $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)))
            ->when(! empty($filters['approval_status']), fn (Builder $query) => $query->where('approval_status', (string) $filters['approval_status']))
            ->when(isset($filters['store_id']) && $filters['store_id'] !== '', fn (Builder $query) => $query->where('store_id', (int) $filters['store_id']))
            ->when(trim((string) ($filters['search'] ?? '')) !== '', function (Builder $query) use ($filters): void {
                $search = trim((string) $filters['search']);
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('target_url', 'like', "%{$search}%");
                });
            });
    }
}
