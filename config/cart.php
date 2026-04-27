<?php

return [
    'product_model' => env(
        'CART_PRODUCT_MODEL',
        App\Domains\Catalog\Infrastructure\Persistence\Models\ProductModel::class
    ),

    'product_fields' => [
        'name' => 'name',
        'price' => 'price',
        'image' => 'image',
        'stock' => 'stock',
        'is_active' => 'is_active',
    ],
];
