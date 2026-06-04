<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;

use App\Domains\Catalog\Application\UseCases\ProductVariant\ListProductVariantsUseCase;
use App\Domains\Catalog\Application\UseCases\ProductVariant\GetProductVariantUseCase;

use App\Domains\Catalog\Presentation\Http\Resources\ProductVariantResource;

class ProductVariantController extends Controller
{
    public function index(
        int $productId,
        ListProductVariantsUseCase $useCase
    ) {
        return ProductVariantResource::collection(
            $useCase->execute($productId)
        );
    }

    public function show(
        int $variantId,
        GetProductVariantUseCase $useCase
    ) {
        $variant = $useCase->execute(
            $variantId
        );

        abort_if(!$variant, 404);

        return new ProductVariantResource(
            $variant
        );
    }
}