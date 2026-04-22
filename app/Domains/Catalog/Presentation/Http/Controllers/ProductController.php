<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Domains\Catalog\Application\UseCases\Product\{
    CreateProductUseCase,
    ListProductsUseCase,
    GetProductUseCase,
    UpdateProductUseCase,
    DeleteProductUseCase
};

use App\Domains\Catalog\Presentation\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function index(
        Request $request,
        ListProductsUseCase $useCase
    ) {
        $products = $useCase->execute($request->all());

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
        GetProductUseCase $useCase
    ) {
        $product = $useCase->execute($id);

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


}
