<?php

declare(strict_types=1);

namespace App\Domains\Order\Voucher\Infrastructure\Persistence\Repositories;

use App\Domains\Order\Voucher\Domain\Entities\Voucher;
use App\Domains\Order\Voucher\Domain\Repositories\VoucherRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentVoucherRepository implements VoucherRepositoryInterface
{
    public function getAll(array $filters = []): Collection
    {
        return Voucher::query()
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->when(isset($filters['store_id']), fn ($query) => $query->where('store_id', $filters['store_id']))
            ->when((bool) ($filters['active_now'] ?? false), function ($query): void {
                $query->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now())
                    ->where(function ($usageQuery): void {
                        $usageQuery->where('usage_limit', 0)
                            ->orWhereColumn('used_count', '<', 'usage_limit');
                    });
            })
            ->orderByDesc('created_at')
            ->get();
    }

    public function findById(int $id): ?Voucher
    {
        return Voucher::find($id);
    }

    public function findByCode(string $code): ?Voucher
    {
        return Voucher::where('code', strtoupper($code))->first();
    }

    public function save(Voucher $voucher): Voucher
    {
        $voucher->save();
        return $voucher;
    }

    public function delete(Voucher $voucher): bool
    {
        return (bool) $voucher->delete();
    }
}
