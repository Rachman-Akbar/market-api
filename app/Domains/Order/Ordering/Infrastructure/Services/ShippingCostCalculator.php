<?php

namespace App\Domains\Order\Ordering\Domain\Services;

use Illuminate\Support\Facades\DB;

class ShippingCostCalculator
{
    /**
     * Menghitung ongkir berdasarkan koordinat customer menggunakan rumus Haversine
     */
    public function calculate(float $customerLat, float $customerLng): float
    {
        // 1. Ambil konfigurasi aktif dari tabel database baru
        $settings = DB::table('shipping_settings')->first();

        $storeLat = (float) ($settings->store_latitude ?? -6.1944491);
        $storeLng = (float) ($settings->store_longitude ?? 106.7644912);
        $maxFreeDistance = (float) ($settings->free_shipping_max_distance ?? 5.0);
        $defaultRate = (float) ($settings->default_flat_rate ?? 15000.00);

        // 2. Hitung Jarak Terlurus dengan Rumus Haversine (Satuan KM)
        $earthRadius = 6371; // Radius bumi dalam KM

        $dLat = deg2rad($customerLat - $storeLat);
        $dLng = deg2rad($customerLng - $storeLng);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($storeLat)) * cos(deg2rad($customerLat)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c; // Hasil jarak dalam KM

        // 3. Evaluasi Aturan Bisnis
        if ($distance <= $maxFreeDistance) {
            return 0.00; // Gratis Ongkir jika masuk dalam radius (misal <= 5km)
        }

        return $defaultRate; // Tarif normal jika di luar radius
    }
}
