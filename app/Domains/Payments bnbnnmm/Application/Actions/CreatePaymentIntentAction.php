<?php

namespace App\Domains\Payments\Application\Actions;

use App\Models\Payment;

final class CreatePaymentIntentAction
{
    public function execute(int $orderId, ?string $paymentMethod = null): Payment
    {
        return Payment::query()->create([
            'order_id' => $orderId,
            'status' => 'pending',
            'payment_method' => $paymentMethod,
        ]);
    }
}
