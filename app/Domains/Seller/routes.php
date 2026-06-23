<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Berikan prefix 'seller' di sini agar menjadi api/v1/seller/...
Route::prefix('seller')->group(function () {

    // Me-load sub-route feature Stores
    Route::group([], app_path('Domains/Seller/Stores/Presentation/routes.php'));

});
