<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Application\UseCases;

use App\Domains\PPOB\Domain\Entities\PpoTransactionStatus;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use RuntimeException;

/**
 * Handles Midtrans payment notifications for PPOB (digital) purchases. The
 * transaction is looked up by its reference_id (used as the Midtrans order_id).
 * On a successful settlement the buyer is marked paid and the provider top-up
 * is submitted & finalized.
 */
final class HandlePpobMidtransWebhookUseCase
{
    public function __construct(
        private FinalizePpobTopUpUseCase $finalize,
    ) {}

    public function execute(array $payload): void
    {
        foreach (['order_id', 'status_code', 'gross_amount', 'transaction_status', 'signature_key'] as $field) {
            if (! isset($payload[$field]) || $payload[$field] === '') {
                throw new RuntimeException("Payload Midtrans tidak memiliki field {$field}.");
            }
        }

        $referenceId = (string) $payload['order_id'];
        $statusCode = (string) $payload['status_code'];
        $grossAmount = (string) $payload['gross_amount'];
        $transactionStatus = strtolower((string) $payload['transaction_status']);
        $fraudStatus = strtolower((string) ($payload['fraud_status'] ?? 'accept'));
        $paymentType = (string) ($payload['payment_type'] ?? 'midtrans');
        $incomingSignature = (string) $payload['signature_key'];
        $transactionId = isset($payload['transaction_id']) ? (string) $payload['transaction_id'] : null;
        $serverKey = (string) config('midtrans.server_key');

        if ($serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        $localSignature = hash('sha512', $referenceId . $statusCode . $grossAmount . $serverKey);
        if (! hash_equals($localSignature, $incomingSignature)) {
            throw new RuntimeException('Signature Midtrans tidak valid.');
        }

        $tx = PpoTransactionModel::where('reference_id', $referenceId)->first();
        if (! $tx) {
            throw new RuntimeException("Transaksi PPOB {$referenceId} tidak ditemukan.");
        }

        if (abs((float) $grossAmount - (float) $tx->total_amount) > 1) {
            throw new RuntimeException('Nominal notifikasi Midtrans tidak sesuai dengan total transaksi.');
        }

        $paid = ($transactionStatus === 'settlement')
            || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

        if ($paid) {
            $tx->payment_method = $paymentType;
            $tx->payment_status = 'paid';
            $tx->midtrans_transaction_id = $transactionId;
            $tx->paid_at = now();
            $tx->save();

            // Submit & finalize the provider top-up.
            $this->finalize->executeFor($tx);

            return;
        }

        if (in_array($transactionStatus, ['cancel', 'deny', 'expire'], true)) {
            $tx->payment_status = $transactionStatus === 'expire' ? 'expired' : 'failed';
            $tx->payment_method = $paymentType;
            $tx->midtrans_transaction_id = $transactionId;
            $tx->save();

            return;
        }

        if (in_array($transactionStatus, ['refund', 'partial_refund'], true)) {
            $tx->payment_status = 'refunded';
            $tx->midtrans_transaction_id = $transactionId;
            $tx->save();

            return;
        }

        // Default: still unpaid/pending.
        $tx->payment_status = 'pending';
        $tx->midtrans_transaction_id = $transactionId;
        $tx->payment_method = $paymentType;
        $tx->save();
    }
}
