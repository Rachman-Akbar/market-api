<?php

namespace App\Domains\Catalog\Category\Application\Queries;

use Illuminate\Support\Collection;
use App\Domains\Catalog\Category\Domain\Repositories\CategoryRepositoryInterface;

final class GetHeaderMenuQuery
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(): Collection
    {
        return $this->repository->getHeaderMenu();
    }
}
