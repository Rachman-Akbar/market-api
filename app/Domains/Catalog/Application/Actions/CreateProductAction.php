<?php

namespace App\Domains\Catalog\Application\Actions;

use App\Domains\Catalog\Domain\Services\ProductStatusService;
use App\Events\ProductCreated;
use App\Models\Product;
use Illuminate\Support\Str;

final class CreateProductAction
{
    public function __construct(private readonly ProductStatusService $statusService) {}

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(string $sellerId, array $payload): Product
    {
        $product = Product::query()->create([
            'seller_id' => $sellerId,
            'name' => $payload['name'],
            'slug' => Str::slug($payload['name']) . '-' . Str::lower(Str::random(6)),
            'description' => $payload['description'] ?? null,
            'price' => $payload['price'],
            'status' => $this->statusService->normalize((string) ($payload['status'] ?? 'draft')),
        ]);

        if (! empty($payload['category_ids']) && is_array($payload['category_ids'])) {
            $product->categories()->sync($payload['category_ids']);
        }

        if (! empty($payload['images']) && is_array($payload['images'])) {
            foreach ($payload['images'] as $index => $url) {
                $product->images()->create([
                    'url' => (string) $url,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        $product->stock()->create([
            'quantity' => 0,
            'reserved_quantity' => 0,
        ]);

        event(new ProductCreated($product->id, $product->seller_id));

        return $product->load(['images', 'categories', 'stock']);
    }
}
