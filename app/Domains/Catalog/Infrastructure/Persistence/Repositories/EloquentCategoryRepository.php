<?php

namespace App\Domains\Catalog\Infrastructure\Persistence;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CategoryModel;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    private function toEntity(CategoryModel $model): Category
    {
    return new Category(
        $model->id,
        $model->entity_id,
        $model->name,
        $model->slug,
        $model->description
    );
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Category::latest()->paginate(15);
    }

    public function findById(string $id)
    {
        return Category::find($id);
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update(string $id, array $data)
    {
        $category = Category::findOrFail($id);
        $category->update($data);

        return $category;
    }

    public function delete(string $id): bool
    {
        return Category::findOrFail($id)->delete();
    }

}
