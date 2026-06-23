<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Queries
use App\Domains\Seller\Stores\Application\Queries\ListStoreQuery;
use App\Domains\Seller\Stores\Application\Queries\GetStoreQuery;
use App\Domains\Seller\Stores\Application\Queries\GetStoreBySlugQuery;
use App\Domains\Seller\Stores\Application\Queries\GetStoreByIdQuery;
use App\Domains\Seller\Stores\Application\Queries\ListProductByStoreSlugQuery;

// Use Cases
use App\Domains\Seller\Stores\Application\UseCases\CreateStoreUseCase;
use App\Domains\Seller\Stores\Application\UseCases\UpdateStoreUseCase;

// Resources
use App\Domains\Seller\Stores\Presentation\Http\Resources\StoreResource;
use App\Domains\Seller\Stores\Presentation\Http\Resources\StoreListResource;
use App\Domains\Catalog\Product\Presentation\Http\Resources\ProductResource;

final class StoreController extends Controller
{
    // 1. Deklarasi Properti Dependensi
    private ListProductByStoreSlugQuery $listProductByStoreSlugQuery;
    private GetStoreByIdQuery $getStoreByIdQuery;

    // 2. Constructor untuk Dependency Injection
    public function __construct(
        ListProductByStoreSlugQuery $listProductByStoreSlugQuery,
        GetStoreByIdQuery $getStoreByIdQuery
    ) {
        $this->listProductByStoreSlugQuery = $listProductByStoreSlugQuery;
        $this->getStoreByIdQuery = $getStoreByIdQuery;
    }

    /**
     * GET /stores (Menampilkan Semua Toko)
     */
    public function index(Request $request, ListStoreQuery $query): AnonymousResourceCollection
    {
        $stores = $query->execute($request->query());
        return StoreListResource::collection($stores);
    }

    /**
     * GET /stores/slug/{slug} (Mencari Toko berdasarkan Slug)
     */
    public function showBySlug(string $slug, GetStoreBySlugQuery $query): StoreResource
    {
        $store = $query->execute($slug);
        abort_if(! $store, 404, 'Store not found.');

        return new StoreResource($store);
    }

    /**
     * GET /stores/{id} (Mencari Toko berdasarkan ID)
     */
public function showById(int $id): JsonResponse
{
    $store = $this->getStoreByIdQuery->execute($id);

    abort_if(! $store, 404, 'Store dengan ID tersebut tidak ditemukan.');

    // Ubah objek Entity menjadi array menggunakan Mapper sebelum dijadikan JSON
    $storeArray = \App\Domains\Seller\Stores\Infrastructure\Persistence\Mappers\StoreMapper::toModel($store);

    return response()->json($storeArray);
}
    /**
     * GET /stores/{slug}/products (List Produk Berdasarkan Slug Toko)
     */
    public function productsBySlug(Request $request, string $slug): JsonResponse
    {
        $filters = $request->only(['per_page', 'page', 'search']);
        $products = $this->listProductByStoreSlugQuery->execute($slug, $filters);

        return response()->json($products);
    }

    /**
     * POST /stores (Mendaftar/Membuat Toko Baru)
     */
    public function registerStore(Request $request, CreateStoreUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'phone'      => 'nullable|string',
            'city'       => 'nullable|string',
            'province'   => 'nullable|string',
            'address'    => 'nullable|string',
        ]);

        $userId = (string) $request->user()->id;
        $deviceName = $request->header('X-Device-Name') ?? 'web';

        $storeData = $useCase->execute($userId, $validated, $deviceName);

        return response()->json([
            'message' => 'Store registered successfully',
            'data'    => [
                'id'        => $storeData->id,
                'user_id'   => $storeData->userId,
                'name'      => $storeData->name,
                'slug'      => $storeData->slug,
                'is_active' => $storeData->isActive
            ]
        ], 201);
    }

    /**
     * PUT/PATCH /stores/{id} (Mengupdate Informasi Toko)
     */
  public function updateStore(int $id, Request $request, UpdateStoreUseCase $useCase): JsonResponse
{
    $validated = $request->validate([
        'store_name' => 'nullable|string|max:255',
        'address'    => 'nullable|string',
        'logo'       => 'nullable|string',
        'is_active'  => 'nullable|boolean',
    ]);

    // Ambil ID user yang login dan rolenya dari token / session auth Laravel
    $currentUserId = (string) $request->user()->id;
    $role = (string) $request->user()->role; // Pastikan kolom/attribute 'role' tersedia di model User Anda (misal: 'admin' atau 'seller')

    // Kirim data autentikasi ke dalam UseCase
    $storeData = $useCase->execute($id, $currentUserId, $role, $validated);

    return response()->json([
        'message' => 'Store updated successfully',
        'data'    => [
            'id'        => $storeData->id,
            'name'      => $storeData->name,
            'slug'      => $storeData->slug,
            'is_active' => $storeData->isActive
        ]
    ], 200);
}
}
