<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
    private const DEFAULT_SELLER_ID = '7d140c91-2c01-431f-9c5b-f41c629b1a06';

    /**
     * Mengambil ID Seller dari user yang login, atau fallback ke default seller.
     */
    private function resolveCurrentSellerId(Request $request): string
    {
        return $request->user()?->id ?? self::DEFAULT_SELLER_ID;
    }

    public function index(Request $request, ListProductsQuery $query)
    {
        $filters = $request->all();
        $filters['seller_id'] = $this->resolveCurrentSellerId($request);

        return ProductResource::collection($query->execute($filters));
    }

    public function showBySlug(Request $request, string $slug, GetProductBySlugQuery $query)
    {
        $product = $query->execute($slug);

        if (! $product) {
            return response()->json(['message' => "Produk dengan slug {$slug} tidak ditemukan."], 404);
        }

        if ($product->sellerId() !== $this->resolveCurrentSellerId($request)) {
            return response()->json(['message' => 'Produk ini bukan milik seller yang sedang digunakan.'], 403);
        }

        return new ProductResource($product);
    }

    public function sellerIndex(Request $request, ListSellerProductsQuery $query)
    {
        $products = $query->execute(
            sellerId: $this->resolveCurrentSellerId($request),
            filters: $request->all()
        );

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request, CreateProductUseCase $useCase)
    {
        $payload = $request->validated();

        // Memasukkan seller_id otomatis (auth atau default) ke payload
        $payload['seller_id'] = $this->resolveCurrentSellerId($request);

        try {
            $product = $useCase->execute($payload);
            return new ProductResource($product);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, int $id, GetProductQuery $query)
    {
        $product = $query->execute($id);

        if (! $product) {
            return response()->json(['message' => "Produk dengan ID {$id} tidak ditemukan."], 404);
        }

        if ($product->sellerId() !== $this->resolveCurrentSellerId($request)) {
            return response()->json(['message' => 'Produk ini bukan milik seller yang sedang digunakan.'], 403);
        }

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, int $id, GetProductQuery $query, UpdateProductUseCase $useCase)
    {
        $product = $query->execute($id);

        if (! $product) {
            return response()->json(['message' => "Produk dengan ID {$id} tidak ditemukan."], 404);
        }

        if ($product->sellerId() !== $this->resolveCurrentSellerId($request)) {
            return response()->json(['message' => 'Produk ini bukan milik seller yang sedang digunakan.'], 403);
        }

        $payload = $request->validated();
        unset($payload['seller_id'], $payload['store_id']);

        if (isset($payload['sku'])) {
            $payload['sku'] = is_string($payload['sku']) ? trim($payload['sku']) : $payload['sku'];
            if ($payload['sku'] === '') {
                unset($payload['sku']);
            }
        }

        $updated = $useCase->execute($id, $payload);

        return new ProductResource($updated);
    }

    public function destroy(Request $request, int $id, GetProductQuery $query, DeleteProductUseCase $useCase)
    {
        $product = $query->execute($id);

        if (! $product) {
            return response()->json(['message' => "Produk dengan ID {$id} tidak ditemukan."], 404);
        }

        if ($product->sellerId() !== $this->resolveCurrentSellerId($request)) {
            return response()->json(['message' => 'Produk ini bukan milik seller yang sedang digunakan.'], 403);
        }

        $useCase->execute($id);

        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }
}
