<?php

namespace App\Domains\Catalog\Product\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;

use App\Domains\Catalog\Product\Application\Query\ProductAttribute\ListProductAttributesQuery;
use App\Domains\Catalog\Product\Application\Query\ProductAttribute\GetProductAttributeQuery;



use App\Domains\Catalog\Presentation\Http\Resources\ProductAttributeResource;

class ProductAttributeController extends Controller
{
    public function index(
        ListProductAttributesQuery $query
    ) {
        return ProductAttributeResource::collection(
            $query ->execute()
        );
    }

    public function show(
        int $id,
        GetProductAttributeQuery $query
    ) {
        $attribute = $query->execute($id);

        abort_if(!$attribute, 404);

        return new ProductAttributeResource(
            $attribute
        );
    }
}
