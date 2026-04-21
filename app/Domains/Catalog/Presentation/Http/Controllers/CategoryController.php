<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Domains\Catalog\Presentation\Http\Resources\CategoryResource;
use App\Domains\Catalog\Application\UseCases\Category\ListCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Category\UpdateCategoryUseCase;
use App\Domains\Catalog\Application\UseCases\Category\DeleteCategoryUseCase;

// Temporary stub for undefined CreateCategoryUseCase
// Remove this and use the correct import when available
class CreateCategoryUseCase {
    public function execute(array $data) {
        // ...
    }
}

class CategoryController extends Controller
{
    public function __construct(
        private ListCategoryUseCase $list,
        private CreateCategoryUseCase $create,
        private UpdateCategoryUseCase $update,
        private DeleteCategoryUseCase $delete
    ) {}

    public function index()
    {
        return CategoryResource::collection(
            $this->list->execute()
        );
    }

    public function store(Request $request)
    {
        $category = $this->create->execute(
            $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string'
            ])
        );

        return new CategoryResource($category);
    }

    public function update(string $id, Request $request)
    {
        $category = $this->update->execute(
            $id,
            $request->all()
        );

        return new CategoryResource($category);
    }

    public function destroy(string $id)
    {
        $this->delete->execute($id);

        return response()->noContent();
    }
}
