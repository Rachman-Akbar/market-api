<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Catalog\Banner\Presentation\Http\Controllers\BannerController;

        Route::prefix('banners')
            ->name('banners.')
            ->group(function () {

                Route::get(
                    '/',
                    [BannerController::class, 'index']
                )->name('index');
            });
