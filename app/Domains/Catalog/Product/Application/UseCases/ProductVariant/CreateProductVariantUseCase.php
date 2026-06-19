<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Application\UseCases\ProductVariant;

use Illuminate\Support\Facades\DB;
use App\Domains\Catalog\Product\Domain\Entities\ProductVariant;
use App\Domains\Catalog\Product\Domain\Repositories\ProductVariantRepositoryInterface;

final class CreateProductVariantUseCase
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $variants
    ) {}

    public function execute(int $productId, array $data): ProductVariant
    {
        return DB::transaction(function () use ($productId, $data) {
            $variant = $this->variants->save(new ProductVariant(
                id: null,
                productId: $productId,
                sku: (string) $data['sku'],
                name: (string) $data['name'],
                price: (float) ($data['price'] ?? 0),
                stock: (int) ($data['stock'] ?? 0),
                isDefault: (bool) ($data['is_default'] ?? false)
            ));

            if (array_key_exists('values', $data)) {
                $this->variants->replaceValues(
                    variantId: (int) $variant->id(),
                    values: $data['values'] ?? []
                );
            }

            return $this->variants->findById((int) $variant->id());
        });
    }
}
