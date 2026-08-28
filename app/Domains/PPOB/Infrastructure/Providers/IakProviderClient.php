<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Providers;

use Illuminate\Support\Facades\Http;

/**
 * Gateway to the IAK / Mobile Pulsa provider.
 *
 * Executes only the documented IAK endpoints with the correct signature:
 *   sign = md5(username + api_key + dependent_parameters)
 * See SDGS/docs for the verified contract (api.iak.id).
 */
class IakProviderClient
{
    public function __construct(
        private string $baseUrl,
        private string $username,
        private string $apiKey,
    ) {}

    /**
     * POST {base}/top-up — prepaid top-up (pulsa/data/token/voucher).
     * Reusing the same ref_id makes this an idempotent status check on IAK side.
     *
     * @return array{response: array, http_status: int}
     */
    public function topUp(string $referenceId, string $customerId, string $productCode): array
    {
        $sign = md5($this->username . $this->apiKey . $referenceId);

        return $this->post('/top-up', [
            'username' => $this->username,
            'ref_id' => $referenceId,
            'customer_id' => $customerId,
            'product_code' => $productCode,
            'sign' => $sign,
        ]);
    }

    /**
     * POST {base}/check-status — fetch the current status of a previous top-up.
     */
    public function checkStatus(string $referenceId): array
    {
        $sign = md5($this->username . $this->apiKey . $referenceId);

        return $this->post('/check-status', [
            'username' => $this->username,
            'ref_id' => $referenceId,
            'sign' => $sign,
        ]);
    }

    /**
     * POST {base}/balance/check — query provider account balance.
     */
    public function checkBalance(): array
    {
        $sign = md5($this->username . $this->apiKey);

        return $this->post('/balance/check', [
            'username' => $this->username,
            'sign' => $sign,
        ]);
    }

    /**
     * POST {base}/v1/bill/check (inquiry-pasca) — postpaid bill inquiry
     * (electricity bill, internet, etc). Returns tr_id for payment.
     */
    public function billInquiry(
        string $referenceId,
        string $productCode,
        string $customerNumber
    ): array {
        $sign = md5($this->username . $this->apiKey . $referenceId);

        return $this->post('/v1/bill/check', [
            'commands' => 'inquiry-pasca',
            'username' => $this->username,
            'ref_id' => $referenceId,
            'hp' => $customerNumber,
            'code' => $productCode,
            'sign' => $sign,
        ]);
    }

    /**
     * POST {base}/v1/bill/check (pay-pasca) — complete a postpaid bill payment
     * using the tr_id from a successful inquiry.
     */
    public function billPayment(string $trId): array
    {
        $sign = md5($this->username . $this->apiKey . $trId);

        return $this->post('/v1/bill/check', [
            'commands' => 'pay-pasca',
            'username' => $this->username,
            'tr_id' => $trId,
            'sign' => $sign,
        ]);
    }

    /**
     * Verify the callback signature from IAK:
     * sign = md5(username + api_key + ref_id)
     */
    public function verifyCallbackSignature(string $referenceId, string $providedSign): bool
    {
        $expected = md5($this->username . $this->apiKey . $referenceId);

        return hash_equals($expected, $providedSign ?? '');
    }

    private function post(string $path, array $payload): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');

        $response = Http::timeout(config('ppob.provider.timeout', 60))
            ->asForm()
            ->post($url, $payload);

        return [
            'response' => $response->json() ?? [],
            'http_status' => $response->status(),
        ];
    }
}
