<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'seller_id',
        'name',
        'slug',
        'description',
        'price',
        'status',
    ];
    // Relasi ke kategori (many-to-many)
    public function categories()
    {
        // Asumsi pivot table: category_product, foreign keys: product_id, category_id
        return $this->belongsToMany(\App\Models\Category::class, 'product_categories', 'product_id', 'category_id');
    }

    // Relasi ke gambar produk (one-to-many)
    public function images()
    {
        return $this->hasMany(\App\Models\ProductImage::class, 'product_id');
    }

    // Relasi ke stok (one-to-one)
    public function stock()
    {
        return $this->hasOne(\App\Models\Stock::class, 'product_id');
    }
}
