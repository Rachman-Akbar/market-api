<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Application\Services;

use App\Domains\PPOB\Infrastructure\Mail\InvoiceCreatedMail;
use App\Domains\PPOB\Infrastructure\Persistence\Models\InvoiceModel;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Generates and manages default invoices from successful digital (PPOB)
 * transactions. Idempotent: repeated generation for the same transaction
 * returns the existing invoice instead of creating duplicates.
 */
class InvoiceService
{
    /**
     * Generate (or return the existing) invoice for a PPOB transaction.
     */
    public function generateForTransaction(PpoTransactionModel $tx): InvoiceModel
    {
        $existing = InvoiceModel::where('source_type', 'ppob_transaction')
            ->where('source_id', (string) $tx->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $invoiceNumber = $this->nextInvoiceNumber();

        return InvoiceModel::create([
            'invoice_number' => $invoiceNumber,
            'user_id' => $tx->user_id,
            'source_type' => 'ppob_transaction',
            'source_id' => (string) $tx->id,
            'transaction_reference' => $tx->reference_id,
            'invoice_type' => 'digital',
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
     * Queue the invoice email exactly once for a transaction.
     * Idempotent: skips if already sent or currently being sent.
     * Returns the invoice, or null when no invoice exists to send.
     */
    public function sendForTransaction(PpoTransactionModel $tx): ?InvoiceModel
    {
        $invoice = InvoiceModel::where('source_type', 'ppob_transaction')
            ->where('source_id', (string) $tx->id)
            ->first();

        if (! $invoice) {
            return null;
        }

        return $this->send($invoice);
    }

    /**
     * Queue the invoice email at most once (idempotent) and record the outcome
     * so retries and webhook replays never double-send.
     */
    public function send(InvoiceModel $invoice): ?InvoiceModel
    {
        $user = $invoice->user;
        if (! $user || empty($user->email) || $invoice->email_sent_at) {
            return $invoice;
        }

        // Atomically claim the send so concurrent webhook hits can't double-queue.
        $claimed = $this->claimSend($invoice->id);
        if (! $claimed) {
            return $invoice;
        }

        // Track the dispatched message id (database queue stores no message id;
        // we record a synthetic one for idempotency/tracing).
        $messageId = (string) Str::uuid();

        try {
            Mail::to($user->email)->queue(new InvoiceCreatedMail($invoice, $user->name));
            $invoice->email_sent_at = now();
            $invoice->email_status = 'sent';
            $invoice->email_message_id = $messageId;
            $invoice->save();
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email invoice', [
                'invoice_id' => $invoice->id,
                'message' => $e->getMessage(),
            ]);
            $invoice->email_status = 'failed';
            $invoice->save();
        }

        return $invoice;
    }

    /**
     * Atomically flip the email_status from 'none' so only one process queues
     * the email. Returns true when the caller won the claim.
     */
    private function claimSend(int $invoiceId): bool
    {
        return (bool) InvoiceModel::where('id', $invoiceId)
            ->where('email_status', 'none')
            ->update(['email_status' => 'queued']);
    }

    /**
     * Build a unique, human-friendly invoice number: INV-YYYYMMDD-XXXXXX.
     */
    private function nextInvoiceNumber(): string
    {
        do {
            $number = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(6));
        } while (InvoiceModel::where('invoice_number', $number)->exists());

        return $number;
    }
}
