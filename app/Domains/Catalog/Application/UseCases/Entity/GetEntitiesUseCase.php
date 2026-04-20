<?php

namespace App\Domains\Catalog\Application\UseCases\Entity;

use App\Domains\Catalog\Domain\Repositories\EntityRepository;

class GetEntitiesUseCase
{
    private $entities;
    public function __construct(EntityRepository $entities)
    {
        $this->entities = $entities;
    }
    public function execute(array $filters = [], int $perPage = 15)
    {
        return $this->entities->paginate($filters, $perPage);
    }
}
