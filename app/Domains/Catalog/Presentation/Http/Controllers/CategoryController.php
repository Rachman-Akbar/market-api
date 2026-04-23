<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Domains\Catalog\Application\UseCases\Category\ListCategoryUseCase;
use App\Domains\Catalog\Presentation\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    public function __construct(
        private ListCategoryUseCase $list
    ) {}

    public function index()
    {
        return CategoryResource::collection(
            $this->list->execute()
        );
    }
}