<?php

namespace App\Domains\Order\Payment\Infrastructure\Persistence\Mappers;

use App\Domains\Order\Payment\Domain\Entities\Payment;
use App\Domains\Order\Payment\Infrastructure\Persistence\Models\PaymentModel;

final class PaymentMapper
{
    public function toDomain(PaymentModel $model): Payment
    {
        return new Payment(
            id: $model->id,
            orderNumber: $model->order_number,
            transactionId: $model->transaction_id,
            paymentMethod: $model->payment_method,
            amount: $model->amount,
            status: $model->status,
            payload: $model->payload
        );
    }

    public function toPersistenceArray(Payment $entity): array
    {
        return [
            'order_number' => $entity->orderNumber,
            'transaction_id' => $entity->transactionId,
            'payment_method' => $entity->paymentMethod,
            'amount' => $entity->amount,
            'status' => $entity->status,
            'payload' => $entity->payload ? json_encode($entity->payload) : null,
        ];
    }
}
