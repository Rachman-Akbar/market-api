<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Mail;

use App\Domains\PPOB\Infrastructure\Persistence\Models\ReceiptModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Order receipt (kuitansi) sent for completed marketplace orders.
 * Digital products use PaymentReceiptMail instead.
 */
final class ReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ReceiptModel $receipt,
        public readonly string $buyerName,
    ) {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Bukti Pembayaran {$this->receipt->receipt_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.receipt-created',
            with: [
                'buyerName' => $this->buyerName,
                'receiptNumber' => $this->receipt->receipt_number,
                'productName' => $this->receipt->product_name,
                'customerId' => $this->receipt->customer_id,
                'total' => number_format((float) $this->receipt->total, 0, ',', '.'),
                'paymentMethod' => $this->receipt->payment_method ?? '-',
                'receiptUrl' => config('app.frontend_url')
                    ."/ppob/receipt/".urlencode((string) $this->receipt->transaction_reference),
            ],
        );
    }
}
