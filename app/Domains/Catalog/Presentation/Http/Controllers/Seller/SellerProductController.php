<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers\Seller;

use App\Domains\Catalog\Application\UseCases\Seller\Product\CreateSellerProductUseCase;
use App\Domains\Catalog\Application\UseCases\Seller\Product\DeleteSellerProductUseCase;
use App\Domains\Catalog\Application\UseCases\Seller\Product\ListSellerProductsUseCase;
use App\Domains\Catalog\Application\UseCases\Seller\Product\ShowSellerProductUseCase;
use App\Domains\Catalog\Application\UseCases\Seller\Product\UpdateSellerProductUseCase;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class SellerProductController extends Controller
{
    public function index(Request $request, ListSellerProductsUseCase $useCase): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_active' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json($useCase->execute($user, $filters));
    }

    public function store(Request $request, CreateSellerProductUseCase $useCase): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'primary_category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'store_category_ids' => ['sometimes', 'array'],
            'store_category_ids.*' => ['integer'],
            'store_catalog_group_ids' => ['sometimes', 'array'],
            'store_catalog_group_ids.*' => ['integer'],

            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:100'],
            'weight_gram' => ['nullable', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return response()->json($useCase->execute($user, $validated), 201);
    }

    public function show(
        Request $request,
        int|string $product,
        ShowSellerProductUseCase $useCase
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        return response()->json($useCase->execute($user, $product));
    }

    public function update(
        Request $request,
        int|string $product,
        UpdateSellerProductUseCase $useCase
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'primary_category_id' => ['sometimes', 'nullable', 'integer', Rule::exists('categories', 'id')],
            'store_category_ids' => ['sometimes', 'array'],
            'store_category_ids.*' => ['integer'],
            'store_catalog_group_ids' => ['sometimes', 'array'],
            'store_catalog_group_ids.*' => ['integer'],

            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sku' => ['sometimes', 'nullable', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string'],
            'short_description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'brand' => ['sometimes', 'nullable', 'string', 'max:100'],
            'weight_gram' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'thumbnail' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return response()->json($useCase->execute($user, $product, $validated));
    }

    public function destroy(
        Request $request,
        int|string $product,
        DeleteSellerProductUseCase $useCase
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $useCase->execute($user, $product);

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }
}
