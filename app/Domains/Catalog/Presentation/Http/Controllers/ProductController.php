<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;


use Illuminate\Routing\Controller;
use App\Domains\Catalog\Application\Actions\Product\CreateProductAction;
use App\Domains\Catalog\Application\Actions\Product\ListProductsAction;
use App\Domains\Catalog\Application\Actions\Product\GetProductAction;
use App\Domains\Catalog\Presentation\Http\Requests\ProductRequest;
use App\Domains\Catalog\Presentation\Http\Resources\ProductResource;

class ProductController extends Controller
{

    public function __construct(
        private CreateProductAction $createProduct,
        private ListProductsAction $listProducts,
        private GetProductAction $getProductDetail
    ) {}


    public function index(\Illuminate\Http\Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'min_price', 'max_price']);
        $perPage = $request->get('per_page', 15);
        $products = $this->listProducts->handle($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'message' => null,
        ]);
    }

    public function show($idOrSlug)
    {
        $product = $this->getProductDetail->handle($idOrSlug);

        if (!$product) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
            'message' => null,
        ]);
    }

    public function store(ProductRequest $request)
    {
        $dto = new \App\Domains\Catalog\Application\DTOs\CreateProductData($request->validated());
        $product = $this->createProduct->handle($dto);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
            'message' => null,
        ], 201);
    }
}
