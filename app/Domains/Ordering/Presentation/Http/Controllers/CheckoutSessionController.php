<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Controllers;

use App\Domains\Ordering\Infrastructure\Services\CreateCheckoutSessionFromCartService;
use App\Models\CheckoutSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;
use Throwable;

final class CheckoutSessionController extends Controller
{
    public function store(
        Request $request,
        CreateCheckoutSessionFromCartService $service,
    ): JsonResponse {
        $payload = $request->validate([
            'payment_method' => ['required', 'in:midtrans,manual_transfer'],
            'shipping_address' => ['required', 'array'],
            'shipping_address.recipient_name' => ['required', 'string'],
            'shipping_address.phone' => ['required', 'string'],
            'shipping_address.address_line' => ['required', 'string'],
            'shipping_address.province' => ['nullable', 'string'],
            'shipping_address.city' => ['nullable', 'string'],
            'shipping_address.district' => ['nullable', 'string'],
            'shipping_address.postal_code' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],

            'manual_transfer' => ['nullable', 'array'],
            'manual_transfer.bank_destination' => ['nullable', 'string'],
            'manual_transfer.sender_account_name' => ['nullable', 'string'],
            'manual_transfer.sender_account_number' => ['nullable', 'string'],
            'manual_transfer.transfer_date' => ['nullable', 'string'],
            'manual_transfer.admin_note' => ['nullable', 'string'],
            'manual_transfer.transfer_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        try {
            $session = $service->create(
                payload: $payload,
                user: $request->user(),
                transferProof: $request->file('manual_transfer.transfer_proof'),
            );

            return response()->json([
                'message' => 'Checkout session berhasil dibuat.',
                'data' => [
                    'id' => $session->id,
                    'session_number' => $session->session_number,
                    'status' => $session->status,
                    'payment_method' => $session->payment_method,
                    'payment_gateway' => $session->payment_gateway,
                    'grand_total' => $session->grand_total,
                ],
            ], 201);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Gagal membuat checkout session.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }
    }

    public function show(Request $request, string $session): JsonResponse
    {
        $checkoutSession = CheckoutSession::query()
            ->where('user_id', (string) $request->user()->getAuthIdentifier())
            ->where(function ($query) use ($session): void {
                $query->where('session_number', $session);

                if (ctype_digit($session)) {
                    $query->orWhere('id', (int) $session);
                }
            })
            ->with(['items', 'latestPaymentAttempt'])
            ->first();

        if (! $checkoutSession) {
            return response()->json([
                'message' => 'Checkout session tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'data' => $checkoutSession,
        ]);
    }

    public function cancel(Request $request, string $session): JsonResponse
    {
        $checkoutSession = CheckoutSession::query()
            ->where('user_id', (string) $request->user()->getAuthIdentifier())
            ->where(function ($query) use ($session): void {
                $query->where('session_number', $session);

                if (ctype_digit($session)) {
                    $query->orWhere('id', (int) $session);
                }
            })
            ->first();

        if (! $checkoutSession) {
            return response()->json([
                'message' => 'Checkout session tidak ditemukan.',
            ], 404);
        }

        if ($checkoutSession->created_order_id) {
            return response()->json([
                'message' => 'Checkout session sudah menjadi order dan tidak bisa dibatalkan.',
            ], 422);
        }

        $checkoutSession->forceFill([
            'status' => 'cancelled',
        ])->save();

        return response()->json([
            'message' => 'Checkout session dibatalkan. Cart tetap aman.',
        ]);
    }
}
