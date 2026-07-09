<?php

namespace App\Domains\Order\Ordering\Domain\Services;

use Illuminate\Support\Facades\DB;

class ExpressShippingCalculator implements ShippingCalculatorInterface
{
    public function calculate(array $data): float
    {
        // Pindahkan rumus Haversine (jarak koordinat) kamu ke sini
        // Menggunakan $data['latitude'] dan $data['longitude']
        return 25000.00;
    }
}
