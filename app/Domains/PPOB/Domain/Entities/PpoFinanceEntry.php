<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Entities;

final class PpoFinanceEntry
{
    public function __construct(
        public ?int $id,
        public string $sourceType,
        public string $sourceId,
        public ?int $ppobTransactionId,
        public string $referenceId,
        public string $transactionType,
        public string $title,
        public ?string $description,
        public float $amount,
        public string $status,
        public string $occurredAt,
        public ?array $metadata = null,
        public bool $isActive = true,
    ) {}
}
