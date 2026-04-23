<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Domains\Catalog\Application\UseCases\Store\GetStoreUseCase;
use App\Domains\Catalog\Presentation\Http\Resources\StoreResource;

class StoreController extends Controller
{
    public function __construct(
        private GetStoreUseCase $useCase
    ) {}

    public function index()
    {
        return StoreResource::collection(
            $this->useCase->execute()
        );
    }
}