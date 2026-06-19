<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Application\UseCases\ProductVariant;

use Illuminate\Support\Facades\DB;
use App\Domains\Catalog\Product\Domain\Entities\ProductVariant;
use App\Domains\Catalog\Product\Domain\Repositories\ProductVariantRepositoryInterface;

final class UpdateProductVariantUseCase
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $variants
    ) {}

    public function execute(int|string $variantId, array $data): ProductVariant
    {
        return DB::transaction(function () use ($variantId, $data) {
            $current = $this->variants->findById((int) $variantId);

            abort_if(! $current, 404, 'Product variant not found.');

            $variant = $this->variants->save(new ProductVariant(
                id: $current->id(),
                productId: $current->productId(),
                sku: (string) ($data['sku'] ?? $current->sku()),
                name: (string) ($data['name'] ?? $current->name()),
                price: (float) ($data['price'] ?? $current->price()),
                stock: (int) ($data['stock'] ?? $current->stock()),
                isDefault: array_key_exists('is_default', $data) ? (bool) $data['is_default'] : $current->isDefault()
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
