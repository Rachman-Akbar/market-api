<?php

namespace App\Domains\Catalog\Domain\Entities;

class Category
{
    public $id;
    public $entity_id;
    public $name;
    public $slug;
    public $description;

    public function __construct($id, $entity_id, $name, $slug, $description = null)
    {
        $this->id = $id;
        $this->entity_id = $entity_id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
    }
}
