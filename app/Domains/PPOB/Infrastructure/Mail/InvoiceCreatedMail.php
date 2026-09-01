<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Mail;

use App\Domains\PPOB\Infrastructure\Persistence\Models\InvoiceModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class InvoiceCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly InvoiceModel $invoice,
        public readonly string $buyerName,
    ) {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice {$this->invoice->invoice_number} Transaksi Berhasil",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-created',
            with: [
                'buyerName' => $this->buyerName,
                'invoiceNumber' => $this->invoice->invoice_number,
                'productName' => $this->invoice->product_name,
                'customerId' => $this->invoice->customer_id,
                'total' => number_format((float) $this->invoice->total, 0, ',', '.'),
                'paymentMethod' => $this->invoice->payment_method ?? '-',
                'invoiceUrl' => config('app.frontend_url')
                    ."/ppob/invoice/".urlencode((string) $this->invoice->transaction_reference),
            ],
        );
    }
}