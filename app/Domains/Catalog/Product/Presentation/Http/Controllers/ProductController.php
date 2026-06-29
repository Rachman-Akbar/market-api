<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Domains\Catalog\Product\Application\Query\Product\GetProductQuery;
use App\Domains\Catalog\Product\Application\Query\Product\ListProductsQuery;
use App\Domains\Catalog\Product\Application\Query\Product\GetProductBySlugQuery;
use App\Domains\Catalog\Product\Application\Query\Product\ListSellerProductsQuery;
use App\Domains\Catalog\Product\Application\UseCases\Product\CreateProductUseCase;
use App\Domains\Catalog\Product\Application\UseCases\Product\UpdateProductUseCase;
use App\Domains\Catalog\Product\Application\UseCases\Product\DeleteProductUseCase;
use App\Domains\Catalog\Product\Presentation\Http\Requests\StoreProductRequest;
use App\Domains\Catalog\Product\Presentation\Http\Requests\UpdateProductRequest;
use App\Domains\Catalog\Product\Presentation\Http\Resources\ProductResource;

final class ProductController extends Controller
{
    private function resolveCurrentSellerId(Request $request): ?string
    {
        $userId = $request->user()?->id;
        return $userId ? (string) $userId : null;
    }

    private function resolveStoreIdBySellerId(string $sellerId): ?int
    {
        $storeId = DB::table('stores')->where('user_id', $sellerId)->value('id');
        return $storeId ? (int) $storeId : null;
    }

    // --- PUBLIC ROUTES ---
    public function index(Request $request, ListProductsQuery $query)
    {
        return ProductResource::collection($query->execute($request->all()));
    }

    public function showBySlug(string $slug, GetProductBySlugQuery $query)
    {
        $product = $query->execute($slug);
        if (! $product) {
            return response()->json(['message' => "Produk dengan slug '{$slug}' tidak ditemukan."], 404);
        }
        return new ProductResource($product);
    }

    public function show(int $id, GetProductQuery $query)
    {
        $product = $query->execute($id);
        if (! $product) {
            return response()->json(['message' => "Produk dengan ID {$id} tidak ditemukan."], 404);
        }
        return new ProductResource($product);
    }

    // --- PROTECTED ROUTES (SELLER ONLY) ---
    public function sellerIndex(Request $request, ListSellerProductsQuery $query)
    {
        $sellerId = $this->resolveCurrentSellerId($request);
        if (! $sellerId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $products = $query->execute(
            sellerId: $sellerId,
            filters: $request->all()
        );

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request, CreateProductUseCase $useCase)
    {
        $sellerId = $this->resolveCurrentSellerId($request);
        if (! $sellerId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $payload = $request->validated();
        $payload['seller_id'] = $sellerId;

        try {
            $product = $useCase->execute($payload);
            return new ProductResource($product);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(UpdateProductRequest $request, int $id, GetProductQuery $query, UpdateProductUseCase $useCase)
    {
        $sellerId = $this->resolveCurrentSellerId($request);
        if (! $sellerId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $product = $query->execute($id);
        if (! $product) {
            return response()->json(['message' => "Produk dengan ID {$id} tidak ditemukan."], 404);
        }

        // Perubahan: Otorisasi dialihkan ke pencocokan storeId hasil resolve DB
        $storeId = $this->resolveStoreIdBySellerId($sellerId);
        if ($product->storeId() !== $storeId) {
            return response()->json(['message' => 'Forbidden. Produk ini bukan milik toko Anda.'], 403);
        }

        $payload = $request->validated();
        unset($payload['seller_id'], $payload['store_id']);

        if (isset($payload['sku'])) {
            $payload['sku'] = is_string($payload['sku']) ? trim($payload['sku']) : $payload['sku'];
            if ($payload['sku'] === '') unset($payload['sku']);
        }

        $updated = $useCase->execute($id, $payload);
        return new ProductResource($updated);
    }

    public function destroy(Request $request, int $id, GetProductQuery $query, DeleteProductUseCase $useCase)
    {
        $sellerId = $this->resolveCurrentSellerId($request);
        if (! $sellerId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $product = $query->execute($id);
        if (! $product) {
            return response()->json(['message' => "Produk dengan ID {$id} tidak ditemukan."], 404);
        }

        // Perubahan: Mengamankan penghapusan via storeId matching
        $storeId = $this->resolveStoreIdBySellerId($sellerId);
        if ($product->storeId() !== $storeId) {
            return response()->json(['message' => 'Forbidden. Produk ini bukan milik toko Anda.'], 403);
        }

        $useCase->execute($id);
        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }
}