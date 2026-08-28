<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Presentation\Http\Controllers;

use App\Domains\PPOB\Domain\Repositories\PpoOperatorRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PpoAdminOperatorController extends Controller
{
    public function __construct(
        private PpoOperatorRepositoryInterface $operators,
    ) {}

    public function index(): JsonResponse
    {
        $data = collect($this->operators->getActiveByCategory(null))->map(fn ($o) => [
            'id' => $o->id,
            'name' => $o->name,
            'slug' => $o->slug,
            'category' => $o->category,
            'brand' => $o->brand,
            'operator_prefix' => $o->operatorPrefix,
            'icon_url' => $o->iconUrl,
            'is_active' => $o->isActive,
        ])->all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'unique:ppob_operators,slug'],
            'category' => ['required', 'string', 'max:40'],
            'brand' => ['nullable', 'string', 'max:120'],
            'operator_prefix' => ['nullable', 'string', 'max:120'],
            'icon_url' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['is_active'] = true;

        $operator = $this->operators->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Operator PPOB berhasil dibuat.',
            'data' => ['id' => $operator->id, 'name' => $operator->name],
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'category' => ['sometimes', 'string', 'max:40'],
            'brand' => ['nullable', 'string', 'max:120'],
            'operator_prefix' => ['nullable', 'string', 'max:120'],
            'icon_url' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['updated_by'] = $request->user()->id;

        $operator = $this->operators->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Operator PPOB berhasil diperbarui.',
            'data' => ['id' => $operator->id, 'name' => $operator->name],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->operators->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Operator PPOB berhasil dihapus.',
        ]);
    }
}
