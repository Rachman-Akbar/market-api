<?php

declare(strict_types=1);

use App\Http\Controllers\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('payments/midtrans')->group(function () {
    // URL: POST api/v1/payments/midtrans/notification
    Route::post('notification', [MidtransWebhookController::class, 'handleNotification'])
        ->name('payments.midtrans.notification');
});
