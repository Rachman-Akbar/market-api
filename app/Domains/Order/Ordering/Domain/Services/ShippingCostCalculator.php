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

    public function calculate(string $courier, array $context, ?string $service = null): float
    {
        $courier = strtolower($courier);

        if ($courier === 'ambil_sendiri') {
            return 0.0;
        }

        if ($courier === 'express') {
            if (!$this->hasCompleteCoordinates($context)) {
                throw new InvalidArgumentException('Koordinat asal atau tujuan belum lengkap.');
            }

            return $this->expressCalculator->calculate($context);
        }

        if (!$this->hasCompleteDestinationIds($context)) {
            throw new InvalidArgumentException(
                'RajaOngkir belum tersedia karena destination ID asal atau tujuan belum ditemukan.'
            );
        }

        return $this->rajaOngkirCalculator->calculate($context + [
            'courier' => $courier,
            'service' => $service,
        ]);
    }

    public function options(array $context): array
    {
        $options = [];

        if ($this->hasCompleteDestinationIds($context)) {
            try {
                $options = $this->rajaOngkirCalculator->options($context);
            } catch (\Throwable $exception) {
                $context['shipping_warning'] = $exception->getMessage();
            }
        }

        if ($this->hasCompleteCoordinates($context)) {
            $cost = $this->expressCalculator->calculate($context);
            $distance = $this->expressCalculator->distanceInKilometers(
                (float) $context['origin_latitude'],
                (float) $context['origin_longitude'],
                (float) $context['latitude'],
                (float) $context['longitude']
            );
            $options[] = [
                'id' => 'express:internal',
                'courier' => 'express',
                'courier_label' => 'Express Internal',
                'service' => 'INTERNAL',
                'description' => 'Pengiriman berdasarkan jarak toko ke alamat penerima.',
                'etd' => number_format($distance, 1, ',', '.') . ' km',
                'cost' => $cost,
            ];
        }

        $options[] = [
            'id' => 'ambil_sendiri:pickup',
            'courier' => 'ambil_sendiri',
            'courier_label' => 'Ambil Sendiri',
            'service' => 'PICKUP',
            'description' => 'Ambil pesanan langsung di toko.',
            'etd' => '',
            'cost' => 0.0,
        ];

        if (!$options) {
            throw new InvalidArgumentException('Tidak ada metode pengiriman yang tersedia.');
        }

        return collect($options)->sortBy('cost')->values()->all();
    }

    private function hasCompleteDestinationIds(array $context): bool
    {
        return trim((string) ($context['origin_destination_id'] ?? '')) !== ''
            && trim((string) ($context['destination_id'] ?? '')) !== '';
    }

    private function hasCompleteCoordinates(array $context): bool
    {
        foreach ([
            'origin_latitude',
            'origin_longitude',
            'latitude',
            'longitude',
        ] as $key) {
            if (!array_key_exists($key, $context) || !is_numeric($context[$key])) {
                return false;
            }
        }

        return true;
    }
}
