<?php

declare(strict_types=1);

namespace App\Domains\Cart\Presentation\Http\Controllers;

use App\Domains\Cart\Application\DTOs\AddCartItemData;
use App\Domains\Cart\Application\DTOs\CartSummaryData;
use App\Domains\Cart\Application\DTOs\UpdateCartItemData;
use App\Domains\Cart\Application\UseCases\AddItemToCartUseCase;
use App\Domains\Cart\Application\UseCases\ClearCartUseCase;
use App\Domains\Cart\Application\UseCases\GetCartUseCase;
use App\Domains\Cart\Application\UseCases\RemoveItemFromCartUseCase;
use App\Domains\Cart\Application\UseCases\UpdateCartItemQuantityUseCase;
use App\Domains\Cart\Presentation\Http\Requests\AddCartItemRequest;
use App\Domains\Cart\Presentation\Http\Requests\UpdateCartItemRequest;
use App\Domains\Cart\Presentation\Http\Resources\CartResource;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;

final class CartController extends Controller
{
    public function __construct(
        private readonly GetCartUseCase $getCart,
        private readonly AddItemToCartUseCase $addItem,
        private readonly UpdateCartItemQuantityUseCase $updateItem,
        private readonly RemoveItemFromCartUseCase $removeItem,
        private readonly ClearCartUseCase $clearCart,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        return $this->respond(fn (): CartSummaryData => $this->getCart->execute($this->userId($request)));
    }

    public function store(AddCartItemRequest $request): JsonResponse
    {
        return $this->respond(fn (): CartSummaryData => $this->addItem->execute(new AddCartItemData(
            userId: $this->userId($request),
            productId: (int) $request->validated('product_id'),
            quantity: (int) $request->validated('quantity'),
        )));
    }

    public function update(UpdateCartItemRequest $request, int|string $productId): JsonResponse
    {
        return $this->respond(fn (): CartSummaryData => $this->updateItem->execute(new UpdateCartItemData(
            userId: $this->userId($request),
            productId: (int) $productId,
            quantity: (int) $request->validated('quantity'),
        )));
    }

    public function destroy(Request $request, int|string $productId): JsonResponse
    {
        return $this->respond(fn (): CartSummaryData => $this->removeItem->execute(
            userId: $this->userId($request),
            productId: (int) $productId,
        ));
    }

    public function clear(Request $request): JsonResponse
    {
        return $this->respond(fn (): CartSummaryData => $this->clearCart->execute($this->userId($request)));
    }

    private function respond(callable $callback): JsonResponse
    {
        try {
            /** @var CartSummaryData $summary */
            $summary = $callback();

            return CartResource::make($summary->toArray())->response();
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    private function userId(Request $request): string
    {
        return (string) $request->user()->getAuthIdentifier();
    }
}
