<?php

declare(strict_types=1);

namespace App\Domains\Order\Payment\Presentation\Http\Controllers;

use App\Domains\Order\Payment\Application\UseCases\HandleMidtransWebhookUseCase;
use App\Domains\PPOB\Application\UseCases\HandlePpobMidtransWebhookUseCase;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class MidtransWebhookController extends Controller
{
    public function __construct(
        private HandleMidtransWebhookUseCase $webhookUseCase,
        private HandlePpobMidtransWebhookUseCase $ppobWebhookUseCase,
    ) {}

    public function handleNotification(Request $request): JsonResponse
    {
        try {
<<<<<<< HEAD
            $this->webhookUseCase->execute($request->all());
=======
            $orderId = (string) $request->input('order_id', '');

            if ($orderId !== '' && PpoTransactionModel::where('reference_id', $orderId)->exists()) {
                $this->ppobWebhookUseCase->execute($request->all());
            } else {
                $this->webhookUseCase->execute($request->all());
            }
>>>>>>> 766322f401066e067c940f8610801fb39e4fda37

            return response()->json(['success' => true, 'message' => 'Notifikasi Midtrans berhasil diproses.']);
        } catch (Throwable $exception) {
            Log::warning('Midtrans webhook rejected', [
                'message' => $exception->getMessage(),
                'order_id' => $request->input('order_id'),
                'transaction_status' => $request->input('transaction_status'),
            ]);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
