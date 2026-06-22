<?php

namespace App\Domains\Catalog\Banner\Application\UseCases;

use App\Domains\Catalog\Banner\Domain\Repositories\BannerRepositoryInterface;
use Exception;

class DeleteBannerUseCase
{
    public function __construct(private BannerRepositoryInterface $repository) {}

    public function execute(int $id): bool
    {
        if (!$this->repository->findById($id)) {
            throw new Exception("Banner toko tidak ditemukan.");
        }

        return $this->repository->delete($id);
    }
}
