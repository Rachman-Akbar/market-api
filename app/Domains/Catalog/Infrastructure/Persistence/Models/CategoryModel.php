<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'id',
        'entity_id',
        'name',
        'slug',
        'description'
    ];

    public $incrementing = false;

    protected $keyType = 'string';
}