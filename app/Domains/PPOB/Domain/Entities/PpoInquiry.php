<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Entities;

final class PpoInquiry
{
    public function __construct(
        public ?int $id,
        public string $referenceId,
        public string $userId,
        public ?int $operatorId,
        public ?string $productCode,
        public string $category,
        public string $customerId,
        public ?string $trId,
        public ?string $customerName,
        public ?string $customerNo,
        public ?float $billAmount,
        public ?float $adminCharge,
        public ?string $adminChargeMessage,
        public ?array $detail,
        public string $status,
        public ?string $expiresAt = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}
}
