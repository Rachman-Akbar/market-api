<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Domains\Catalog\Application\UseCases\Category\ListCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Category\GetCategoryDetailUseCase;
use App\Domains\Catalog\Application\UseCases\Category\ListProductsByCategorySlugUseCase;
use App\Domains\Catalog\Presentation\Http\Resources\CategoryResource;
use App\Domains\Catalog\Presentation\Http\Resources\ProductResource;

final class CategoryController extends Controller
{
    public function index(
        Request $request,
        ListCategoryUseCase $useCase
    ) {
        $categories = $useCase->execute($request->all());

        return CategoryResource::collection($categories);
    }

    public function showBySlug(
        string $slug,
        GetCategoryDetailUseCase $useCase
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
}
