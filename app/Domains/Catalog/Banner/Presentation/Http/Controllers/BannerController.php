<?php

namespace App\Domains\Catalog\Banner\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Domains\Catalog\Banner\Application\Queries\GetBannerQuery;
use App\Domains\Catalog\Banner\Application\UseCases\UpsertBannerUseCase;
use App\Domains\Catalog\Banner\Application\UseCases\DeleteBannerUseCase;
use App\Domains\Catalog\Banner\Application\Dtos\BannerData;
use App\Domains\Catalog\Banner\Presentation\Http\Requests\BannerRequest;
use App\Domains\Catalog\Banner\Presentation\Http\Resources\BannerResource;

class BannerController extends Controller
{
    // GET /api/v1/catalog/shop-banners
    // Jika tidak membawa ?store_id=X di URL, otomatis mencari milik store_id 27
    public function index(Request $request, GetBannerQuery $query): JsonResponse
    {
        $storeId = (int) $request->query('store_id', 27);
        $banners = $query->execute($storeId);

        return response()->json(['data' => array_map(fn($b) => $b->toArray(), $banners)]);
    }
    // Ganti parameter pertama dari Request menjadi BannerRequest
    public function store(BannerRequest $request, UpsertBannerUseCase $useCase): BannerResource
    {
        // 1. Ambil data yang sudah lolos validasi dari BannerRequest
        $validatedData = $request->validated();

        // 2. Masukkan ke dalam DTO (store_id otomatis jadi 27 di dalam sini jika kosong)
        $dto = BannerData::fromArray($validatedData);

        // 3. Eksekusi Use Case
        $banner = $useCase->execute($dto);

        // 4. Kembalikan data menggunakan BannerResource (Otomatis status code 201)
        return new BannerResource($banner);
    }
    
    // PUT /api/v1/catalog/shop-banners/{id}
    public function update(BannerRequest $request, int $id, UpsertBannerUseCase $useCase): JsonResponse
    {
        $request->validate([
            'image_url' => 'required|string'
        ]);

        $dto = BannerData::fromArray($request->all());
        try {
            $banner = $useCase->execute($dto, $id);
            return response()->json(['message' => 'Banner toko berhasil diperbarui', 'data' => $banner->toArray()]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    // DELETE /api/v1/catalog/shop-banners/{id}
    public function destroy(int $id, DeleteBannerUseCase $useCase): JsonResponse
    {
        try {
            $useCase->execute($id);
            return response()->json(['message' => 'Banner toko berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
