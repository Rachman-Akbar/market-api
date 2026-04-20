<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use App\Domains\Catalog\Application\UseCases\Entity\CreateEntityUseCase;
use App\Domains\Catalog\Application\UseCases\Entity\UpdateEntityUseCase;
use App\Domains\Catalog\Application\UseCases\Entity\GetEntitiesUseCase;
use App\Domains\Catalog\Presentation\Http\Requests\EntityRequest;
use App\Domains\Catalog\Presentation\Http\Resources\EntityResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EntityController extends Controller
{
    public function index(GetEntitiesUseCase $useCase, Request $request)
    {
        $entities = $useCase->execute($request->all(), $request->get('per_page', 15));
        return response()->json([
            'success' => true,
            'data' => EntityResource::collection($entities),
            'message' => null
        ]);
    }

    public function store(CreateEntityUseCase $useCase, EntityRequest $request)
    {
        $entity = $useCase->execute($request->validated());
        return response()->json([
            'success' => true,
            'data' => new EntityResource($entity),
            'message' => null
        ], 201);
    }

    public function update(UpdateEntityUseCase $useCase, EntityRequest $request, $id)
    {
        $entity = $useCase->execute($id, $request->validated());
        return response()->json([
            'success' => true,
            'data' => new EntityResource($entity),
            'message' => null
        ]);
    }
}
