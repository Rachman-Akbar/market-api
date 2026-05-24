<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Domains\Catalog\Application\UseCases\Category\ListCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Category\ListCategoryMenuUseCase;
use App\Domains\Catalog\Application\UseCases\Category\GetCategoryBySlugUseCase;
use App\Domains\Catalog\Application\UseCases\Category\ListProductsByCategorySlugUseCase;
use App\Domains\Catalog\Presentation\Http\Resources\CategoryResource;
use App\Domains\Catalog\Presentation\Http\Resources\ProductResource;
use App\Domains\Catalog\Application\UseCases\Category\CreateCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Category\UpdateCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Category\DeleteCategoryUseCase;
use App\Domains\Catalog\Presentation\Http\Requests\CategoryRequest;

final class CategoryController extends Controller
{
    public function index(
    Request $request,
    ListCategoryUseCase $useCase
) {
    $categories = $useCase->execute($request->all());

    return response()->json([
        'success' => true,
        'data' => CategoryResource::collection($categories),
    ]);
}

    public function menu(
        Request $request,
        ListCategoryMenuUseCase $useCase
    ) {
        $categories = $useCase->execute();

        return CategoryResource::collection($categories);
    }

    public function showBySlug(
        string $slug,
        GetCategoryBySlugUseCase $useCase
    ) {
        $category = $useCase->execute($slug);

        abort_if(! $category, 404, 'Category not found.');

        return new CategoryResource($category);
    }

    public function productsBySlug(
        string $slug,
        Request $request,
        ListProductsByCategorySlugUseCase $useCase
    ) {
        $products = $useCase->execute($slug, $request->all());

        return ProductResource::collection($products);
    }


public function store(
    CategoryRequest $request,
    CreateCategoryUseCase $useCase
) {
    $category = $useCase->execute(
        $request->validated()
    );

    return response()->json([
        'success' => true,
        'data' => new CategoryResource($category),
        'message' => 'Category created successfully',
    ], 201);
}

public function update(
    int $id,
    CategoryRequest $request,
    UpdateCategoryUseCase $useCase
) {
    $category = $useCase->execute(
        $id,
        $request->validated()
    );

    return response()->json([
        'success' => true,
        'data' => new CategoryResource($category),
        'message' => 'Category updated successfully',
    ]);
}

public function destroy(
    int $id,
    DeleteCategoryUseCase $useCase
) {
    $useCase->execute($id);

    return response()->json([
        'success' => true,
        'message' => 'Category deleted successfully',
    ]);
}

}