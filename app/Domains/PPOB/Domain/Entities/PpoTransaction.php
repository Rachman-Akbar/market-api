<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Entities;

final class PpoTransaction
{
    public function __construct(
        public ?int $id,
        public string $referenceId,
        public string $userId,
        public ?int $operatorId,
        public ?int $productId,
        public ?string $providerProductCode,
        public ?string $productName,
        public string $category,
        public string $productType,
        public string $customerId,
        public ?string $customerName,
        public ?float $billAmount,
        public float $providerPrice,
        public float $adminFee,
        public float $commission,
        public float $margin,
        public float $revenue,
        public float $netProfit,
        public float $totalAmount,
        public string $status,
        public ?string $providerStatus = null,
        public ?string $providerMessage = null,
        public ?string $trId = null,
        public ?string $sn = null,
        public ?string $pin = null,
        public ?array $providerRawResponse = null,
        public ?string $callbackSignature = null,
        public ?string $paidAt = null,
        public ?string $completedAt = null,
        public ?string $expiresAt = null,
        public ?string $cancelledAt = null,
        public ?array $metadata = null,
        public bool $isActive = true,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}
}
