<?php

namespace App\Domains\Catalog\Domain\Entities;

class Entity
{
    public $id;
    public $name;
    public $slug;
    public $description;

    public function __construct($id, $name, $slug, $description = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
    }
}
