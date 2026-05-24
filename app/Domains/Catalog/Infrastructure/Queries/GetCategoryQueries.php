<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Infrastructure\Queries;

use Illuminate\Support\Collection;
use App\Domains\Catalog\Infrastructure\Persistence\Models\CategoryModel;

final class GetCategoryDescendantsQuery
{
    public function execute(
        int $categoryId
    ): Collection {

        return CategoryModel::findOrFail($categoryId)
            ->descendantsAndSelf()
            ->get();
    }
}