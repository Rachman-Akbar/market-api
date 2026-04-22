<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Domains\Catalog\Application\UseCases\Banner\GetBannerUseCase;
use App\Domains\Catalog\Presentation\Http\Resources\BannerResource;

class BannerController extends Controller
{
    public function __construct(
        private GetBannerUseCase $useCase
    ) {}

    public function index()
    {
        return BannerResource::collection(
            $this->useCase->execute()
        );
    }
}
