<?php

declare(strict_types=1);

namespace App\Domains\Seller\Showcase\Presentation\Http\Controllers;

use App\Domains\Seller\Showcase\Application\Services\ShowcaseService;
use App\Domains\Seller\Showcase\Presentation\Http\Requests\ShowcaseRequest;
use App\Domains\Seller\Showcase\Presentation\Http\Resources\ShowcaseResource;
use App\Domains\Shared\Presentation\Http\Concerns\ResolvesActiveRole;
use App\Domains\Shared\Presentation\Http\Concerns\ResolvesSellerStoreContext;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class ShowcaseController extends Controller
{
    use ResolvesActiveRole;
    use ResolvesSellerStoreContext;
    public function __construct(private ShowcaseService $service) {}

    public function publicIndex(int $storeId): JsonResponse
    {
        $rows = \App\Domains\Seller\Showcase\Infrastructure\Persistence\Models\ShowcaseModel::query()
            ->active()
            ->where('store_id', $storeId)
            ->whereHas('store', fn ($query) => $query->publiclyAvailable())
            ->with(['store:id,name', 'products' => fn ($query) => $query
                ->where('products.status', 'published')
                ->where('products.is_active', true)
                ->with(['variants' => fn ($variantQuery) => $variantQuery->orderByDesc('is_default')->orderBy('id')])])
            ->withCount(['products' => fn ($query) => $query
                ->where('products.status', 'published')
                ->where('products.is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return ShowcaseResource::collection($rows)->additional(['success' => true])->response();
    }

    public function index(Request $request): JsonResponse
    {
        $rows = $this->service->paginate(
            $request->only(['search', 'is_active']),
            min(100, max(1, (int) $request->query('per_page', 20))),
            $this->sellerStoreScope($request)
        );

        return ShowcaseResource::collection($rows)->additional(['success' => true])->response();
    }

    public function store(ShowcaseRequest $request): JsonResponse
    {
        try {
            $row = $this->service->save($request->validated(), null, $this->sellerStoreScope($request));

            return (new ShowcaseResource($row))->additional(['success' => true, 'message' => 'Etalase berhasil dibuat.'])->response()->setStatusCode(201);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return (new ShowcaseResource($this->service->find($id, $this->sellerStoreScope($request))))->additional(['success' => true])->response();
    }

    public function update(ShowcaseRequest $request, int $id): JsonResponse
    {
        try {
            $row = $this->service->save($request->validated(), $id, $this->sellerStoreScope($request));

            return (new ShowcaseResource($row))->additional(['success' => true, 'message' => 'Etalase berhasil diperbarui.'])->response();
        } catch (InvalidArgumentException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->delete($id, $this->sellerStoreScope($request));

        return response()->json(['success' => true, 'message' => 'Etalase berhasil dihapus.']);
    }

    private function sellerStoreScope(Request $request): ?int
    {
        return $this->hasActiveRole($request, 'seller') ? $this->resolveSellerStoreId($request) : null;
    }
}
