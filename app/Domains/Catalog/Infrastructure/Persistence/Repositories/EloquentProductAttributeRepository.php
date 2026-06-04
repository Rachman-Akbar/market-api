<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Domain\Repositories\ProductAttributeRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\ProductAttribute;
use App\Domains\Catalog\Infrastructure\Persistence\Models\ProductAttributeModel;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\ProductAttributeMapper;

final class EloquentProductAttributeRepository
implements ProductAttributeRepositoryInterface
{
    public function paginate(
        int $perPage = 15
    ) {
        return ProductAttributeModel::query()
            ->paginate($perPage)
            ->through(
                fn ($item)
                    => ProductAttributeMapper::toEntity($item)
            );
    }

    public function findById(
        int $id
    ): ?ProductAttribute {

        $model = ProductAttributeModel::find($id);

        return $model
            ? ProductAttributeMapper::toEntity($model)
            : null;
    }

    public function save(
        ProductAttribute $attribute
    ): ProductAttribute {

        $model = ProductAttributeModel::updateOrCreate(
            ['id' => $attribute->id()],
            [
                'name' => $attribute->name(),
                'slug' => $attribute->slug(),
                'type' => $attribute->type(),
                'is_active' => $attribute->isActive(),
            ]
        );

        return ProductAttributeMapper::toEntity($model);
    }

    public function delete(
        int $id
    ): void {

        ProductAttributeModel::destroy($id);
    }
}



