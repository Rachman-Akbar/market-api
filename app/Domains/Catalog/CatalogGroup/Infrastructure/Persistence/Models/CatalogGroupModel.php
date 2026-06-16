<?php

namespace App\Domains\Catalog\CatalogGroup\Infrastructure\Persistence\Models;

use App\Domains\Catalog\Category\Infrastructure\Persistence\Models\CategoryModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CatalogGroupModel extends Model
{
    protected $table = 'catalog_groups';

    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function categories(): HasMany
    {
        return $this->hasMany(CategoryModel::class, 'catalog_group_id');
    }
}

