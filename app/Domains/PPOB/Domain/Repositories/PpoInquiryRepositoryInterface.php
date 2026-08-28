<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Repositories;

use App\Domains\PPOB\Domain\Entities\PpoInquiry;

interface PpoInquiryRepositoryInterface
{
    public function findById(int $id): ?PpoInquiry;

    public function findActiveByReferenceId(string $referenceId): ?PpoInquiry;

    public function create(array $data): PpoInquiry;

    public function update(int $id, array $data): PpoInquiry;
}
