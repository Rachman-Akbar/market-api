<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Mail;

use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Bukti pembayaran digital (pulsa/data/token/tagihan) yang dikirim ke email
 * pembeli setelah transaksi berhasil. Berbeda dari invoice — menampilkan
 * rincian produk digital, nomor pelanggan, dan SN/TRID dari provider.
 */
final class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly PpoTransactionModel $transaction,
        public readonly string $buyerName,
    ) {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bukti Pembayaran Berhasil - '.($this->transaction->product_name ?? 'Transaksi Digital'),
        );
    }

    public function content(): Content
    {
        $tx = $this->transaction;

        $sn = $tx->sn;
        if (is_array($sn)) {
            $sn = implode(', ', array_filter($sn));
        }

        return new Content(
            view: 'emails.payment-receipt',
            with: [
                'buyerName' => $this->buyerName,
                'referenceId' => $tx->reference_id,
                'trId' => $tx->tr_id,
                'productName' => $tx->product_name,
                'category' => $tx->category,
                'productType' => $tx->product_type,
                'customerId' => $tx->customer_id,
                'customerName' => $tx->customer_name,
                'sn' => $sn ?: '-',
                'total' => number_format((float) $tx->total_amount, 0, ',', '.'),
                'adminFee' => number_format((float) ($tx->admin_fee ?: 0), 0, ',', '.'),
                'paymentMethod' => $tx->payment_method ?? 'midtrans',
                'paidAt' => ($tx->paid_at ?? now())->format('d/m/Y H:i'),
                'receiptUrl' => config('app.frontend_url')
                    ."/ppob/receipt/".urlencode((string) $tx->reference_id),
            ],
        );
    }
}
