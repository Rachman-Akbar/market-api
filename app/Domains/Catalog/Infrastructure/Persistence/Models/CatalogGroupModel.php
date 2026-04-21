<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CatalogGroupModel extends Model
{
    use HasFactory;

    protected $table = 'catalog_groups';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function categories()
    {
        return $this->hasMany(CategoryModel::class, 'catalog_group_id');
    }
}
