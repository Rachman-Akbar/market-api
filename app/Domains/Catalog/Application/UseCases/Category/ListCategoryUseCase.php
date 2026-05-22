<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Cache;

final class ListCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = 'categories_list_' . md5(json_encode($filters) . $perPage);

        return Cache::remember($cacheKey, 300, function () use ($filters, $perPage) {
            return $this->repository->paginate($filters, $perPage);
        });
    }
}