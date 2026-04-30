<?php

declare(strict_types=1);

namespace App\Domains\Stores\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Domains\Stores\Application\UseCases\ListStoreUseCase;
use App\Domains\Stores\Application\UseCases\GetStoreBySlugUseCase;
use App\Domains\Stores\Application\UseCases\ListProductByStoreSlugUseCase;
use App\Domains\Stores\Presentation\Http\Resources\StoreResource;
use App\Domains\Stores\Presentation\Http\Resources\ProductResource;

final class StoreController extends Controller
{
    public function index(
        Request $request,
        ListStoreUseCase $useCase
    ): AnonymousResourceCollection {
        $stores = $useCase->execute($request->all());

        return StoreResource::collection($stores);
    }

    public function showBySlug(
        string $slug,
        GetStoreBySlugUseCase $useCase
    ): StoreResource {
        $store = $useCase->execute($slug);

        abort_if(! $store, 404, 'Store not found.');

        return new StoreResource($store);
    }

    public function productsBySlug(
        string $slug,
        Request $request,
        ListProductByStoreSlugUseCase $useCase
    ): AnonymousResourceCollection {
        $products = $useCase->execute($slug, $request->all());

        return ProductResource::collection($products);
    }
}
