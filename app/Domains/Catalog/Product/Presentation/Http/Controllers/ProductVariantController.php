<?php

namespace App\Domains\Catalog\Product\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;

use App\Domains\Catalog\Product\Application\Query\ProductVariant\GetProductVariantQuery;
use App\Domains\Catalog\Product\Application\Query\ProductVariant\ListProductVariantsQuery;
use App\Domains\Catalog\Product\Presentation\Http\Resources\ProductVariantResource;

class ProductVariantController extends Controller
{
    public function index(
        int $productId,
        ListProductVariantsQuery $query
    ) {
        return ProductVariantResource::collection(
            $query->execute($productId)
        );
    }

    public function show(
        int $variantId,
        GetProductVariantQuery $query
    ) {
        $variant = $query->execute(
            $variantId
        );

        abort_if(!$variant, 404);

        return new ProductVariantResource(
            $variant
        );
    }
}
