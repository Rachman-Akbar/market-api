<?php

namespace App\Domains\Catalog\Application\Actions;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

final class UpdateProductAction
{
    /**
     * @param array<string, mixed> $payload
     */
    public function execute(int $productId, string $sellerId, array $payload): Product
    {
        $product = Product::query()->findOrFail($productId);

        if ($product->seller_id !== $sellerId) {
            throw ValidationException::withMessages([
                'product' => ['You can only update your own product.'],
            ]);
        }

        $product->fill([
            'name' => $payload['name'] ?? $product->name,
            'description' => $payload['description'] ?? $product->description,
            'price' => $payload['price'] ?? $product->price,
            'status' => $payload['status'] ?? $product->status,
        ])->save();

        if (array_key_exists('category_ids', $payload) && is_array($payload['category_ids'])) {
            $product->categories()->sync($payload['category_ids']);
        }

        return $product->load(['images', 'categories', 'stock']);
    }
}
