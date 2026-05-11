<?php

declare(strict_types=1);

use App\Domains\Ordering\Presentation\Http\Controllers\CheckoutController;
use App\Domains\Ordering\Presentation\Http\Controllers\CheckoutSessionController;
use App\Domains\Ordering\Presentation\Http\Controllers\ManualTransferApprovalController;
use App\Domains\Ordering\Presentation\Http\Controllers\MidtransCheckoutSessionPaymentController;
use App\Domains\Ordering\Presentation\Http\Controllers\MidtransNotificationController;
use App\Domains\Ordering\Presentation\Http\Controllers\OrderController;
use App\Domains\Ordering\Presentation\Http\Controllers\OrderPaymentStatusController;
use App\Http\Middleware\EnsureApiTokenIsValid;
use Illuminate\Support\Facades\Route;

Route::post('/midtrans/notifications', MidtransNotificationController::class)
    ->name('midtrans.notifications');

Route::middleware(['auth:sanctum', EnsureApiTokenIsValid::class])->group(function (): void {
    /*
    |--------------------------------------------------------------------------
    | COD Checkout
    |--------------------------------------------------------------------------
    | COD boleh langsung membuat order final.
    */
    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->name('checkout.store');

    /*
    |--------------------------------------------------------------------------
    | Checkout Sessions
    |--------------------------------------------------------------------------
    | Midtrans dan manual transfer tidak langsung membuat order final.
    */
    Route::prefix('checkout-sessions')
        ->name('checkout-sessions.')
        ->group(function (): void {
            Route::post('/', [CheckoutSessionController::class, 'store'])
                ->name('store');

            Route::get('/{session}', [CheckoutSessionController::class, 'show'])
                ->name('show');

            Route::post('/{session}/pay/midtrans', [MidtransCheckoutSessionPaymentController::class, 'create'])
                ->name('pay.midtrans');

            Route::post('/{session}/cancel', [CheckoutSessionController::class, 'cancel'])
                ->name('cancel');

            Route::post('/{session}/approve-manual-transfer', [ManualTransferApprovalController::class, 'approve'])
                ->name('approve-manual-transfer');
        });

    Route::prefix('orders')
        ->name('orders.')
        ->group(function (): void {
            Route::get('/', [OrderController::class, 'index'])
                ->name('index');

            Route::get('/{order}', [OrderController::class, 'show'])
                ->name('show');

            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])
                ->name('cancel');

            Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])
                ->name('update-status');

                Route::get('/payment', [OrderPaymentStatusController::class, 'show'])
    ->name('payment.show');
        });
});
