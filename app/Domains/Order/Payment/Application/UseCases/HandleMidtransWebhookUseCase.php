<?php

namespace App\Domains\Order\Payment\Application\UseCases;

use App\Domains\Order\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Payment\Domain\Repositories\PaymentRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class HandleMidtransWebhookUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    public function execute(array $payload): void
    {
        $orderNumber = $payload['order_id'];
        $statusCode = $payload['status_code'];
        $grossAmount = $payload['gross_amount'];
        $transactionStatus = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? 'accept';
        $paymentType = $payload['payment_type'] ?? 'midtrans';
        $incomingSignature = $payload['signature_key'] ?? '';

        // 1. VERIFIKASI KEAMANAN: Cek Validitas Signature Key Midtrans
        $serverKey = (string) config('midtrans.server_key');
        $localSignature = hash("sha512", $orderNumber . $statusCode . $grossAmount . $serverKey);

        if ($incomingSignature !== $localSignature) {
            throw new Exception("Peringatan Keamanan: Signature Key tidak valid! Akses ditolak.");
        }

        // 2. Ambil data Order dari repositori
        $order = $this->orderRepository->findByOrderNumber($orderNumber);
        if (!$order) {
            throw new Exception("Order dengan nomor {$orderNumber} tidak ditemukan.");
        }

        // 3. Mapping status dari Midtrans ke State Sistem Kita
        $paymentStatus = 'unpaid';
        $orderStatus = 'pending';

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'challenge') {
                $paymentStatus = 'challenged';
            } elseif ($fraudStatus === 'accept') {
                $paymentStatus = 'paid';
                $orderStatus = 'processing'; // Siap dikemas/diproses kurir
            }
        } elseif ($transactionStatus === 'settlement') {
            $paymentStatus = 'paid';
            $orderStatus = 'processing';
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $paymentStatus = 'failed';
            $orderStatus = 'cancelled';
        } elseif ($transactionStatus === 'pending') {
            $paymentStatus = 'unpaid';
        }

        // 4. Eksekusi Perubahan State dalam Database Transaction
        DB::transaction(function () use ($order, $paymentStatus, $orderStatus, $paymentType, $payload) {

            // Mutasi State Order & Simpan
            $order->paymentStatus = $paymentStatus;
            $order->status = $orderStatus;
            $order->paymentMethod = $paymentType;
            $this->orderRepository->update($order);

            // Ambil entity/log payment yang sempat dibuat saat checkout awal
            $payment = $this->paymentRepository->findByOrderNumber($order->orderNumber);
            if ($payment) {
                if ($paymentStatus === 'paid') {
                    $payment->markAsSuccess($payload['transaction_id'] ?? null, $payload);
                } elseif ($paymentStatus === 'failed') {
                    $payment->markAsFailed($payload);
                } else {
                    $payment->payload = $payload; // update log berkala
                }

                // Simpan log payment terbaru
                $this->paymentRepository->save($payment);
            }

        });
    }
}
