<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Presentation\Http\Controllers;

use App\Domains\Identity\User\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Seller\Stores\Application\DTOs\StoreData;
use App\Domains\Seller\Stores\Application\Queries\GetStoreByIdQuery;
use App\Domains\Seller\Stores\Application\Queries\GetStoreBySlugQuery;
use App\Domains\Seller\Stores\Application\Queries\ListProductByStoreSlugQuery;
use App\Domains\Seller\Stores\Application\Queries\ListStoreQuery;
use App\Domains\Seller\Stores\Application\UseCases\CreateStoreUseCase;
use App\Domains\Seller\Stores\Application\UseCases\UpdateStoreUseCase;
use App\Domains\Seller\Stores\Presentation\Http\Resources\StoreListResource;
use App\Domains\Seller\Stores\Presentation\Http\Resources\StoreResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;

final class StoreController extends Controller
{
    public function __construct(
        private ListProductByStoreSlugQuery $listProductByStoreSlugQuery,
        private GetStoreByIdQuery $getStoreByIdQuery,
        private UserRepositoryInterface $userRepository
    ) {}

    public function index(Request $request, ListStoreQuery $query): AnonymousResourceCollection
    {
        $filters = $request->query();
        $filters['public_only'] = true;

        return StoreListResource::collection($query->execute($filters));
    }

    public function manage(Request $request, ListStoreQuery $query): AnonymousResourceCollection
    {
        return StoreListResource::collection($query->execute($request->query()));
    }

    public function showBySlug(string $slug, GetStoreBySlugQuery $query): StoreResource
    {
        $store = $query->execute($slug, true);
        abort_if(! $store, 404, 'Toko tidak ditemukan.');

        return new StoreResource(StoreData::fromEntity($store));
    }

    public function showById(int $id): StoreResource
    {
        $store = $this->getStoreByIdQuery->execute($id);
        abort_if(! $store || ! $store->isPubliclyAvailable(), 404, 'Toko tidak ditemukan.');

        return new StoreResource(StoreData::fromEntity($store));
    }

    public function manageShow(Request $request, int $id): StoreResource
    {
        $store = $this->getStoreByIdQuery->execute($id);
        abort_if(! $store, 404, 'Toko tidak ditemukan.');
        abort_unless($store->userId() === (string) $request->user()->id, 403, 'Anda tidak memiliki akses ke toko ini.');

        return new StoreResource(StoreData::fromEntity($store));
    }

    public function productsBySlug(Request $request, string $slug): JsonResponse
    {
        $products = $this->listProductByStoreSlugQuery->execute(
            $slug,
            $request->only(['search', 'category_id'])
        );

        return response()->json(['data' => $products->values()]);
    }

    public function registerStore(Request $request, CreateStoreUseCase $useCase): JsonResponse
    {
        $validated = $request->validate($this->rules(true));
        $validated = $this->storeUploads($request, $validated);
        $validated['detail'] = $this->detailData($validated);

        $store = $useCase->execute((string) $request->user()->id, $validated, $request->header('X-Device-Name'));

        return (new StoreResource($store))
            ->additional(['message' => 'Toko berhasil dibuat dan menunggu persetujuan admin.'])
            ->response()
            ->setStatusCode(201);
    }

    public function updateStore(int $id, Request $request, UpdateStoreUseCase $useCase): JsonResponse
    {
        $validated = $request->validate($this->rules(false));
        unset($validated['status']);
        $validated = $this->storeUploads($request, $validated);
        $detail = $this->detailData($validated);

        if ($detail !== []) {
            $validated['detail'] = $detail;
        }

        $role = (string) ($this->userRepository->getActiveRoleFromCurrentToken($request->user()) ?: 'buyer');
        $store = $useCase->execute($id, (string) $request->user()->id, $role, $validated);

        return (new StoreResource($store))
            ->additional(['message' => 'Toko berhasil diperbarui.'])
            ->response();
    }

    private function rules(bool $creating): array
    {
        $required = $creating ? 'required' : 'sometimes';

        return [
            'store_name' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'phone' => [$creating ? 'required' : 'nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'city' => [$creating ? 'required' : 'nullable', 'string', 'max:80'],
            'province' => [$creating ? 'required' : 'nullable', 'string', 'max:80'],
            'address' => [$creating ? 'required' : 'nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
            'detail' => ['nullable', 'array'],
            'detail.owner_name' => ['nullable', 'string', 'max:120'],
            'detail.owner_phone' => ['nullable', 'string', 'max:30'],
            'detail.description' => ['nullable', 'string'],
            'detail.shipping_policy' => [$creating ? 'required' : 'nullable', 'string', 'min:10'],
            'detail.return_policy' => [$creating ? 'required' : 'nullable', 'string', 'min:10'],
            'detail.open_days' => [$creating ? 'required' : 'nullable', 'string', 'max:120'],
            'detail.open_time' => [$creating ? 'required' : 'nullable', 'date_format:H:i'],
            'detail.close_time' => [$creating ? 'required' : 'nullable', 'date_format:H:i', 'after:detail.open_time'],
            'detail.whatsapp_url' => ['nullable', 'url', 'max:255'],
            'detail.instagram_url' => ['nullable', 'url', 'max:255'],
            'detail.tiktok_url' => ['nullable', 'url', 'max:255'],
            'detail.website_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    private function storeUploads(Request $request, array $validated): array
    {
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('stores/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            $validated['banner_url'] = $request->file('banner')->store('stores/banners', 'public');
        }

        unset($validated['banner']);

        return $validated;
    }

    private function detailData(array $validated): array
    {
        return Arr::where($validated['detail'] ?? [], fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
