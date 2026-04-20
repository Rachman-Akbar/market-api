<?php

namespace App\Domains\Catalog\Application\UseCases\Entity;

use App\Domains\Catalog\Domain\Repositories\EntityRepository;

class UpdateEntityUseCase
{
    private $entities;
    public function __construct(EntityRepository $entities)
    {
        $this->entities = $entities;
    }
    public function execute($id, array $data)
    {
        if ($this->entities->existsSlug($data['slug'], $id)) {
            throw new \Exception('Slug already exists');
        }
        return $this->entities->update($id, $data);
    }
}
