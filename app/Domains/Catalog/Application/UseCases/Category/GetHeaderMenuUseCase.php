<?php

namespace App\Domains\Catalog\Application\UseCases\Category;

use Illuminate\Support\Collection;
use App\Domains\Catalog\Domain\Repositories\CategoryRepositoryInterface;

final class GetHeaderMenuUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(): Collection
    {
        return $this->repository->getHeaderMenu();
    }
}