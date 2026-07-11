<?php

namespace App\Domains\Order\Ordering\Infrastructure\Services;

use App\Domains\Order\Ordering\Domain\Services\ShippingCalculatorInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RajaOngkirShippingCalculator implements ShippingCalculatorInterface
{
    public function calculate(array $data): float
    {
        $options = $this->options($data);
        $courier = strtolower((string) ($data['courier'] ?? ''));
        $service = strtoupper((string) ($data['service'] ?? ''));

        $matched = collect($options)->first(function (array $option) use ($courier, $service): bool {
            if ($courier !== '' && strtolower($option['courier']) !== $courier) {
                return false;
            }
            return $service === '' || strtoupper($option['service']) === $service;
        });

        if (!$matched) {
            throw new RuntimeException('Layanan ongkir yang dipilih tidak tersedia untuk rute ini.');
        }

        return (float) $matched['cost'];
    }

    public function options(array $data): array
    {
        $origin = trim((string) ($data['origin_destination_id'] ?? ''));
        $destination = trim((string) ($data['destination_id'] ?? ''));
        $weight = max(1, (int) ($data['weight'] ?? 1000));

        if ($origin === '' || $destination === '') {
            throw new RuntimeException('Destination ID asal dan tujuan wajib tersedia untuk menghitung ongkir.');
        }

        $baseUrl = rtrim((string) config('services.shipping.base_url'), '/');
        $apiKey = trim((string) config('services.shipping.key'));

        if ($baseUrl === '' || $apiKey === '') {
            throw new RuntimeException('Konfigurasi API ongkir belum tersedia.');
        }

        $response = $this->client($apiKey)->asForm()->post($baseUrl . '/calculate/domestic-cost', [
            'shipper_destination_id' => $origin,
            'receiver_destination_id' => $destination,
            'weight' => $weight,
            'item_value' => max(0, (int) ($data['item_value'] ?? 0)),
            'cod' => 'no',
        ]);

        if ($response->failed()) {
            throw new RuntimeException((string) ($response->json('message') ?: 'API ongkir gagal merespons.'));
        }

        $payload = $response->json();
        $groups = data_get($payload, 'data', $payload);
        $rows = [];

        foreach (['calculate_reguler', 'calculate_regular', 'calculate_cargo', 'calculate_instant', 'results', 'costs'] as $key) {
            $value = data_get($groups, $key);
            if (is_array($value)) {
                $rows = array_merge($rows, $value);
            }
        }

        if (!$rows && is_array($groups) && array_is_list($groups)) {
            $rows = $groups;
        }

        $allowedCouriers = array_map('strtolower', (array) ($data['allowed_couriers'] ?? []));
        $options = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $courier = strtolower((string) ($row['shipping_name'] ?? $row['courier_code'] ?? $row['code'] ?? $row['courier'] ?? ''));
            $service = strtoupper((string) ($row['service_name'] ?? $row['service'] ?? $row['type'] ?? 'REG'));
            $cost = (float) ($row['shipping_cost'] ?? $row['cost'] ?? $row['price'] ?? data_get($row, 'cost.0.value', 0));

            if ($courier === '' || $cost < 0) {
                continue;
            }
            if ($allowedCouriers && !in_array($courier, $allowedCouriers, true)) {
                continue;
            }

            $etd = (string) ($row['etd'] ?? $row['estimated'] ?? $row['shipping_duration'] ?? '');
            $options[] = [
                'id' => $courier . ':' . strtolower($service),
                'courier' => $courier,
                'courier_label' => strtoupper($courier),
                'service' => $service,
                'description' => (string) ($row['description'] ?? $row['service_description'] ?? ''),
                'etd' => $etd,
                'cost' => $cost,
            ];
        }

        if (!$options) {
            throw new RuntimeException('API ongkir tidak mengembalikan layanan yang dapat digunakan.');
        }

        return collect($options)->unique('id')->sortBy('cost')->values()->all();
    }

    private function client(string $apiKey): PendingRequest
    {
        return Http::acceptJson()
            ->timeout((int) config('services.shipping.timeout', 15))
            ->retry(2, 250)
            ->withHeaders([
                'key' => $apiKey,
                'x-api-key' => $apiKey,
            ]);
    }
}
