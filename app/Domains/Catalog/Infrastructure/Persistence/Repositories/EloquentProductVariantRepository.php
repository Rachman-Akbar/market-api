<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Infrastructure\Persistence\Repositories;

use App\Domains\Catalog\Domain\Entities\ProductVariant;
use App\Domains\Catalog\Domain\Repositories\ProductVariantRepositoryInterface;
use App\Domains\Catalog\Infrastructure\Persistence\Mappers\ProductVariantMapper;
use App\Domains\Catalog\Infrastructure\Persistence\Models\ProductVariantModel;

final class EloquentProductVariantRepository
implements ProductVariantRepositoryInterface
{
    public function paginate(
        int $productId,
        int $perPage = 15
    ) {
        return ProductVariantModel::query()
            ->with('values')
            ->where('product_id', $productId)
            ->paginate($perPage)
            ->through(
                fn ($item)
                    => ProductVariantMapper::toEntity($item)
            );
    }

    public function findById(
        int $id
    ): ?ProductVariant {

        $model = ProductVariantModel::with('values')
            ->find($id);

        return $model
            ? ProductVariantMapper::toEntity($model)
            : null;
    }

    public function save(
        ProductVariant $variant
    ): ProductVariant {

        $model = ProductVariantModel::updateOrCreate(
            ['id' => $variant->id()],
            [
                'product_id' => $variant->productId(),
                'sku' => $variant->sku(),
                'name' => $variant->name(),
                'price' => $variant->price(),
                'stock' => $variant->stock(),
                'is_default' => $variant->isDefault(),
            ]
        );

        return ProductVariantMapper::toEntity(
            $model
        );
    }

    public function delete(
        int $id
    ): void {

        ProductVariantModel::destroy($id);
    }
}