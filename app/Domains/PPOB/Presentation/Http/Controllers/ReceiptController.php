<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Presentation\Http\Controllers;

use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;
use App\Domains\PPOB\Application\Services\ReceiptService;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use App\Domains\PPOB\Infrastructure\Persistence\Models\ReceiptModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function __construct(
        private ReceiptService $receipts,
    ) {}

    /**
     * List the current user's receipts (newest first).
     * Filter by type: 'digital' (PPOB), 'order' (marketplace), or all.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ReceiptModel::where('user_id', $request->user()->id);

        $type = $request->input('type');
        if ($type && in_array($type, ['digital', 'order'], true)) {
            $query->where('receipt_type', $type);
        }

        $receipts = $query->orderByDesc('created_at')
            ->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $receipts->items(),
            'meta' => [
                'current_page' => $receipts->currentPage(),
                'last_page' => $receipts->lastPage(),
                'per_page' => $receipts->perPage(),
                'total' => $receipts->total(),
            ],
        ]);
    }

    /**
     * Unified transaction history combining PPOB and marketplace transactions.
     * Returns a flat list sorted by date (newest first).
     */
    public function history(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $perPage = (int) $request->input('per_page', 20);
        $filterType = $request->input('type');

        $items = collect();

        // PPOB transactions
        if (! $filterType || $filterType === 'digital') {
            $ppobTransactions = PpoTransactionModel::where('user_id', $userId)
                ->with('operator')
                ->orderByDesc('created_at')
                ->limit(100)
                ->get()
                ->map(fn (PpoTransactionModel $tx) => [
                    'id' => $tx->id,
                    'type' => 'digital',
                    'reference_id' => $tx->reference_id,
                    'product_name' => $tx->product_name,
                    'category' => $tx->category,
                    'customer_id' => $tx->customer_id,
                    'total_amount' => (float) $tx->total_amount,
                    'status' => $tx->status,
                    'payment_status' => $tx->payment_status ?? 'pending',
                    'payment_method' => $tx->payment_method,
                    'created_at' => $tx->created_at?->toDateTimeString(),
                    'operator_name' => $tx->operator?->name,
                ]);

            $items = $items->concat($ppobTransactions);
        }

        // Marketplace orders
        if (! $filterType || $filterType === 'order') {
            $orders = OrderModel::where('user_id', $userId)
                ->orderByDesc('created_at')
                ->limit(100)
                ->get()
                ->map(function (OrderModel $order) {
                    $itemCount = $order->subOrders?->flatMap(fn ($sub) => $sub->items?->all() ?? [])->count() ?? 0;

                    return [
                        'id' => $order->id,
                        'type' => 'order',
                        'reference_id' => $order->order_number,
                        'product_name' => $itemCount === 1
                            ? ($order->subOrders?->first()?->items?->first()?->product_name ?? 'Pesanan')
                            : "{$itemCount} produk",
                        'category' => 'marketplace',
                        'customer_id' => null,
                        'total_amount' => (float) $order->total_amount,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status ?? 'unpaid',
                        'payment_method' => $order->payment_method,
                        'created_at' => $order->created_at?->toDateTimeString(),
                        'operator_name' => null,
                    ];
                });

            $items = $items->concat($orders);
        }

        // Sort by created_at descending
        $sorted = $items->sortByDesc('created_at')->values();

        // Paginate manually
        $page = (int) $request->input('page', 1);
        $paged = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'success' => true,
            'data' => $paged,
            'meta' => [
                'current_page' => $page,
                'last_page' => (int) ceil($sorted->count() / $perPage),
                'per_page' => $perPage,
                'total' => $sorted->count(),
            ],
        ]);
    }

    /**
     * Show one receipt, scoped to the current user. If a receipt does not yet
     * exist for a completed transaction, it is generated on demand; for
     * non-completed digital transactions the detail comes from the transaction.
     */
    public function show(Request $request, string $referenceOrId): JsonResponse
    {
        $user = $request->user();

        // Lookup by receipt number or transaction reference, scoped to user.
        $receipt = ReceiptModel::where('user_id', $user->id)
            ->where(function ($q) use ($referenceOrId): void {
                $q->where('receipt_number', $referenceOrId)
                    ->orWhere('transaction_reference', $referenceOrId);
            })
            ->first();

        // If not found, try to build detail from a matching PPOB transaction.
        if (! $receipt) {
            $tx = PpoTransactionModel::where('user_id', $user->id)
                ->where(function ($q) use ($referenceOrId): void {
                    $q->where('reference_id', $referenceOrId)
                        ->orWhere('id', $referenceOrId);
                })
                ->first();

            if ($tx) {
                // Digital detail is always shown from the transaction itself;
                // a receipt row is only generated for completed transactions.
                if (in_array($tx->status, ['success', 'processing'], true)) {
                    $receipt = $this->receipts->generateForTransaction($tx);
                }
                if (! $receipt) {
                    return response()->json([
                        'success' => true,
                        'data' => $this->transactionArray($tx),
                    ]);
                }
            }
        }

        // Try marketplace order
        if (! $receipt) {
            $order = OrderModel::where('user_id', $user->id)
                ->where(function ($q) use ($referenceOrId): void {
                    $q->where('order_number', $referenceOrId)
                        ->orWhere('id', $referenceOrId);
                })
                ->first();

            if ($order && in_array($order->payment_status, ['paid'], true)) {
                $receipt = $this->receipts->generateForOrder($order);
            }
        }

        if (! $receipt) {
            return response()->json([
                'success' => false,
                'message' => 'Bukti pembayaran tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->receiptArray($receipt),
        ]);
    }

    /**
     * Send the receipt email to the current user's Gmail. Digital transactions
     * send a bukti pembayaran (payment receipt), orders send an order receipt.
     * Generates the receipt on demand if it doesn't exist yet.
     */
    public function sendEmail(Request $request, string $referenceOrId): JsonResponse
    {
        $user = $request->user();

        if (empty($user->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak memiliki alamat email.',
            ], 422);
        }

        // Reuse the same lookup logic as show().
        $receipt = ReceiptModel::where('user_id', $user->id)
            ->where(function ($q) use ($referenceOrId): void {
                $q->where('receipt_number', $referenceOrId)
                    ->orWhere('transaction_reference', $referenceOrId);
            })
            ->first();

        if (! $receipt) {
            $tx = PpoTransactionModel::where('user_id', $user->id)
                ->where(function ($q) use ($referenceOrId): void {
                    $q->where('reference_id', $referenceOrId)
                        ->orWhere('id', $referenceOrId);
                })
                ->first();

            if ($tx && in_array($tx->status, ['success', 'processing'], true)) {
                $receipt = $this->receipts->generateForTransaction($tx);
            }
        }

        if (! $receipt) {
            $order = OrderModel::where('user_id', $user->id)
                ->where(function ($q) use ($referenceOrId): void {
                    $q->where('order_number', $referenceOrId)
                        ->orWhere('id', $referenceOrId);
                })
                ->first();

            if ($order && in_array($order->payment_status, ['paid'], true)) {
                $receipt = $this->receipts->generateForOrder($order);
            }
        }

        if (! $receipt) {
            return response()->json([
                'success' => false,
                'message' => 'Bukti pembayaran tidak ditemukan.',
            ], 404);
        }

        $sent = $this->receipts->send($receipt);

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran telah dikirim ke '.$user->email,
            'data' => [
                'email' => $user->email,
                'email_status' => $sent?->email_status ?? 'failed',
            ],
        ]);
    }

    private function receiptArray(ReceiptModel $receipt): array
    {
        return [
            'id' => $receipt->id,
            'receipt_number' => $receipt->receipt_number,
            'transaction_reference' => $receipt->transaction_reference,
            'receipt_type' => $receipt->receipt_type,
            'product_name' => $receipt->product_name,
            'category' => $receipt->category,
            'customer_id' => $receipt->customer_id,
            'customer_name' => $receipt->customer_name,
            'subtotal' => (float) $receipt->subtotal,
            'admin_fee' => (float) $receipt->admin_fee,
            'discount' => (float) $receipt->discount,
            'total' => (float) $receipt->total,
            'payment_method' => $receipt->payment_method,
            'payment_status' => $receipt->payment_status,
            'transaction_status' => $receipt->transaction_status,
            'paid_at' => $receipt->paid_at?->toDateTimeString(),
            'created_at' => $receipt->created_at?->toDateTimeString(),
        ];
    }

    private function transactionArray(PpoTransactionModel $tx): array
    {
        $data = [
            'id' => $tx->id,
            'receipt_number' => null,
            'transaction_reference' => $tx->reference_id,
            'receipt_type' => 'digital',
            'product_name' => $tx->product_name,
            'category' => $tx->category,
            'customer_id' => $tx->customer_id,
            'customer_name' => $tx->customer_name,
            'subtotal' => (float) ($tx->bill_amount ?? $tx->provider_price),
            'admin_fee' => (float) ($tx->admin_fee ?? 0),
            'discount' => 0.0,
            'total' => (float) $tx->total_amount,
            'payment_method' => $tx->payment_method,
            'payment_status' => $tx->payment_status ?? $tx->status,
            'transaction_status' => $tx->status,
            'paid_at' => ($tx->paid_at ?? $tx->completed_at)?->toDateTimeString(),
            'created_at' => $tx->created_at?->toDateTimeString(),
        ];

        return array_merge($data, [
            'raw' => [
                'sn' => $tx->sn,
                'tr_id' => $tx->tr_id,
                'provider_status' => $tx->provider_status,
                'provider_message' => $tx->provider_message,
            ],
        ]);
    }
}
