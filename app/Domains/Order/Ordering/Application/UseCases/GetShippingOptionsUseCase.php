<?php

declare(strict_types=1);

namespace App\Domains\Order\Ordering\Application\UseCases;

use App\Domains\Order\Addresses\Application\UseCases\ResolveAddressDestinationUseCase;
use App\Domains\Order\Addresses\Domain\Entities\Address;
use App\Domains\Order\Addresses\Domain\Repositories\AddressRepositoryInterface;
use App\Domains\Order\Cart\Application\Readers\ProductForCartReaderInterface;
use App\Domains\Order\Cart\Infrastructure\Persistence\Models\CartModel;
use App\Domains\Order\Ordering\Domain\Services\ShippingCostCalculator;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final class GetShippingOptionsUseCase
{
    public function __construct(
        private AddressRepositoryInterface $addressRepository,
        private ProductForCartReaderInterface $productReader,
        private ShippingCostCalculator $shippingCalculator,
        private ResolveAddressDestinationUseCase $resolveDestinationUseCase
    ) {}

    public function execute(string $userId, int $addressId, array $cartItemIds): array
    {
        $contexts = $this->buildStoreContexts($userId, $addressId, $cartItemIds);
        $aggregated = [];
        $warnings = [];

        foreach ($contexts as $storeId => $context) {
            try {
                $storeOptions = $this->shippingCalculator->options($context);
            } catch (Throwable $exception) {
                $warnings[] = "Toko {$context['store_name']}: {$exception->getMessage()}";
                continue;
            }

            foreach ($storeOptions as $option) {
                $id = (string) $option['id'];
                if (!isset($aggregated[$id])) {
                    $aggregated[$id] = $option + [
                        'cost' => 0.0,
                        'store_breakdown' => [],
                        'stores_available' => 0,
                    ];
                }

                $aggregated[$id]['cost'] += (float) $option['cost'];
                $aggregated[$id]['stores_available']++;
                $aggregated[$id]['store_breakdown'][] = [
                    'store_id' => (int) $storeId,
                    'store_name' => $context['store_name'],
                    'weight' => $context['weight'],
                    'cost' => (float) $option['cost'],
                ];
            }
        }

        $storeCount = count($contexts);
        $options = collect($aggregated)
            ->filter(fn (array $option): bool => (int) $option['stores_available'] === $storeCount)
            ->map(function (array $option): array {
                unset($option['stores_available']);
                $option['price'] = $option['cost'];
                return $option;
            })
            ->sortBy('cost')
            ->values()
            ->all();

        if (!$options) {
            throw new RuntimeException($warnings[0] ?? 'Tidak ada layanan pengiriman yang tersedia untuk semua toko.');
        }

        return [
            'options' => $options,
            'warnings' => $warnings,
        ];
    }

    public function buildStoreContexts(string $userId, int $addressId, array $cartItemIds): array
    {
        $address = $this->addressRepository->findByIdAndOwner($addressId, $userId, null);
        if (!$address) {
            throw new RuntimeException('Alamat pengiriman tidak ditemukan.');
        }

        $this->refreshDestinationId($address);

        $cart = CartModel::where('user_id', $userId)->first();
        if (!$cart) {
            throw new RuntimeException('Keranjang belanja tidak ditemukan.');
        }

        $ids = collect($cartItemIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $selectedItems = $cart->items()->whereIn('id', $ids)->get();
        if ($selectedItems->count() !== $ids->count()) {
            throw new RuntimeException('Sebagian item checkout tidak ditemukan di keranjang Anda.');
        }

        $contexts = [];
        foreach ($selectedItems as $item) {
            $details = $this->productReader->getVariantDetails((int) $item->product_variant_id);
            if (!$details) {
                throw new RuntimeException('Data varian produk tidak ditemukan.');
            }
            if ($details->getStock() < (int) $item->quantity) {
                throw new RuntimeException("Stok {$details->getProductName()} tidak mencukupi.");
            }

            $storeId = $details->getStoreId();
            if (!isset($contexts[$storeId])) {
                $contexts[$storeId] = $this->storeContext($storeId, $details->getStoreName(), $address);
            }

            $contexts[$storeId]['weight'] += $details->getWeight() * (int) $item->quantity;
            $contexts[$storeId]['item_value'] += $details->getPrice()->getAmount() * (int) $item->quantity;
        }

        return $contexts;
    }

    private function storeContext(int $storeId, string $storeName, Address $destination): array
    {
        $originAddress = Address::query()
            ->where('store_id', (string) $storeId)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->first();

        if ($originAddress) {
            $this->refreshDestinationId($originAddress);
        }

        $setting = DB::table('shipping_settings')->where('store_id', $storeId)->first();
        $originLatitude = $setting->store_latitude ?? $originAddress?->latitude;
        $originLongitude = $setting->store_longitude ?? $originAddress?->longitude;

        return [
            'store_id' => $storeId,
            'store_name' => $storeName,
            'origin_destination_id' => (string) ($originAddress?->komerce_destination_id ?? ''),
            'destination_id' => (string) ($destination->komerce_destination_id ?? ''),
            'origin_latitude' => $originLatitude !== null ? (float) $originLatitude : null,
            'origin_longitude' => $originLongitude !== null ? (float) $originLongitude : null,
            'latitude' => (float) $destination->latitude,
            'longitude' => (float) $destination->longitude,
            'free_shipping_max_distance' => (float) ($setting->free_shipping_max_distance ?? 0),
            'default_flat_rate' => (float) ($setting->default_flat_rate ?? 0),
            'rate_per_km' => (float) ($setting->rate_per_km ?? config('services.shipping.express_rate_per_km', 0)),
            'weight' => 0,
            'item_value' => 0,
            'allowed_couriers' => (array) config('services.shipping.allowed_couriers', ['jne', 'jnt', 'sicepat', 'tiki', 'pos']),
        ];
    }

    private function refreshDestinationId(Address $address): void
    {
        try {
            $resolved = $this->resolveDestinationUseCase->execute([
                'country' => $address->country,
                'province' => $address->province,
                'city_or_regency' => $address->city_or_regency,
                'district' => $address->district,
                'subdistrict' => $address->subdistrict,
                'postal_code' => $address->postal_code,
            ]);

            if ((string) $address->komerce_destination_id !== $resolved->id) {
                $address->forceFill([
                    'komerce_destination_id' => $resolved->id,
                ])->save();
            }
        } catch (Throwable) {
        }
    }
}
