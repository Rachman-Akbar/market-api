<?php

declare(strict_types=1);

namespace App\Domains\Admin\GameContent\Presentation\Http\Controllers;

use App\Domains\Admin\GameContent\Application\Services\AdminGameContentService;
use App\Domains\Engagement\Gaming\Infrastructure\Persistence\Repositories\EloquentGameContentRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class AdminGameContentController extends Controller
{
    public function __construct(private AdminGameContentService $service) {}

    public function types(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->types(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));

        $rows = $this->service->paginate(
            $request->only(['game_type', 'difficulty', 'search', 'is_active']),
            $perPage
        );

        return response()->json([
            'success' => true,
            'data' => $rows->getCollection()->map(
                fn ($row) => $this->service->format($row)
            )->values(),
            'meta' => [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $row = $this->service->find($id);

        return response()->json([
            'success' => true,
            'data' => $this->service->format($row),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $row = $this->service->create(
            $validated,
            (string) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'data' => $this->service->format($row),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $row = $this->service->update(
            $id,
            $validated,
            (string) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'data' => $this->service->format($row),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->delete($id, (string) $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => ['deleted' => true],
        ]);
    }

    private function rules(): array
    {
        return [
            'game_type' => ['required', 'string', Rule::in(EloquentGameContentRepository::SUPPORTED_TYPES)],
            'title' => ['required', 'string', 'max:160'],
            'difficulty' => ['nullable', 'string', 'max:30'],
            'payload' => ['required', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
