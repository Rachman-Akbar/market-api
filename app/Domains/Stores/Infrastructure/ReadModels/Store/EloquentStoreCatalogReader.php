<?php

declare(strict_types=1);

namespace App\Domains\Stores\Infrastructure\ReadModels\Store;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Domains\Stores\Application\ReadModels\Store\StoreCatalogReaderInterface;

final class EloquentStoreCatalogReader implements StoreCatalogReaderInterface
{
    public function paginatedStores(array $filters = []): LengthAwarePaginator
    {
        $perPage = $this->normalizePerPage($filters['per_page'] ?? 8);

        $query = DB::table('stores')
            ->select([
                'id',
                'user_id',
                'name',
                'slug',
                'description',
                'short_description',
                'phone',
                'email',
                'city',
                'province',
                'address',
                'is_active',
                'logo',
                'banner_url',
                'created_at',
                'updated_at',
            ]);

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== '') {
            $query->where('is_active', $this->toBoolean($filters['is_active']));
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%')
                    ->orWhere('province', 'like', '%' . $search . '%');
            });
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    private function normalizePerPage(mixed $perPage): int
    {
        $perPage = (int) $perPage;

        if ($perPage < 1) {
            return 8;
        }

        return min($perPage, 24);
    }

    private function toBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}