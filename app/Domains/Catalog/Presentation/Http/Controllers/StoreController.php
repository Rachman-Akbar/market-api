<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Domains\Catalog\Application\UseCases\Store\ListStoreUseCase;
use App\Domains\Catalog\Application\UseCases\Store\GetStoreBySlugUseCase;
use App\Domains\Catalog\Application\UseCases\Store\ListProductByStoreSlugUseCase;
use App\Domains\Catalog\Presentation\Http\Resources\StoreResource;
use App\Domains\Catalog\Presentation\Http\Resources\ProductResource;

class StoreController extends Controller
{
    public function index(
        Request $request,
        ListStoreUseCase $useCase
    ) {
        $stores = $useCase->execute($request->all());

        return StoreResource::collection($stores);
    }

    public function showBySlug(
        string $slug,
        GetStoreBySlugUseCase $useCase
    ) {
        $store = $useCase->execute($slug);

        abort_if(!$store, 404);

        return new StoreResource($store);
    }

    public function productsBySlug(
        string $slug,
        ListProductByStoreSlugUseCase $useCase
    ) {
        $products = $useCase->execute($slug);

        return ProductResource::collection($products);
    }
}