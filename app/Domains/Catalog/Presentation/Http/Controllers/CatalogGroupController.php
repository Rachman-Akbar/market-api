<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Domains\Catalog\Application\UseCases\CatalogGroup\{
    CreateCatalogGroupUseCase,
    DeleteCatalogGroupUseCase,
    GetCatalogGroupsUseCase,
    GetCatalogGroupUseCase,
    UpdateCatalogGroupUseCase,
    GetCategoriesByCatalogGroupUseCase,
    GetCatalogGroupBySlugUseCase,
};
use App\Domains\Catalog\Presentation\Http\Requests\CatalogGroupRequest;
use App\Domains\Catalog\Presentation\Http\Resources\CatalogGroupResource;
use App\Domains\Catalog\Presentation\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;

class CatalogGroupController extends Controller
{
    public function index(GetCatalogGroupsUseCase $useCase): JsonResponse
    {
        $groups = $useCase->execute();

        return response()->json([
            'success' => true,
            'data' => CatalogGroupResource::collection($groups),
        ]);
    }

public function show(GetCatalogGroupUseCase $useCase, int $id): JsonResponse
{
    $group = $useCase->execute($id);

    if (!$group) {
        return response()->json([
            'success' => false,
            'message' => 'Catalog group not found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => new CatalogGroupResource($group),
    ]);
}

    public function store(CreateCatalogGroupUseCase $useCase, CatalogGroupRequest $request): JsonResponse
    {
        $group = $useCase->execute($request->validated());

        return response()->json([
            'success' => true,
            'data' => new CatalogGroupResource($group),
            'message' => 'Catalog group created successfully'
        ], 201);
    }

    public function update(UpdateCatalogGroupUseCase $useCase, CatalogGroupRequest $request, int $id): JsonResponse
    {
        $group = $useCase->execute($id, $request->validated());

        return response()->json([
            'success' => true,
            'data' => new CatalogGroupResource($group),
            'message' => 'Catalog group updated successfully'
        ]);
    }

    /**
     * NEW: Ambil semua kategori dari satu Catalog Group
     */
    public function categories(GetCategoriesByCatalogGroupUseCase $useCase, int $id): JsonResponse
    {
        $categories = $useCase->execute($id);

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
        ]);
    }

    public function showBySlug(
    GetCatalogGroupBySlugUseCase $useCase,
    string $slug
): JsonResponse {

    $group = $useCase->execute($slug);

    if (!$group) {
        return response()->json([
            'success' => false,
            'message' => 'Catalog group not found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => new CatalogGroupResource($group),
    ]);
}

public function destroy(DeleteCatalogGroupUseCase $useCase, int $id): JsonResponse
    {
        $deleted = $useCase->execute($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Catalog group not found or failed to delete'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Catalog group deleted successfully'
        ]);
    }

}

