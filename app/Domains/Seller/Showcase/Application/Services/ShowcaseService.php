<?php

declare(strict_types=1);

namespace App\Domains\Seller\Showcase\Application\Services;

use App\Domains\Seller\Showcase\Domain\Repositories\ShowcaseRepositoryInterface;
use App\Domains\Seller\Showcase\Infrastructure\Persistence\Models\ShowcaseModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class ShowcaseService
{
    public function __construct(private ShowcaseRepositoryInterface $repository) {}

    public function paginate(array $filters, int $perPage, ?int $storeId): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage, $storeId);
    }

    public function find(int $id, ?int $storeId): ShowcaseModel
    {
        return $this->repository->find($id, $storeId)
            ?? throw new InvalidArgumentException('Etalase tidak ditemukan.');
    }

    public function save(array $data, ?int $id, ?int $sellerStoreId): ShowcaseModel
    {
        $storeId = $sellerStoreId ?? (int) ($data['store_id'] ?? 0);

        if ($storeId <= 0) {
            throw new InvalidArgumentException('Toko wajib dipilih.');
        }

        $productIds = array_values(array_unique(array_map('intval', $data['product_ids'] ?? [])));
        $ownedCount = DB::table('products')
            ->where('store_id', $storeId)
            ->whereIn('id', $productIds)
            ->whereNull('deleted_at')
            ->count();

        if ($ownedCount !== count($productIds)) {
            throw new InvalidArgumentException('Semua produk etalase harus berasal dari toko yang sama.');
        }

        $model = $id ? $this->find($id, $sellerStoreId) : new ShowcaseModel();
        $slug = Str::slug((string) $data['name']);
        $exists = ShowcaseModel::withTrashed()
            ->where('store_id', $storeId)
            ->where('slug', $slug)
            ->when($id, fn ($query) => $query->where('id', '!=', $id))
            ->exists();

        if ($exists) {
            throw new InvalidArgumentException('Nama etalase sudah digunakan pada toko ini.');
        }

        $model->fill([
            'store_id' => $storeId,
            'name' => trim((string) $data['name']),
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $this->repository->save($model, $productIds);
    }

    public function delete(int $id, ?int $storeId): void
    {
        $this->repository->delete($this->find($id, $storeId));
    }
}
