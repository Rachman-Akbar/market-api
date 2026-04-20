<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use App\Domains\Catalog\Application\UseCases\Category\CreateCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Category\GetCategoriesUseCase;
use App\Domains\Catalog\Application\UseCases\Category\GetCategoryDetailUseCase;
use App\Domains\Catalog\Presentation\Http\Requests\CategoryRequest;
use App\Domains\Catalog\Presentation\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    public function index(GetCategoriesUseCase $useCase, Request $request)
    {
        $categories = $useCase->execute($request->all(), $request->get('per_page', 15));
        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
            'message' => null
        ]);
    }

    public function show(GetCategoryDetailUseCase $useCase, $id)
    {
        $category = $useCase->execute($id);
        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category),
            'message' => null
        ]);
    }

    public function store(CreateCategoryUseCase $useCase, CategoryRequest $request)
    {
        $category = $useCase->execute($request->validated());
        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category),
            'message' => null
        ], 201);
    }
}
