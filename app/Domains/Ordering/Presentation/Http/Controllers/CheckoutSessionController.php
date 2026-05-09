<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Controllers;

use App\Domains\Ordering\Infrastructure\Services\CreateCheckoutSessionFromCartService;
use App\Models\CheckoutSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

final class CheckoutSessionController extends Controller
{
    public function store(
        Request $request,
        CreateCheckoutSessionFromCartService $service,
    ): JsonResponse {
        $this->decodeJsonArrayInput($request, 'shipping_address');
        $this->decodeJsonArrayInput($request, 'manual_transfer');

        $validator = Validator::make($request->all(), [
            'payment_method' => ['required', 'in:midtrans,manual_transfer'],

            'shipping_address' => ['required', 'array'],
            'shipping_address.recipient_name' => ['required', 'string'],
            'shipping_address.phone' => ['required', 'string'],
            'shipping_address.address_line' => ['required', 'string'],
            'shipping_address.province' => ['required', 'string'],
            'shipping_address.city' => ['required', 'string'],
            'shipping_address.district' => ['required', 'string'],
            'shipping_address.postal_code' => ['required', 'string'],

            'notes' => ['nullable', 'string'],

            'manual_transfer' => ['nullable', 'array'],
            'manual_transfer.bank_destination' => ['nullable', 'string'],
            'manual_transfer.sender_account_name' => ['nullable', 'string'],
            'manual_transfer.sender_account_number' => ['nullable', 'string'],
            'manual_transfer.transfer_date' => ['nullable', 'string'],
            'manual_transfer.admin_note' => ['nullable', 'string'],

            // Support nested FormData:
            // manual_transfer[transfer_proof]
            'manual_transfer.transfer_proof' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:4096',
            ],

            // Support top-level FormData:
            // transfer_proof
            'transfer_proof' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:4096',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data checkout session tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $transferProof = $request->file('manual_transfer.transfer_proof')
                ?? $request->file('transfer_proof');

            $session = $service->create(
                payload: $validator->validated(),
                user: $request->user(),
                transferProof: $transferProof,
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
                    'created_order_id' => $session->created_order_id,
                    'items' => $session->items,
                ],
            ], 201);
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
                'message' => 'Gagal membuat checkout session.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
                'file' => config('app.debug') ? $exception->getFile() : null,
                'line' => config('app.debug') ? $exception->getLine() : null,
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
            ->with(['items'])
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

    private function decodeJsonArrayInput(Request $request, string $key): void
    {
        $value = $request->input($key);

        if (! is_string($value)) {
            return;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $request->merge([
                $key => $decoded,
            ]);
        }
    }
}