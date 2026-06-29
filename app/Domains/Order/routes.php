<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Berikan prefix 'catalog' di sini agar menjadi api/v1/catalog/...
Route::prefix('order')->group(function () {

    Route::group([], app_path('Domains/Order/Cart/Presentation/routes.php'));
    Route::group([], app_path('Domains/Order/Addresses/Presentation/routes.php'));
    Route::group([], app_path('Domains/Order/Wishlist/Presentation/routes.php'));
    Route::group([], app_path('Domains/Order/Ordering/Presentation/routes.php'));

});
