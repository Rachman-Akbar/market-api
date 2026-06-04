<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Domains\Catalog\Application\UseCases\Category\ListCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Category\ListCategoryMenuUseCase;
use App\Domains\Catalog\Application\UseCases\Category\GetCategoryBySlugUseCase;
use App\Domains\Catalog\Application\UseCases\Category\ListProductsByCategorySlugUseCase;
use App\Domains\Catalog\Application\UseCases\Category\CreateCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Category\UpdateCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Category\DeleteCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Category\GetCategoryByPathUseCase;
use App\Domains\Catalog\Application\UseCases\Category\GetHeaderMenuUseCase;
use App\Domains\Catalog\Application\UseCases\Category\ListProductsByCategoryPathUseCase;
use App\Domains\Catalog\Presentation\Http\Requests\CategoryRequest;
use App\Domains\Catalog\Presentation\Http\Resources\CategoryResource;
use App\Domains\Catalog\Presentation\Http\Resources\ProductResource;
use App\Domains\Catalog\Presentation\Http\Resources\CatalogGroupResource;

final class CategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori terstruktur (Tree Structure).
     */
    public function index(
        Request $request,
        ListCategoryUseCase $useCase
    ): JsonResponse {
        $categories = $useCase->execute();
        
        return response()->json([
            'success' => true,
            'data'    => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Menampilkan struktur menu kategori.
     */
    public function menu(
        Request $request,
        ListCategoryMenuUseCase $useCase
    ): JsonResponse {
        $categories = $useCase->execute();

        return response()->json([
            'success' => true,
            'data'    => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Menampilkan detail kategori berdasarkan Slug.
     */
    public function showBySlug(
        string $slug,
        GetCategoryBySlugUseCase $useCase
    ): JsonResponse {
        $category = $useCase->execute($slug);

        abort_if(! $category, 404, 'Category not found.');

        return response()->json([
            'success' => true,
            'data'    => new CategoryResource($category),
        ]);
    }

    /**
     * Menampilkan produk di dalam kategori berdasarkan Slug kategori.
     */
    public function productsBySlug(
        string $slug,
        Request $request,
        ListProductsByCategorySlugUseCase $useCase
    ): JsonResponse {
        $products = $useCase->execute($slug, $request->all());

        return response()->json([
            'success' => true,
            'data'    => ProductResource::collection($products),
        ]);
    }

    /**
     * Membuat kategori baru.
     */
    public function store(
        CategoryRequest $request,
        CreateCategoryUseCase $useCase
    ): JsonResponse {
        $category = $useCase->execute(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data'    => new CategoryResource($category),
            'message' => 'Category created successfully',
        ], 201);
    }

    /**
     * Mengubah data kategori.
     */
    public function update(
        int $id,
        CategoryRequest $request,
        UpdateCategoryUseCase $useCase
    ): JsonResponse {
        $category = $useCase->execute(
            $id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data'    => new CategoryResource($category),
            'message' => 'Category updated successfully',
        ]);
    }

    /**
     * Menghapus kategori.
     */
    public function destroy(
        int $id,
        DeleteCategoryUseCase $useCase
    ): JsonResponse {
        $useCase->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    /**
     * Menampilkan menu header berdasarkan kelompok katalog.
     */
    public function headerMenu(
        GetHeaderMenuUseCase $useCase
    ): JsonResponse {
        $catalogGroups = $useCase->execute();

        return response()->json([
            'catalogGroups' => CatalogGroupResource::collection($catalogGroups),
        ]);
    }

    /**
     * Menampilkan detail kategori berdasarkan Full URL Path.
     */
    public function showByPath(
        string $path,
        GetCategoryByPathUseCase $useCase
    ): JsonResponse {
        $category = $useCase->execute($path);

        abort_if(! $category, 404, 'Category not found.');

        return response()->json([
            'success' => true,
            'data'    => new CategoryResource($category),
        ]);
    }

    /**
     * Menampilkan produk di dalam kategori berdasarkan Full URL Path kategori.
     */
    public function productsByPath(
        string $path,
        Request $request,
        ListProductsByCategoryPathUseCase $useCase
    ): JsonResponse {
        $products = $useCase->execute(
            $path,
            $request->all(),
        );

        return response()->json([
            'success' => true,
            'data'    => ProductResource::collection($products),
        ]);
    }
}