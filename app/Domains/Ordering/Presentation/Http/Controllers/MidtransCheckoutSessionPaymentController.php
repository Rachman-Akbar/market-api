<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Controllers;

use App\Domains\Ordering\Infrastructure\Services\MidtransCheckoutSessionSnapService;
use App\Models\CheckoutSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;
use Throwable;

final class MidtransCheckoutSessionPaymentController extends Controller
{
    public function create(
        Request $request,
        string $session,
        MidtransCheckoutSessionSnapService $snapService,
    ): JsonResponse {
        try {
            $checkoutSession = CheckoutSession::query()
                ->where('user_id', (string) $request->user()->getAuthIdentifier())
                ->where(function ($query) use ($session): void {
                    $query->where('session_number', $session);

                    if (ctype_digit($session)) {
                        $query->orWhere('id', (int) $session);
                    }
                })
                ->with(['items'])
                ->first();

            if (! $checkoutSession) {
                return response()->json([
                    'message' => 'Checkout session tidak ditemukan.',
                ], 404);
            }

            $payment = $snapService->createTransaction(
                session: $checkoutSession,
                user: $request->user(),
            );

            return response()->json([
                'message' => 'Payment Midtrans berhasil dibuat.',
                'data' => [
                    'order_id' => $payment['order_id'] ?? null,
                    'snap_token' => $payment['snap_token'] ?? null,
                    'redirect_url' => $payment['redirect_url'] ?? null,
                ],
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'debug' => config('app.debug') ? [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ] : null,
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Gagal membuat payment Midtrans.',
                'error' => config('app.debug') ? $exception->getMessage() : 'Internal Server Error',
                'file' => config('app.debug') ? $exception->getFile() : null,
                'line' => config('app.debug') ? $exception->getLine() : null,
            ], 500);
        }
    }
}
