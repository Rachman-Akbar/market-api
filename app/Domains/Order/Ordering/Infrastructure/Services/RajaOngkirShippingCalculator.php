<?php

namespace App\Domains\Order\Ordering\Infrastructure\Services;

use App\Domains\Order\Ordering\Domain\Services\ShippingCalculatorInterface;

class RajaOngkirShippingCalculator implements ShippingCalculatorInterface
{
    public function calculate(array $data): float
    {
        // Logika menembak API RajaOngkir menggunakan $data['destination_id'], $data['weight'], dll.
        // Contoh return dummy:
        return 15000.00;
    }
}
