<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use App\Domains\Catalog\Application\UseCases\CatalogGroup\CreateCatalogGroupUseCase;
use App\Domains\Catalog\Application\UseCases\CatalogGroup\UpdateCatalogGroupUseCase;
use App\Domains\Catalog\Application\UseCases\CatalogGroup\GetCatalogGroupUseCase;
use App\Domains\Catalog\Presentation\Http\Requests\CatalogGroupRequest;
use App\Domains\Catalog\Presentation\Http\Resources\CatalogGroupResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CatalogGroupController extends Controller
{

public function index(GetCatalogGroupUseCase $useCase)
{
    $entities = $useCase->execute();

    return response()->json([
        'success' => true,
        'data' => CatalogGroupResource::collection($entities),
    ]);
}

    public function store(CreateCatalogGroupUseCase $useCase, CatalogGroupRequest $request)
    {
        $CatalogGroup = $useCase->execute($request->validated());
        return response()->json([
            'success' => true,
            'data' => new CatalogGroupResource($CatalogGroup),
            'message' => null
        ], 201);
    }

    public function update(UpdateCatalogGroupUseCase $useCase, CatalogGroupRequest $request, $id)
    {
        $CatalogGroup = $useCase->execute($id, $request->validated());
        return response()->json([
            'success' => true,
            'data' => new CatalogGroupResource($CatalogGroup),
            'message' => null
        ]);
    }
}
