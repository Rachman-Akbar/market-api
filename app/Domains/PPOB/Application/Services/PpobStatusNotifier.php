<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Application\Services;

use App\Domains\Communication\Chat\Application\Services\StatusNotificationChatService;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;

/**
 * Mengirim notifikasi ke chat user saat transaksi digital (pulsa, data,
 * token listrik, tagihan) telah selesai. Email bukti pembayaran ditangani
 * oleh ReceiptService (idempotent), jadi layanan ini hanya menambah chat.
 */
final class PpobStatusNotifier
{
    public function __construct(private readonly StatusNotificationChatService $chat) {}

    public function notifySuccess(PpoTransactionModel $transaction): bool
    {
        return $this->chat->send(
            buyerId: (string) $transaction->user_id,
            title: 'Transaksi Selesai',
            message: $this->message($transaction),
            attachments: [
                'kind' => 'ppob_status',
                'notification_key' => "ppob:{$transaction->id}:success",
                'transaction_reference' => (string) $transaction->reference_id,
                'product_name' => (string) $transaction->product_name,
                'category' => (string) $transaction->category,
                'customer_id' => (string) ($transaction->customer_id ?? ''),
                'total_amount' => (float) $transaction->total_amount,
                'status' => 'success',
                'subject' => 'Transaksi '.($transaction->product_name ?? 'Digital').' Selesai',
            ],
        ) !== null;
    }

    private function message(PpoTransactionModel $transaction): string
    {
        $productName = (string) ($transaction->product_name ?? 'Produk digital');
        $referenceId = (string) $transaction->reference_id;
        $amount = number_format((float) $transaction->total_amount, 0, ',', '.');

        return "Transaksi {$productName} (ref: {$referenceId}) sebesar Rp {$amount} sudah SELESAI. "
            .'Cek bukti pembayaran di halaman PPOB ya! ✨';
    }
}