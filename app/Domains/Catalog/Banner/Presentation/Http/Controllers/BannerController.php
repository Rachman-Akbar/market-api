<?php

namespace App\Domains\Catalog\Banner\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Domains\Catalog\Banner\Application\Queries\GetBannerQuery;
use App\Domains\Catalog\Banner\Presentation\Http\Resources\BannerResource;

class BannerController extends Controller
{
    public function __construct(
        private GetBannerQuery $useCase
    ) {}

    public function index()
    {
        return BannerResource::collection(
            $this->useCase->execute()
        );
    }
}
