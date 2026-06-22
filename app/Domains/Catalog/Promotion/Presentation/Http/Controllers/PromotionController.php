<?php

namespace App\Domains\Catalog\Promotion\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Domains\Catalog\Promotion\Application\Queries\GetPromotionQuery;
use App\Domains\Catalog\Promotion\Application\UseCases\UpsertPromotionUseCase;
use App\Domains\Catalog\Promotion\Application\UseCases\DeletePromotionUseCase;
use App\Domains\Catalog\Promotion\Application\Dtos\PromotionData;

class PromotionController extends Controller
{
    // GET /api/v1/catalog/promotions (Public - Home Page)
    public function index(GetPromotionQuery $query): JsonResponse
    {
        $promotions = $query->execute();

        // Memetakan DTO ke Array murni untuk response JSON
        $data = array_map(fn($item) => $item->toArray(), $promotions);

        return response()->json(['data' => $data]);
    }

    // POST /api/v1/catalog/promotions (Admin/Seller buat promo)
    public function store(Request $request, UpsertPromotionUseCase $useCase): JsonResponse
    {
        $request->validate([
            'image_url'    => 'required|string',
            'click_action' => 'required|in:none,product,category,url',
        ]);

        $dto = PromotionData::fromArray($request->all());
        $promotion = $useCase->execute($dto);

        return response()->json(['message' => 'Promosi berhasil ditambahkan', 'data' => $promotion->toArray()], 201);
    }

    // PUT /api/v1/catalog/promotions/{id}
    public function update(Request $request, int $id, UpsertPromotionUseCase $useCase): JsonResponse
    {
        $request->validate([
            'image_url'    => 'required|string',
            'click_action' => 'required|in:none,product,category,url',
        ]);

        $dto = PromotionData::fromArray($request->all());
        try {
            $promotion = $useCase->execute($dto, $id);
            return response()->json(['message' => 'Promosi berhasil diperbarui', 'data' => $promotion->toArray()]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    // DELETE /api/v1/catalog/promotions/{id}
    public function destroy(int $id, DeletePromotionUseCase $useCase): JsonResponse
    {
        try {
            $useCase->execute($id);
            return response()->json(['message' => 'Promosi berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
