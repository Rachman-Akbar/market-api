<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Application\Services;

use App\Domains\PPOB\Domain\Repositories\PpoTransactionLogRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Providers\IakProviderClient;
use Illuminate\Http\Request;

/**
 * Orchestrates IAK calls, maps provider status codes to the application
 * transaction lifecycle, and persists an audit log of every request/response.
 */
class IakProviderService
{
    public function __construct(
        private IakProviderClient $client,
        private PpoTransactionLogRepositoryInterface $logs,
    ) {}

    /**
     * Submit a prepaid top-up. Returns mapped result.
     *
     * @return array{success: bool, status: string, provider_status: ?string, message: ?string, tr_id: ?string, sn: ?string, response: array}
     */
    public function submitTopUp(
        string $referenceId,
        string $customerId,
        string $productCode,
        ?int $transactionId = null
    ): array {
        $result = $this->client->topUp($referenceId, $customerId, $productCode);

        $this->log($transactionId, $referenceId, 'top_up', 'outgoing', [
            'customer_id' => $customerId,
            'product_code' => $productCode,
        ], $result['response'], $result['http_status']);

        return $this->mapPrepaidResult($result['response'], 'top_up');
    }

    /**
     * Check the current status of a prepaid transaction.
     */
    public function checkStatus(string $referenceId, ?int $transactionId = null): array
    {
        $result = $this->client->checkStatus($referenceId);

        $this->log($transactionId, $referenceId, 'check_status', 'outgoing', [], $result['response'], $result['http_status']);

        return $this->mapPrepaidResult($result['response'], 'check_status');
    }

    /**
     * Query provider balance.
     */
    public function checkBalance(): array
    {
        $result = $this->client->checkBalance();

        $this->log(null, 'balance', 'check_balance', 'outgoing', [], $result['response'], $result['http_status']);

        $data = $result['response']['data'] ?? $result['response'] ?? [];

        return [
            'success' => $this->isSuccess($result['response']),
            'balance' => $data['balance'] ?? null,
            'message' => $data['message'] ?? null,
        ];
    }

    /**
     * Inquiry a postpaid bill. Returns tr_id to be used for payment.
     */
    public function inquiryBill(
        string $referenceId,
        string $productCode,
        string $customerNumber,
        ?int $transactionId = null,
        ?string $prefixTransactionId = null
    ): array {
        $result = $this->client->billInquiry($referenceId, $productCode, $customerNumber);

        $this->log($transactionId, $referenceId, 'inquiry', 'outgoing', [
            'product_code' => $productCode,
            'customer_number' => $customerNumber,
        ], $result['response'], $result['http_status']);

        $data = $result['response']['data'] ?? $result['response'] ?? [];

        return [
            'success' => $this->isSuccess($result['response']),
            'rc' => $data['response_code'] ?? $data['rc'] ?? null,
            'message' => $data['message'] ?? $data['response_message'] ?? null,
            'tr_id' => $data['tr_id'] ?? null,
            'customer_name' => $data['customer_name'] ?? $data['hp'] ?? null,
            'customer_no' => $data['customer_name'] ?? $data['customer_no'] ?? null,
            'bill_amount' => isset($data['price']) ? (float) $data['price'] : null,
            'admin_charge' => isset($data['admin_charge']) ? (float) $data['admin_charge'] : null,
            'admin_charge_message' => $data['admin_charge_message'] ?? null,
            'detail' => $data['detail'] ?? null,
            'response' => $result['response'],
        ];
    }

    /**
     * Complete a postpaid bill payment using a tr_id.
     */
    public function payBill(
        string $trId,
        string $referenceId,
        ?int $transactionId = null
    ): array {
        $result = $this->client->billPayment($trId);

        $this->log($transactionId, $referenceId, 'payment', 'outgoing', ['tr_id' => $trId], $result['response'], $result['http_status']);

        return $this->mapPostpaidResult($result['response']);
    }

