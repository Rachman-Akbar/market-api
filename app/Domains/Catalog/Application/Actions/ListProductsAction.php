<?php

namespace App\Domains\Catalog\Application\Actions;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

final class ListProductsAction
{
    /**
     * @return Collection<int, Product>
     */
    public function execute(): Collection
    {
        return Product::query()
            ->with(['images', 'categories', 'stock'])
            ->where('status', 'active')
            ->latest()
            ->get();
    }
}
