<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Domains\Catalog\Product\Application\Query\Product\GetProductQuery;
use App\Domains\Catalog\Product\Application\Query\ProductVariant\GetProductVariantQuery;
use App\Domains\Catalog\Product\Application\Query\ProductVariant\ListProductVariantsQuery;
use App\Domains\Catalog\Product\Application\UseCases\ProductVariant\CreateProductVariantUseCase;
use App\Domains\Catalog\Product\Application\UseCases\ProductVariant\UpdateProductVariantUseCase;
use App\Domains\Catalog\Product\Application\UseCases\ProductVariant\DeleteProductVariantUseCase;
use App\Domains\Catalog\Product\Presentation\Http\Requests\StoreProductVariantRequest;
use App\Domains\Catalog\Product\Presentation\Http\Requests\UpdateProductVariantRequest;
use App\Domains\Catalog\Product\Presentation\Http\Resources\ProductVariantResource;

final class ProductVariantController extends Controller
{
    // --- PUBLIC ROUTE ---
    public function publicIndex(Request $request, int $productId, GetProductQuery $productQuery, ListProductVariantsQuery $query)
    {
        $product = $productQuery->execute($productId);
        
        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $variants = $query->execute($productId, $request->all());

        return ProductVariantResource::collection($variants);
    }

    // --- PROTECTED ROUTES (SELLER ONLY) ---
    public function index(Request $request, int $productId, GetProductQuery $productQuery, ListProductVariantsQuery $query)
    {
        $this->assertSellerProduct($request, $productId, $productQuery);

        $variants = $query->execute($productId, $request->all());

        return ProductVariantResource::collection($variants);
    }

    public function store(StoreProductVariantRequest $request, int $productId, GetProductQuery $productQuery, CreateProductVariantUseCase $useCase)
    {
        $this->assertSellerProduct($request, $productId, $productQuery);

        $variant = $useCase->execute($productId, $request->validated());

        return new ProductVariantResource($variant);
    }

    public function show(Request $request, int $productId, int $variantId, GetProductQuery $productQuery, GetProductVariantQuery $query)
    {
        $this->assertSellerProduct($request, $productId, $productQuery);

        $variant = $query->execute($variantId);

        if (! $variant || $variant->productId() !== $productId) {
            return response()->json(['message' => 'Product variant not found.'], 404);
        }

        return new ProductVariantResource($variant);
    }

    public function update(UpdateProductVariantRequest $request, int $productId, int $variantId, GetProductQuery $productQuery, GetProductVariantQuery $query, UpdateProductVariantUseCase $useCase)
    {
        $this->assertSellerProduct($request, $productId, $productQuery);

        $variant = $query->execute($variantId);

        if (! $variant || $variant->productId() !== $productId) {
            return response()->json(['message' => 'Product variant not found.'], 404);
        }

        $updated = $useCase->execute($variantId, $request->validated());

        return new ProductVariantResource($updated);
    }

    public function destroy(Request $request, int $productId, int $variantId, GetProductQuery $productQuery, GetProductVariantQuery $query, DeleteProductVariantUseCase $useCase)
    {
        $this->assertSellerProduct($request, $productId, $productQuery);

        $variant = $query->execute($variantId);

        if (! $variant || $variant->productId() !== $productId) {
            return response()->json(['message' => 'Product variant not found.'], 404);
        }

        $useCase->execute($variantId);

        return response()->json(['message' => 'Product variant deleted successfully.']);
    }

    // --- HELPER METOD ---
    private function assertSellerProduct(Request $request, int $productId, GetProductQuery $query): void
    {
        $product = $query->execute($productId);
        
        abort_if(! $product, 404, 'Product not found.');

        $user = $request->user();
        abort_if(! $user, 401, 'Unauthenticated.');

        abort_if($product->sellerId() !== (string) $user->getAuthIdentifier(), 403, 'Forbidden. This product does not belong to you.');
    }
}