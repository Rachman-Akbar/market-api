<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Application\Services;

use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;
use App\Domains\PPOB\Infrastructure\Mail\PaymentReceiptMail;
use App\Domains\PPOB\Infrastructure\Mail\ReceiptMail;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use App\Domains\PPOB\Infrastructure\Persistence\Models\ReceiptModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Generates and manages receipt (bukti pembayaran) records for completed
 * transactions (both PPOB digital and marketplace orders). Idempotent:
 * repeated generation for the same source returns the existing receipt
 * instead of creating duplicates.
 */
class ReceiptService
{
    // ── PPOB (digital) receipts ───────────────────────────────────────────

    /**
     * Generate (or return the existing) receipt for a PPOB transaction.
     */
    public function generateForTransaction(PpoTransactionModel $tx): ReceiptModel
    {
        $existing = ReceiptModel::where('source_type', 'ppob_transaction')
            ->where('source_id', (string) $tx->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $receiptNumber = $this->nextReceiptNumber();

        return ReceiptModel::create([
            'receipt_number' => $receiptNumber,
            'user_id' => $tx->user_id,
            'source_type' => 'ppob_transaction',
            'source_id' => (string) $tx->id,
            'transaction_reference' => $tx->reference_id,
            'receipt_type' => 'digital',
            'product_name' => $tx->product_name,
            'category' => $tx->category,
            'customer_id' => $tx->customer_id,
            'customer_name' => $tx->customer_name,
            'subtotal' => (float) ($tx->revenue ?: $tx->total_amount),
            'admin_fee' => (float) ($tx->admin_fee ?: 0),
            'discount' => 0,
            'total' => (float) $tx->total_amount,
            'payment_method' => $tx->payment_method,
            'payment_status' => $tx->payment_status ?? 'pending',
            'transaction_status' => $tx->status,
            'paid_at' => $tx->paid_at,
            'email_status' => 'none',
        ]);
    }

    /**
     * Send the receipt email once for a PPOB transaction.
     */
    public function sendForTransaction(PpoTransactionModel $tx): ?ReceiptModel
    {
        $receipt = ReceiptModel::where('source_type', 'ppob_transaction')
            ->where('source_id', (string) $tx->id)
            ->first();

        if (! $receipt) {
            return null;
        }

        return $this->send($receipt);
    }

    // ── Marketplace order receipts ────────────────────────────────────────

    /**
     * Generate (or return the existing) receipt for a marketplace order.
     * Builds the product list from order items with quantity and price.
     */
    public function generateForOrder(OrderModel $order): ReceiptModel
    {
        $existing = ReceiptModel::where('source_type', 'order')
            ->where('source_id', (string) $order->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $items = $order->subOrders?->flatMap(fn ($sub) => $sub->items?->all() ?? [])->all() ?? [];
        $itemCount = count($items);
        $productName = $itemCount === 1
            ? ($items[0]->product_name ?? 'Produk')
            : "{$itemCount} produk";

        $subtotal = (float) $order->total_amount;
        $discount = (float) ($order->discount_amount ?? 0) + (float) ($order->shipping_discount_amount ?? 0);

        $receiptNumber = $this->nextReceiptNumber();

        return ReceiptModel::create([
            'receipt_number' => $receiptNumber,
            'user_id' => $order->user_id,
            'source_type' => 'order',
            'source_id' => (string) $order->id,
            'transaction_reference' => $order->order_number,
            'receipt_type' => 'order',
            'product_name' => $productName,
            'category' => 'marketplace',
            'customer_id' => $order->user_id,
            'customer_name' => null,
            'subtotal' => $subtotal,
            'admin_fee' => (float) ($order->admin_fee ?? 0),
            'discount' => $discount,
            'total' => $subtotal - $discount,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status ?? 'unpaid',
            'transaction_status' => $order->status ?? 'pending',
            'paid_at' => $order->payment_status === 'paid' ? $order->updated_at : null,
            'email_status' => 'none',
        ]);
    }

    /**
     * Send the receipt email for a marketplace order.
     */
    public function sendForOrder(OrderModel $order): ?ReceiptModel
    {
        $receipt = ReceiptModel::where('source_type', 'order')
            ->where('source_id', (string) $order->id)
            ->first();

        if (! $receipt) {
            return null;
        }

        return $this->send($receipt);
    }

    // ── Shared sending logic ──────────────────────────────────────────────

    /**
     * Send the receipt email at most once (idempotent) and record the outcome
     * so retries and webhook replays never double-send.
     */
    public function send(ReceiptModel $receipt): ?ReceiptModel
    {
        $user = $receipt->user;
        if (! $user || empty($user->email) || $receipt->email_sent_at) {
            return $receipt;
        }

        // Atomically claim the send so concurrent webhook hits can't double-queue.
        $claimed = $this->claimSend($receipt->id);
        if (! $claimed) {
            return $receipt;
        }

        // Track the dispatched message id (database queue stores no message id;
        // we record a synthetic one for idempotency/tracing).
        $messageId = (string) Str::uuid();

        try {
            // Digital (PPOB) transactions get a payment receipt email with the
            // actual product/SN details; marketplace orders get an order receipt.
            if ($receipt->source_type === 'ppob_transaction') {
                $tx = PpoTransactionModel::find($receipt->source_id);
                if ($tx) {
                    Mail::to($user->email)->send(new PaymentReceiptMail($tx, $user->name));
                }
            } else {
                Mail::to($user->email)->send(new ReceiptMail($receipt, $user->name));
            }
            $receipt->email_sent_at = now();
            $receipt->email_status = 'sent';
            $receipt->email_message_id = $messageId;
            $receipt->save();
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email bukti pembayaran', [
                'receipt_id' => $receipt->id,
                'message' => $e->getMessage(),
            ]);
            $receipt->email_status = 'failed';
            $receipt->save();
        }

        return $receipt;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Atomically flip the email_status from 'none' so only one process queues
     * the email. Returns true when the caller won the claim.
     */
    private function claimSend(int $receiptId): bool
    {
        return (bool) ReceiptModel::where('id', $receiptId)
            ->where('email_status', 'none')
            ->update(['email_status' => 'queued']);
    }

    /**
     * Build a unique, human-friendly receipt number: RCT-YYYYMMDD-XXXXXX.
     */
    private function nextReceiptNumber(): string
    {
        do {
            $number = 'RCT-'.date('Ymd').'-'.strtoupper(Str::random(6));
        } while (ReceiptModel::where('receipt_number', $number)->exists());

        return $number;
    }
}
