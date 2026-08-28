<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Application\Services;

use App\Domains\PPOB\Domain\Entities\PpoProduct;
use App\Domains\PPOB\Domain\Repositories\PpoOperatorRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoProductRepositoryInterface;

class PpoCatalogService
{
    public function __construct(
        private PpoProductRepositoryInterface $products,
        private PpoOperatorRepositoryInterface $operators,
        private PricingEngine $pricing,
    ) {}

    public function categories(): array
    {
        return [
            ['key' => 'pulsa', 'label' => 'Pulsa'],
            ['key' => 'data', 'label' => 'Paket Data'],
            ['key' => 'token-listrik', 'label' => 'Token Listrik'],
            ['key' => 'tagihan', 'label' => 'Tagihan Listrik'],
            ['key' => 'internet', 'label' => 'Internet / WiFi'],
            ['key' => 'voucher', 'label' => 'Voucher'],
        ];
    }

    public function operators(?string $category = null): array
    {
        return $this->operators->getActiveByCategory($category);
    }

    public function products(string $category, ?int $operatorId = null): array
    {
        $entities = $this->products->getAvailableByCategory($category, $operatorId);

        return array_map(fn (PpoProduct $p) => $this->pricedToArray($p), $entities);
    }

    public function productById(int $id): ?array
    {
        $entity = $this->products->findById($id);

        if (! $entity) {
            return null;
        }

        return $this->pricedToArray($entity);
    }

    public function productEntityById(int $id): ?PpoProduct
    {
        return $this->products->findById($id);
    }

    public function createProduct(array $data): array
    {
        $this->applyDerivedPricing($data);

        $created = $this->products->create($data);

        $entity = $this->products->findById($created->id);

        return $this->pricedToArray($entity);
    }

    public function updateProduct(int $id, array $data): array
    {
        if (isset($data['provider_price']) || isset($data['margin']) || isset($data['admin_fee'])) {
            $current = $this->products->findById($id);
            $provider = (float) ($data['provider_price'] ?? $current?->providerPrice ?? 0);
            $margin = (float) ($data['margin'] ?? $current?->margin ?? 0);
            $adminFee = (float) ($data['admin_fee'] ?? $current?->adminFee ?? 0);
            $data['selling_price'] = round($provider + $margin + $adminFee, 2);
        }

        $this->products->update($id, $data);
        $this->pricing->clearProductCache($id);

        return $this->pricedToArray($this->products->findById($id));
    }

    public function deleteProduct(int $id): void
    {
        $this->products->delete($id);
        $this->pricing->clearProductCache($id);
    }

    public function listProducts(array $filters = []): array
    {
        $entities = $this->products->getAvailableByCategory(
            $filters['category'] ?? 'pulsa',
            $filters['operator_id'] ?? null,
        );

        return array_map(fn (PpoProduct $p) => $this->pricedToArray($p), $entities);
    }

    private function applyDerivedPricing(array &$data): void
    {
        $providerPrice = (float) ($data['provider_price'] ?? 0);
        $margin = (float) ($data['margin'] ?? 0);
        $adminFee = (float) ($data['admin_fee'] ?? 0);
        $data['commission'] = $data['commission'] ?? 0;
        $data['selling_price'] = round($providerPrice + $margin + $adminFee, 2);
    }

    private function pricedToArray(PpoProduct $entity): array
    {
        $priced = $this->pricing->priceProduct($entity);
        $product = $priced['product'];

        return [
            'id' => $product->id,
            'operator_id' => $product->operatorId,
            'category' => $product->category,
            'product_type' => $product->productType,
            'name' => $product->name,
            'brand' => $product->brand,
            'nominal' => $product->nominal,
            'provider_price' => $product->providerPrice,
            'admin_fee' => $product->adminFee,
            'commission' => $product->commission,
            'margin' => $product->margin,
            'selling_price' => $product->sellingPrice,
            'icon_url' => $product->iconUrl,
            'is_available' => $product->isAvailable,
        ];
    }
}
