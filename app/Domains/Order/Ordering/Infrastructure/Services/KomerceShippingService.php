<?php

namespace App\Domains\Order\Ordering\Infrastructure\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class KomerceShippingService
{
    public function searchDestination(string $cityName)
    {
        // Mengambil config dari services.php
        $apiKey = config('services.shipping.key');
        $baseUrl = config('services.shipping.base_url');

        // Menembak endpoint Direct Search sesuai dokumentasi
        $response = Http::withHeaders([
            'key' => $apiKey
        ])->get($baseUrl . 'destination/domestic-destination', [
            'search' => $cityName,
            'limit' => 5,
            'offset' => 0
        ]);

        if ($response->failed()) {
            throw new Exception("Gagal mengambil data destinasi dari Komerce.");
        }

        return $response->json();
    }
}
