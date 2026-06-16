<?php

namespace App\Domains\Catalog\Banner\Domain\Repositories;

use App\Domains\Catalog\Banner\Domain\Entities\Banner;

interface BannerRepositoryInterface
{
    /** @return array<int, Banner> */
    public function all(): array;
}
