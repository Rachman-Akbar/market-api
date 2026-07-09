<?php

namespace App\Domains\Order\Payment\Infrastructure\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class MidtransService
{
    private string $serverKey;
    private string $baseUrl;
    private ?array $enabledPayments;

    public function __construct()
    {
        // Membaca langsung dari config/midtrans.php milikmu
        $this->serverKey = (string) config('midtrans.server_key');
        $this->enabledPayments = config('midtrans.enabled_payments');

        $this->baseUrl = config('midtrans.is_production')
            ? 'https://app.midtrans.com/snap/v1/'
            : 'https://app.sandbox.midtrans.com/snap/v1/';
    }

    public function createSnapToken(array $params): string
    {
        $payload = [
            'transaction_details' => [
                'order_id' => $params['order_id'],
                'gross_amount' => (int) $params['gross_amount'],
            ],
            'customer_details' => [
                'user_id' => $params['user_id']
            ],
            // Mengatur fitur 3DS keamanan kartu kredit dari config
            'credit_card' => [
                'secure' => (bool) config('midtrans.is_3ds', true)
            ]
        ];

        // Jika enabled_payments di .env diisi, masukkan ke payload Snap
        if (!is_null($this->enabledPayments)) {
            $payload['enabled_payments'] = $this->enabledPayments;
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
        ->withBasicAuth($this->serverKey, '')
        ->post($this->baseUrl . 'transactions', $payload);

        if ($response->failed()) {
            $errorMsg = $response->json()['error_messages'][0] ?? 'Gagal membuat transaksi ke Midtrans';
            throw new Exception("Midtrans Error: " . $errorMsg);
        }

        return $response->json()['token'];
    }
}
