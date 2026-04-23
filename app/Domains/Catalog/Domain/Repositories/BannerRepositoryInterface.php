<?php

namespace App\Domains\Catalog\Domain\Repositories;

use App\Domains\Catalog\Domain\Entities\Banner;

interface BannerRepositoryInterface
{
    /** @return array<int, Banner> */
    public function all(): array;
}