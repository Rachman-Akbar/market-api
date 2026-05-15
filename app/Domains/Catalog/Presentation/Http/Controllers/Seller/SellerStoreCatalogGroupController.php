<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers\Seller;

use App\Domains\Catalog\Application\UseCases\Seller\StoreCatalogGroup\CreateStoreCatalogGroupUseCase;
use App\Domains\Catalog\Application\UseCases\Seller\StoreCatalogGroup\DeleteStoreCatalogGroupUseCase;
use App\Domains\Catalog\Application\UseCases\Seller\StoreCatalogGroup\ListStoreCatalogGroupsUseCase;
use App\Domains\Catalog\Application\UseCases\Seller\StoreCatalogGroup\ShowStoreCatalogGroupUseCase;
use App\Domains\Catalog\Application\UseCases\Seller\StoreCatalogGroup\UpdateStoreCatalogGroupUseCase;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SellerStoreCatalogGroupController extends Controller
{
    public function index(Request $request, ListStoreCatalogGroupsUseCase $useCase): JsonResponse
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

    public function store(Request $request, CreateStoreCatalogGroupUseCase $useCase): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return response()->json($useCase->execute($user, $validated), 201);
    }

    public function show(
        Request $request,
        int|string $storeCatalogGroup,
        ShowStoreCatalogGroupUseCase $useCase
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        return response()->json($useCase->execute($user, $storeCatalogGroup));
    }

    public function update(
        Request $request,
        int|string $storeCatalogGroup,
        UpdateStoreCatalogGroupUseCase $useCase
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:160'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string'],
            'thumbnail' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return response()->json($useCase->execute($user, $storeCatalogGroup, $validated));
    }

    public function destroy(
        Request $request,
        int|string $storeCatalogGroup,
        DeleteStoreCatalogGroupUseCase $useCase
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $useCase->execute($user, $storeCatalogGroup);

        return response()->json([
            'message' => 'Store catalog group deleted successfully.',
        ]);
    }
}
