<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Domains\Catalog\Application\DTOs\StoreData;
use App\Domains\Catalog\Application\UseCases\Store\GetStoreUseCase;
use App\Domains\Catalog\Application\UseCases\Store\CreateStoreUseCase;
use App\Domains\Catalog\Presentation\Http\Requests\StoreRequest;
use App\Domains\Catalog\Presentation\Http\Resources\StoreResource;

class StoreController extends Controller
{
    public function __construct(
        private GetStoreUseCase $useCase
    ) {}

    public function index()
    {
        $stores = $this->useCase->execute();

        return StoreResource::collection($stores);
    }
}
