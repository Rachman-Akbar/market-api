<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Controllers;

use App\Domains\Ordering\Infrastructure\Services\FinalizeCheckoutSessionService;
use App\Models\CheckoutSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;
use Throwable;

final class ManualTransferApprovalController extends Controller
{
    public function approve(
        Request $request,
        string $session,
        FinalizeCheckoutSessionService $finalizer,
    ): JsonResponse {
        $user = $request->user();

        $canApprove = (bool) ($user->is_admin ?? false)
            || (method_exists($user, 'can') && (
                $user->can('manage-orders') || $user->can('orders.manage')
            ));

        if (! $canApprove) {
            return response()->json([
                'message' => 'Tidak diizinkan memverifikasi transfer manual.',
            ], 403);
        }

        $checkoutSession = CheckoutSession::query()
            ->where(function ($query) use ($session): void {
                $query->where('session_number', $session);

                if (ctype_digit($session)) {
                    $query->orWhere('id', (int) $session);
                }
            })
            ->with('items')
            ->first();

        if (! $checkoutSession) {
            return response()->json([
                'message' => 'Checkout session tidak ditemukan.',
            ], 404);
        }

        if ($checkoutSession->payment_method !== 'manual_transfer') {
            return response()->json([
                'message' => 'Checkout session ini bukan manual transfer.',
            ], 422);
        }

        try {
            $checkoutSession->forceFill([
                'status' => 'paid',
                'payment_gateway' => null,
                'payment_method' => 'manual_transfer',
                'manual_verified_by' => (string) $user->getAuthIdentifier(),
                'manual_verified_at' => now(),
                'paid_at' => now(),
            ])->save();

            $order = $finalizer->finalizePaidSession(
                $checkoutSession,
                (string) $user->getAuthIdentifier(),
            );

            return response()->json([
                'message' => 'Manual transfer disetujui dan order berhasil dibuat.',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Gagal menyetujui manual transfer.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }
    }
}
