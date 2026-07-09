<?php

namespace App\Domains\Order\Ordering\Domain\Services;

use App\Domains\Order\Ordering\Infrastructure\Services\RajaOngkirShippingCalculator;
use InvalidArgumentException;

class ShippingCostCalculator
{
    public function __construct(
        private ExpressShippingCalculator $expressCalculator,
        private RajaOngkirShippingCalculator $rajaOngkirCalculator
    ) {}

    public function calculate(string $courier, array $context): float
    {
        if ($courier === 'ambil_sendiri') {
            return 0.00;
        }

        if ($courier === 'express') {
            return $this->expressCalculator->calculate($context);
        }

        // Jika kurir adalah jne, pos, tiki, dll (RajaOngkir)
        if (in_array($courier, ['jne', 'pos', 'tiki'])) {
            return $this->rajaOngkirCalculator->calculate($context);
        }

        throw new InvalidArgumentException("Kurir tidak didukung.");
    }
}
