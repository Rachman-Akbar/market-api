<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Models\CategoryModel;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;

final class ProductModel extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'store_id',
        'primary_category_id',
        'seller_id',
        'name',
        'slug',
        'description',
        'brand',
        'thumbnail',
        'status',
        'is_active',
    ];

    protected $casts = [
        'store_id' => 'integer',
        'primary_category_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function store()
    {
        return $this->belongsTo(StoreModel::class, 'store_id');
    }

    public function primaryCategory()
    {
        return $this->belongsTo(CategoryModel::class, 'primary_category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(
            CategoryModel::class,
            'product_categories',
            'product_id',
            'category_id'
        )->withPivot('is_primary');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariantModel::class, 'product_id');
    }

    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValueModel::class, 'product_id');
    }
}
