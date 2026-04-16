<?php

use App\Http\Controllers\Api\MarketplaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Route Registration
|--------------------------------------------------------------------------
| Domain routes are registered per bounded context to keep architecture
| boundaries explicit and scalable.
*/

$domainRouteFiles = [
    app_path('Domains/Identity/Presentation/routes.php'),
    app_path('Domains/Users/Presentation/routes.php'),
    app_path('Domains/Catalog/Presentation/routes.php'),
    app_path('Domains/Inventory/Presentation/routes.php'),
    app_path('Domains/Orders/Presentation/routes.php'),
    app_path('Domains/Payments/Presentation/routes.php'),
    app_path('Domains/Reviews/Presentation/routes.php'),
    app_path('Domains/Realtime/Presentation/routes.php'),
];

Route::get('/', function () {
    return response()->json([
        'message' => 'UKOMP API is running.',
        'version_prefix' => '/api/v1',
    ]);
});

Route::get('/products', [MarketplaceController::class, 'products']);
Route::get('/products/{slug}', [MarketplaceController::class, 'productBySlug']);
Route::get('/categories', [MarketplaceController::class, 'categories']);
Route::get('/stores/{slug}', [MarketplaceController::class, 'storeBySlug']);
Route::post('/cart', [MarketplaceController::class, 'upsertCart']);
Route::post('/orders', [MarketplaceController::class, 'createOrder']);

Route::prefix('v1')->group(function () use ($domainRouteFiles): void {
    foreach ($domainRouteFiles as $routeFile) {
        if (file_exists($routeFile)) {
            require $routeFile;
        }
    }
});
