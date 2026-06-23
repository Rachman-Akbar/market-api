<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

use App\Domains\Seller\Stores\Application\Queries\ListStoreQuery;
use App\Domains\Seller\Stores\Application\Queries\GetStoreQuery;
use App\Domains\Seller\Stores\Application\Queries\ListProductByStoreSlugQuery;
use App\Domains\Seller\Stores\Application\UseCases\CreateStoreUseCase;

use App\Domains\Seller\Stores\Presentation\Http\Resources\StoreResource;
use App\Domains\Seller\Stores\Presentation\Http\Resources\StoreListResource;
use App\Domains\Catalog\Product\Presentation\Http\Resources\ProductResource;

final class StoreController extends Controller
{
    /**
     * Menangani POST /stores (Membuat Toko Baru)
     */
  /**
     * Menangani POST /stores (Membuat Toko Baru)
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

        // Eksekusi UseCase (Akan menghasilkan objek StoreData DTO)
        $storeData = $useCase->execute($userId, $validated, $deviceName);

        return response()->json([
            'message' => 'Store registered successfully',
            // Bungkus ke dalam array agar tidak memicu type mismatch di return JSON
            'data'    => [
                'id'       => $storeData->id,
                'user_id'  => $storeData->userId,
                'name'     => $storeData->name,
                'slug'     => $storeData->slug,
                'is_active'=> $storeData->isActive
            ]
        ], 201);
    }

    public function index(Request $request, ListStoreQuery $query): AnonymousResourceCollection 
    {
        $stores = $query->execute($request->query());
        return StoreListResource::collection($stores);
    }

    public function showBySlug(string $slug, GetStoreQuery $query): StoreResource 
    {
        $store = $query->execute($slug);
        abort_if(! $store, 404, 'Store not found.');
        return new StoreResource($store);
    }

    public function productsBySlug(string $slug, Request $request, ListProductByStoreSlugQuery $query): AnonymousResourceCollection 
    {
        $products = $query->execute($slug, $request->query());
        return ProductResource::collection($products);
    }
}