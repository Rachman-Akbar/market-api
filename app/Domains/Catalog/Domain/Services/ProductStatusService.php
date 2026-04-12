<?php

namespace App\Domains\Catalog\Domain\Services;

final class ProductStatusService
{
    public function normalize(string $status): string
    {
        $allowed = ['draft', 'active', 'inactive'];

        return in_array($status, $allowed, true) ? $status : 'draft';
    }
}
