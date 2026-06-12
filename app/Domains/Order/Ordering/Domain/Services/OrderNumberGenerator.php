<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Domain\Services;

final class OrderNumberGenerator
{
    public function generate(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(bin2hex(random_bytes(4)));

        return "ORD-{$date}-{$random}";
    }
}
