<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::group([], app_path('Domains/Identity/routes.php'));
Route::group([], app_path('Domains/Catalog/routes.php'));
Route::group([], app_path('Domains/Seller/routes.php'));
Route::group([], app_path('Domains/Admin/routes.php'));
Route::group([], app_path('Domains/Order/routes.php'));
