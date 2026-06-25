<?php

namespace App\Domains\Order\Wishlist\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Order\Wishlist\Application\UseCases\AddItemToWishlistUseCase;
use App\Domains\Order\Wishlist\Application\UseCases\GetWishlistUseCase;
use App\Domains\Order\Wishlist\Application\UseCases\RemoveItemFromWishlistUseCase;
use App\Domains\Order\Wishlist\Application\DTOs\WishlistInputDto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use DomainException;

class WishlistController extends Controller
{
    public function __construct(
        private readonly GetWishlistUseCase $getWishlistUseCase,
        private readonly AddItemToWishlistUseCase $addItemUseCase,
        private readonly RemoveItemFromWishlistUseCase $removeItemUseCase
    ) {}

    /**
     * Helper privat untuk mengamankan User ID dari Guard Sanctum
     */
    private function getAuthenticatedUserId(): string
    {
        $user = auth('sanctum')->user();

        // Jika user tidak ditemukan lewat token sanctum, langsung kunci dengan 401
        if (!$user) {
            throw new HttpResponseException(
                response()->json([
                    'message' => 'Unauthenticated.'
                ], 401)
            );
        }

        return (string) $user->id;
    }

    /**
     * READ: Menampilkan daftar wishlist user
     */
    public function index(): JsonResponse
    {
        $userId = $this->getAuthenticatedUserId();

        return response()->json([
            'success' => true,
            'data' => $this->getWishlistUseCase->execute($userId)
        ], 200);
    }

    /**
     * CREATE: Menambahkan produk ke wishlist
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id']);

        try {
            $userId = $this->getAuthenticatedUserId();
            $dto = new WishlistInputDto($userId, (int) $request->product_id);

            $this->addItemUseCase->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke wishlist.'
            ], 201);
        } catch (DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * DELETE: Menghapus produk dari wishlist
     */
    public function destroy(int $productId): JsonResponse
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $this->removeItemUseCase->execute($userId, $productId);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus dari wishlist.'
            ], 200);
        } catch (DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
