<?php

namespace App\Domains\Catalog\Application\UseCases\Entity;

use App\Domains\Catalog\Domain\Entities\Entity;
use App\Domains\Catalog\Domain\Repositories\EntityRepository;

class CreateEntityUseCase
{
    private $entities;
    public function __construct(EntityRepository $entities)
    {
        $this->entities = $entities;
    }
    public function execute(array $data): Entity
    {
        if ($this->entities->existsSlug($data['slug'])) {
            throw new \Exception('Slug already exists');
        }
        $entity = new Entity(null, $data['name'], $data['slug'], $data['description'] ?? null);
        return $this->entities->create($entity);
    }
}
