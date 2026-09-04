<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Application\Services;

use App\Domains\PPOB\Domain\Repositories\PpoTransactionLogRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoTransactionRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use App\Domains\PPOB\Infrastructure\Providers\IakProviderClient;
use Illuminate\Support\Facades\DB;

/**
 * Applies an inbound IAK callback to the matching transaction. Only a terminal
 * success/failed state is applied; a still-processing callback is ignored so we
 * never mark a paid order back to pending. Finance is posted exactly once.
 */
class PpoCallbackHandler
{
    public function __construct(
        private PpoTransactionRepositoryInterface $transactions,
        private PpoTransactionLogRepositoryInterface $logs,
        private IakProviderClient $client,
        private PpoFinanceService $finance,
        private ReceiptService $receipts,
    ) {}

    public function handle(array $data, ?string $ip = null): bool
    {
        $referenceId = $data['ref_id'] ?? null;

        if (! $referenceId) {
            return false;
        }

        // Verify callback authenticity using the documented sign.
        $providedSign = $data['sign'] ?? null;
        if ($providedSign !== null && ! $this->client->verifyCallbackSignature($referenceId, (string) $providedSign)) {
            $this->log($referenceId, $data, $ip, 'callback', 'invalid signature', null);

            return false;
        }

        $tx = PpoTransactionModel::where('reference_id', $referenceId)->first();

        if (! $tx) {
            $this->log($referenceId, $data, $ip, 'callback', 'transaction not found', null);

            return false;
        }

        $this->log($referenceId, $data, $ip, 'callback', 'received', $tx->id);

        $status = (string) ($data['status'] ?? '');

        return DB::transaction(function () use ($tx, $status, $data, $referenceId) {
            // Terminal states only; ignore in-flight updates.
            if ($status === '1') {
                $this->applySuccess($tx, $data, $referenceId);
            } elseif ($status === '2') {
                $this->applyFailure($tx, $data);
            } else {
                return false;
            }

            return true;
        });
    }

    private function applySuccess(PpoTransactionModel $tx, array $data, string $referenceId): void
    {
        $tx->status = 'success';
        $tx->provider_status = (string) ($data['status'] ?? '');
        $tx->provider_message = $data['message'] ?? null;
        $tx->tr_id = $data['tr_id'] ?? $tx->tr_id;
        $sn = $data['sn'] ?? null;
        $tx->sn = is_array($sn) ? (implode(',', $sn) ?: null) : $sn;
        $tx->pin = $data['pin'] ?? $data['activation_code'] ?? $tx->pin;
        $tx->callback_signature = $data['sign'] ?? null;
        $tx->provider_raw_response = $data;
        $tx->completed_at = now();
        $tx->save();

        // Post finance once (idempotent).
        $this->finance->postForSuccess($tx);

        // Generate a receipt (bukti pembayaran) for successful digital top-ups.
        $this->receipts->generateForTransaction($tx->fresh());

        // Send the receipt email (idempotent, one send per receipt).
        $this->receipts->sendForTransaction($tx->fresh());
    }

    private function applyFailure(PpoTransactionModel $tx, array $data): void
    {
        $tx->status = 'failed';
        $tx->provider_status = (string) ($data['status'] ?? '');
        $tx->provider_message = $data['message'] ?? null;
        $tx->provider_raw_response = $data;
        $tx->save();
    }

    private function log(string $referenceId, array $data, ?string $ip, string $action, string $messagePrefix, ?int $txId): void
    {
        $this->logs->create([
            'ppob_transaction_id' => $txId,
            'reference_id' => $referenceId,
            'action' => $action,
            'direction' => 'incoming',
            'request_payload' => $this->redact($data),
            'response_payload' => null,
            'http_status' => 200,
            'provider_status' => (string) ($data['status'] ?? ''),
            'provider_message' => $messagePrefix,
            'ip_address' => $ip,
        ]);
    }

    private function redact(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if ($key === 'sign') {
                $payload[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $payload[$key] = $this->redact($value);
            }
        }

        return $payload;
    }
}
