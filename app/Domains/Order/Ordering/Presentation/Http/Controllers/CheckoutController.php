<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Controllers;

use App\Domains\Ordering\Application\DTOs\CreateOrderData;
use App\Domains\Ordering\Application\UseCases\Order\CreateOrderFromCartUseCase;
use App\Domains\Ordering\Presentation\Http\Requests\CheckoutRequest;
use App\Domains\Ordering\Presentation\Http\Resources\OrderResource;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class CheckoutController extends Controller
{
    public function store(
        CheckoutRequest $request,
        CreateOrderFromCartUseCase $useCase,
    ): JsonResponse {
        $payload = $request->validated();

        if (($payload['payment_method'] ?? null) !== 'cod') {
            return response()->json([
                'message' => 'Gunakan checkout session untuk Midtrans atau Manual Transfer.',
            ], 422);
        }

        try {
            $order = $useCase->execute(
                CreateOrderData::fromArray(
                    $payload,
                    (string) $request->user()->getAuthIdentifier(),
                ),
            );

            return response()->json([
                'message' => 'Checkout COD berhasil.',
                'data' => new OrderResource($order),
            ], 201);
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}


