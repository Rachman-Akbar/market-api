<?php

namespace App\Domains\Order\Payment\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Domains\Order\Payment\Application\UseCases\HandleMidtransWebhookUseCase;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private HandleMidtransWebhookUseCase $webhookUseCase
    ) {}

    public function handleNotification(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();

            // Eksekusi Logika Bisnis & Validasi Keamanan
            $this->webhookUseCase->execute($payload);

            // Midtrans hanya butuh HTTP status 200 OK sebagai tanda notifikasi berhasil diterima
            return response()->json([
                'status' => 'success',
                'message' => 'Midtrans notification handled and verified successfully.'
            ], 200);

        } catch (\Exception $e) {
            // Tulis error ke log laravel agar Anda bisa menelusuri jika ada kegagalan manipulasi signature
            Log::error('Midtrans Webhook Error: ' . $e->getMessage(), [
                'payload' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
