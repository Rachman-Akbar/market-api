<?php

namespace App\Domains\Payments\Application\Actions;

use App\Events\PaymentCompleted;
use App\Models\Payment;

final class MarkPaymentStatusAction
{
    public function execute(int $orderId, string $status): Payment
    {
        $payment = Payment::query()->where('order_id', $orderId)->firstOrFail();
        $payment->status = $status;
        $payment->save();

        if ($status === 'completed') {
            event(new PaymentCompleted($orderId));
        }

        return $payment->refresh();
    }
}
