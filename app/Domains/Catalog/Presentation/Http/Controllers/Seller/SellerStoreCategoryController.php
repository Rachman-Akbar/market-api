<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers\Seller;

use App\Domains\Catalog\Application\UseCases\Seller\StoreCategory\CreateStoreCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Seller\StoreCategory\DeleteStoreCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Seller\StoreCategory\ListStoreCategoriesUseCase;
use App\Domains\Catalog\Application\UseCases\Seller\StoreCategory\ShowStoreCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Seller\StoreCategory\UpdateStoreCategoryUseCase;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SellerStoreCategoryController extends Controller
{
    public function index(Request $request, ListStoreCategoriesUseCase $useCase): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $filters = $request->validate([
            'is_active' => ['nullable', 'boolean'],
        ]);

        return response()->json([
            'data' => $useCase->execute($user, $filters),
        ]);
    }

    public function store(Request $request, CreateStoreCategoryUseCase $useCase): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'parent_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return response()->json($useCase->execute($user, $validated), 201);
    }

    public function show(
        Request $request,
        int|string $storeCategory,
        ShowStoreCategoryUseCase $useCase
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        return response()->json($useCase->execute($user, $storeCategory));
    }

    public function update(
        Request $request,
        int|string $storeCategory,
        UpdateStoreCategoryUseCase $useCase
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'parent_id' => ['sometimes', 'nullable', 'integer'],
            'name' => ['sometimes', 'required', 'string', 'max:160'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return response()->json($useCase->execute($user, $storeCategory, $validated));
    }

    public function destroy(
        Request $request,
        int|string $storeCategory,
        DeleteStoreCategoryUseCase $useCase
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $useCase->execute($user, $storeCategory);

        return response()->json([
            'message' => 'Store category deleted successfully.',
        ]);
    }
}
