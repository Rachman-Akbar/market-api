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


// 45e1e9f7-a60c-448e-bee5-9d7a7db1e7de
