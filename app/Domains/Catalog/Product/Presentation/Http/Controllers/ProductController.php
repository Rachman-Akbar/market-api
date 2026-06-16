<?php

namespace App\Domains\Catalog\Product\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Domains\Catalog\Product\Application\UseCases\Product\{
    CreateProductUseCase,
    UpdateProductUseCase,
    DeleteProductUseCase
};

use App\Domains\Catalog\Product\Application\Queries\Product\{
    ListProductsQuery,
    GetProductBySlugQuery
};

use App\Domains\Catalog\Product\Presentation\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function index(
        Request $request,
        ListProductsQuery $query
    ) {
        $products = $query ->execute($request->all());

        return ProductResource::collection($products);
    }

    public function store(
        Request $request,
        CreateProductUseCase $useCase
    ) {
        $product = $useCase->execute($request->all());

        return new ProductResource($product);
    }

    public function show(
        string $id,
        GetProductBySlugQuery $query
    ) {
        $product = $query->execute($id);

        abort_if(!$product, 404);

        return new ProductResource($product);
    }

    public function update(
        Request $request,
        string $id,
        UpdateProductUseCase $useCase
    ) {
        $product = $useCase->execute($id, $request->all());

        return new ProductResource($product);
    }

    public function destroy(
        string $id,
        DeleteProductUseCase $useCase
    ) {
        $useCase->execute($id);

        return response()->json([
            'message' => 'Product deleted'
        ]);
    }


public function showBySlug(
    string $slug,
    ProductGetProductBySlugQuery $query
) {
    $product = $query->execute($slug);

    abort_if(!$product, 404);

    return new ProductResource($product);
}


}



