<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;

use App\Domains\Catalog\Application\UseCases\ProductAttribute\ListProductAttributesUseCase;
use App\Domains\Catalog\Application\UseCases\ProductAttribute\GetProductAttributeUseCase;

use App\Domains\Catalog\Presentation\Http\Resources\ProductAttributeResource;

class ProductAttributeController extends Controller
{
    public function index(
        ListProductAttributesUseCase $useCase
    ) {
        return ProductAttributeResource::collection(
            $useCase->execute()
        );
    }

    public function show(
        int $id,
        GetProductAttributeUseCase $useCase
    ) {
        $attribute = $useCase->execute($id);

        abort_if(!$attribute, 404);

        return new ProductAttributeResource(
            $attribute
        );
    }
}
