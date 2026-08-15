<?php

declare(strict_types=1);

use App\Domains\Shared\Spreadsheet\Presentation\Http\Controllers\SpreadsheetTransferController;
use Illuminate\Support\Facades\Route;

Route::prefix('catalog')->group(function (): void {
    $spreadsheetModulePattern = 'product|category|catalog-group|promotion|voucher|banner|order|income|expense|receivable|payable|stock|raw-material|raw-material-stock|product-costing|customer|review|cost-impact';

    Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'role:seller,admin'])
        ->prefix('spreadsheets/{module}')
        ->group(function () use ($spreadsheetModulePattern): void {
            Route::get('/template', [SpreadsheetTransferController::class, 'template'])
                ->where('module', $spreadsheetModulePattern);
            Route::post('/export', [SpreadsheetTransferController::class, 'export'])
                ->where('module', $spreadsheetModulePattern);
            Route::post('/import/preview', [SpreadsheetTransferController::class, 'previewImport'])
                ->where('module', $spreadsheetModulePattern);
            Route::post('/import', [SpreadsheetTransferController::class, 'import'])
                ->where('module', $spreadsheetModulePattern);
            Route::post('/bulk-delete', [SpreadsheetTransferController::class, 'bulkDelete'])
                ->where('module', $spreadsheetModulePattern);
        });

    $routeFiles = [
        app_path('Domains/Catalog/CatalogGroup/Presentation/routes.php'),
        app_path('Domains/Catalog/Category/Presentation/routes.php'),
        app_path('Domains/Catalog/Promotion/Presentation/routes.php'),
        app_path('Domains/Catalog/Promotion/Presentation/payment-routes.php'),
        app_path('Domains/Catalog/Banner/Presentation/routes.php'),
        app_path('Domains/Catalog/Product/Presentation/routes.php'),
        app_path('Domains/Catalog/Media/Presentation/routes.php'),
    ];

    foreach ($routeFiles as $routeFile) {
        if (file_exists($routeFile)) {
            Route::group([], $routeFile);
        }
    }
});

Route::prefix('seller')->group(function (): void {
    $sellerPromotionRoutes = app_path('Domains/Catalog/Promotion/Presentation/seller-routes.php');

    if (file_exists($sellerPromotionRoutes)) {
        Route::group([], $sellerPromotionRoutes);
    }
});
