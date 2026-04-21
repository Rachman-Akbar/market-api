<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use App\Domains\Catalog\Application\Actions\CreateProductAction;
use App\Domains\Catalog\Application\Actions\GetProductDetailAction;
use App\Domains\Catalog\Application\Actions\UpdateProductAction;
use App\Domains\Catalog\Application\UseCases\Product\ListProductsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CatalogController extends Controller
{
    public function index(ListProductsUseCase $action): JsonResponse
    {
        return response()->json([
            'data' => $action->execute(),
        ]);
    }

    public function show(int $id, GetProductDetailAction $action): JsonResponse
    {
        return response()->json([
            'data' => $action->execute($id),
        ]);
    }

    public function store(Request $request, CreateProductAction $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'string'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $product = $action->execute($user->id, $validated);

        return response()->json([
            'data' => $product,
        ], 201);
    }

    public function update(int $id, Request $request, UpdateProductAction $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $product = $action->execute($id, $user->id, $validated);

        return response()->json([
            'data' => $product,
        ]);
    }
}
