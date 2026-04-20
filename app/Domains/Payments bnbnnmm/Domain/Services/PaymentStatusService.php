<?php

namespace App\Domains\Payments\Domain\Services;

final class PaymentStatusService
{
    public function isTerminal(string $status): bool
    {
        return in_array($status, ['completed', 'failed'], true);
    }
}
