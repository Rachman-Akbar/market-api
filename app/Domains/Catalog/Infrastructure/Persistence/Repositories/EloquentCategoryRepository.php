<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CategoryModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\CategoryMapper;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CategoryModel::query()->with('catalogGroup');

        if (!empty($filters['catalog_group_id'])) {
            $query->where('catalog_group_id', $filters['catalog_group_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $query->withCount('products');

        $paginator = $query->latest()->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn ($model) => CategoryMapper::toEntity($model))
        );

        return $paginator;
    }

    public function findById(int $id): ?Category
    {
        $model = CategoryModel::with('catalogGroup')
            ->withCount('products')
            ->find($id);

        return $model ? CategoryMapper::toEntity($model) : null;
    }

    public function save(Category $category): Category
    {
        $model = $category->id()
            ? CategoryModel::find($category->id())
            : null;

        if (!$model) {
            $model = CategoryMapper::toModel($category);
        } else {
            $model->catalog_group_id = $category->catalogGroupId();
            $model->name = $category->name();
            $model->slug = $category->slug();
            $model->description = $category->description();
            $model->image_url = $category->imageUrl();
            $model->cover_image_url = $category->coverImageUrl();
            $model->is_active = $category->isActive();
        }

        $model->save();

        $model->load('catalogGroup');
        $model->loadCount('products');

        return CategoryMapper::toEntity($model);
    }

    public function delete(int $id): bool
    {
        return CategoryModel::where('id', $id)->delete() > 0;
    }
}