<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Presentation\Http\Controllers;

use App\Domains\PPOB\Application\Services\PpoCatalogService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PpoCatalogController extends Controller
{
    public function __construct(
        private PpoCatalogService $catalog,
    ) {}

    public function categories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->catalog->categories(),
        ]);
    }

    public function operators(Request $request): JsonResponse
    {
        $category = $request->query('category');

        $data = collect($this->catalog->operators($category ?: null))->map(fn ($o) => [
            'id' => $o->id,
            'name' => $o->name,
            'slug' => $o->slug,
            'category' => $o->category,
            'icon_url' => $o->iconUrl,
        ])->all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:40'],
            'operator_id' => ['nullable', 'integer', 'exists:ppob_operators,id'],
        ]);

        $data = $this->catalog->products($validated['category'], $validated['operator_id'] ?? null);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