    /**
     * Handle an incoming IAK callback. Returns true if it was accepted and applied.
     */
    public function handleCallback(Request $request): bool
    {
        $payload = $request->all();
        $data = $payload['data'] ?? $payload;

        $referenceId = $data['ref_id'] ?? null;

        if (! $referenceId) {
            return false;
        }

        app()->make(PpoCallbackHandler::class)->handle($data, $request->ip());

        return true;
    }

    private function mapPrepaidResult(array $response, string $action): array
    {
        $data = $response['data'] ?? $response ?? [];

        $statusText = $this->mapPrepaidStatus($data, $action);

        $sn = $data['sn'] ?? $data['serial_number'] ?? null;

        return [
            'success' => in_array($statusText, ['success', 'processing', 'pending'], true),
            'status' => $statusText,
            'provider_status' => (string) ($data['status'] ?? ''),
            'message' => $data['message'] ?? $data['desc'] ?? null,
            'tr_id' => $data['tr_id'] ?? null,
            'sn' => is_array($sn) ? (implode(',', $sn) ?: null) : $sn,
            'pin' => $data['pin'] ?? $data['activation_code'] ?? null,
            'response' => $response,
        ];
    }

    private function mapPrepaidStatus(array $data, string $action): string
    {
        $status = (string) ($data['status'] ?? '');
        $rc = (string) ($data['rc'] ?? '');

        if ($status === '1' || $rc === '00') {
            return 'success';
        }

        if ($status === '2' || $rc === '02') {
            return 'failed';
        }

        // status 0 / rc 01 => still processing
        return $action === 'top_up' ? 'processing' : 'pending';
    }

    private function mapPostpaidResult(array $response): array
    {
        $data = $response['data'] ?? $response ?? [];

        $rc = (string) ($data['response_code'] ?? $data['rc'] ?? '');

        if ($rc === '00') {
            return [
                'success' => true,
                'status' => 'success',
                'provider_status' => $rc,
                'message' => $data['message'] ?? $data['response_message'] ?? null,
                'tr_id' => $data['tr_id'] ?? null,
                'sn' => $data['sn'] ?? null,
                'response' => $response,
            ];
        }

        if ($rc === '01') {
            return [
                'success' => true,
                'status' => 'processing',
                'provider_status' => $rc,
                'message' => $data['message'] ?? $data['response_message'] ?? null,
                'tr_id' => $data['tr_id'] ?? null,
                'sn' => $data['sn'] ?? null,
                'response' => $response,
            ];
        }

        return [
            'success' => false,
            'status' => 'failed',
            'provider_status' => $rc,
            'message' => $data['message'] ?? $data['response_message'] ?? null,
            'tr_id' => $data['tr_id'] ?? null,
            'sn' => $data['sn'] ?? null,
            'response' => $response,
        ];
    }

    private function isSuccess(array $response): bool
    {
        $data = $response['data'] ?? $response ?? [];
        $rc = (string) ($data['response_code'] ?? $data['rc'] ?? $data['rc'] ?? '');

        return $rc === '00' || $rc === '01';
    }

    private function log(
        ?int $transactionId,
        string $referenceId,
        string $action,
        string $direction,
        array $request,
        array $response,
        ?int $httpStatus,
        ?string $providerStatus = null,
        ?string $providerMessage = null,
        ?string $ip = null
    ): void {
        $providerMessage ??= $response['data']['message'] ?? $response['message'] ?? null;

        $this->logs->create([
            'ppob_transaction_id' => $transactionId,
            'reference_id' => $referenceId,
            'action' => $action,
            'direction' => $direction,
            'request_payload' => $this->redact($request),
            'response_payload' => $this->redact($response),
            'http_status' => $httpStatus,
            'provider_status' => $providerStatus,
            'provider_message' => is_string($providerMessage) ? $providerMessage : null,
            'ip_address' => $ip,
        ]);
    }

    private function redact(array $payload): array
    {
        // Never persist the sign or api key in logs.
        foreach ($payload as $key => $value) {
            if ($key === 'sign' || strtolower((string) $key) === 'apikey') {
                $payload[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $payload[$key] = $this->redact($value);
            }
        }

        return $payload;
    }
}
