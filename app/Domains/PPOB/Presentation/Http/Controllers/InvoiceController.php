<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Presentation\Http\Controllers;

use App\Domains\PPOB\Application\Services\InvoiceService;
use App\Domains\PPOB\Infrastructure\Persistence\Models\InvoiceModel;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoices,
    ) {}

    /**
     * List the current user's PPOB invoices (newest first).
     */
    public function index(Request $request): JsonResponse
    {
        $invoices = InvoiceModel::where('user_id', $request->user()->id)
            ->where('invoice_type', 'digital')
            ->orderByDesc('created_at')
            ->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    /**
     * Show one invoice, scoped to the current user. If an invoice does not yet
     * exist for a successful transaction, it is generated on demand.
     */
    public function show(Request $request, string $referenceOrId): JsonResponse
    {
        $user = $request->user();

        // Lookup by invoice number or transaction reference.
        $invoice = InvoiceModel::where('invoice_number', $referenceOrId)
            ->orWhere('transaction_reference', $referenceOrId)
            ->first();

        if ($invoice && $invoice->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak ditemukan.',
            ], 404);
        }

        // If not found, try to generate from a matching successful transaction.
        if (! $invoice) {
            $tx = PpoTransactionModel::where('user_id', $user->id)
                ->where(function ($q) use ($referenceOrId): void {
                    $q->where('reference_id', $referenceOrId)
                        ->orWhere('id', $referenceOrId);
                })
                ->first();

            if ($tx && in_array($tx->status, ['success', 'processing'], true)) {
                $invoice = $this->invoices->generateForTransaction($tx);
            }
        }

        if (! $invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->invoiceArray($invoice),
        ]);
    }

    private function invoiceArray(InvoiceModel $invoice): array
    {
        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'transaction_reference' => $invoice->transaction_reference,
            'invoice_type' => $invoice->invoice_type,
            'product_name' => $invoice->product_name,
            'category' => $invoice->category,
            'customer_id' => $invoice->customer_id,
            'customer_name' => $invoice->customer_name,
            'subtotal' => (float) $invoice->subtotal,
            'admin_fee' => (float) $invoice->admin_fee,
            'discount' => (float) $invoice->discount,
            'total' => (float) $invoice->total,
            'payment_method' => $invoice->payment_method,
            'payment_status' => $invoice->payment_status,
            'transaction_status' => $invoice->transaction_status,
            'paid_at' => $invoice->paid_at?->toDateTimeString(),
            'created_at' => $invoice->created_at?->toDateTimeString(),
        ];
    }
}
