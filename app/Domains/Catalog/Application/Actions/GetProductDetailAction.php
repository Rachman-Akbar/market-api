<?php

namespace App\Domains\Catalog\Application\Actions;

use App\Models\Product;

final class GetProductDetailAction
{
    public function execute(int $productId): Product
    {
        return Product::query()
            ->with(['images', 'categories', 'stock'])
            ->findOrFail($productId);
    }
}
