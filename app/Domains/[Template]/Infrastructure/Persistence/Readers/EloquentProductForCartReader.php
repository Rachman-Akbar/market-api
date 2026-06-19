<?php

declare(strict_types=1);

namespace App\Domains\Cart\Infrastructure\Persistence\Readers;

use App\Domains\Cart\Domain\Repositories\ProductForCartReaderInterface;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

final class EloquentProductForCartReader implements ProductForCartReaderInterface
{
    public function findForCart(int $productId): ?array
    {
        $modelClass = $this->resolveProductModel();

        /** @var Model|null $product */
        $product = $modelClass::query()->find($productId);

        if ($product === null) {
            return null;
        }

        $fields = $this->fields();

        $stock = $product->getAttribute($fields['stock']);
        $isActiveValue = $product->getAttribute($fields['is_active']);

        return [
            'id' => (int) $product->getAttribute('id'),
            'name' => (string) $product->getAttribute($fields['name']),
            'price' => (int) $product->getAttribute($fields['price']),
            'image' => $product->getAttribute($fields['image']),
            'stock' => $stock === null ? null : (int) $stock,
            'is_active' => $isActiveValue === null ? true : (bool) $isActiveValue,
        ];
    }

    /** @return class-string<Model> */
    private function resolveProductModel(): string
    {
        $configured = config('cart.product_model');

        if (is_string($configured) && class_exists($configured)) {
            /** @var class-string<Model> $configured */
            return $configured;
        }

        $candidates = [
            'App\\Domains\\Catalog\\Infrastructure\\Persistence\\Models\\ProductModel',
            'App\\Models\\Product',
        ];

        foreach ($candidates as $candidate) {
            if (class_exists($candidate)) {
                /** @var class-string<Model> $candidate */
                return $candidate;
            }
        }

        throw new RuntimeException(
            'Product model untuk Cart belum ditemukan. Set config cart.product_model ke model Product Catalog kamu.'
        );
    }

    /** @return array{name:string,price:string,image:string,stock:string,is_active:string} */
    private function fields(): array
    {
        return array_merge([
            'name' => 'name',
            'price' => 'price',
            'image' => 'image',
            'stock' => 'stock',
            'is_active' => 'is_active',
        ], (array) config('cart.product_fields', []));
    }
}
