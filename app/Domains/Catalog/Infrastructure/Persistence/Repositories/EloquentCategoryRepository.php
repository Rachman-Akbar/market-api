<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Domain\Entities\Category;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CategoryModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\CategoryMapper;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $paginator = CategoryModel::latest()->paginate(15);

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn ($model) => CategoryMapper::toEntity($model))
        );

        return $paginator;
    }

    public function findById(string $id): ?Category
    {
        $model = CategoryModel::find($id);

        return $model
            ? CategoryMapper::toEntity($model)
            : null;
    }

    public function save(Category $category): Category
    {
        $model = CategoryModel::find($category->id())
            ?? CategoryMapper::toModel($category);

        $model->name = $category->name();
        $model->slug = $category->slug();
        $model->description = $category->description();

        $model->save();

        return CategoryMapper::toEntity($model);
    }

    public function delete(string $id): bool
    {
        return CategoryModel::where('id', $id)->delete() > 0;
    }
}