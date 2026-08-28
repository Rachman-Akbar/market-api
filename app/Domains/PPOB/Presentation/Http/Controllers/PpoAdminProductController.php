<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Presentation\Http\Controllers;

use App\Domains\PPOB\Application\Services\PpoCatalogService;
use App\Domains\PPOB\Domain\Repositories\PpoProductRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PpoAdminProductController extends Controller
{
    public function __construct(
        private PpoCatalogService $catalog,
        private PpoProductRepositoryInterface $products,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'max:40'],
            'operator_id' => ['nullable', 'integer', 'exists:ppob_operators,id'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->catalog->listProducts($filters),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'operator_id' => ['nullable', 'integer', 'exists:ppob_operators,id'],
            'category' => ['required', 'string', 'max:40'],
            'product_type' => ['required', 'in:prepaid,postpaid'],
            'provider_product_code' => ['required', 'string', 'max:80', 'unique:ppob_products,provider_product_code'],
            'name' => ['required', 'string', 'max:160'],
            'brand' => ['nullable', 'string', 'max:120'],
            'nominal' => ['nullable', 'string', 'max:80'],
            'provider_price' => ['required', 'numeric', 'min:0'],
            'admin_fee' => ['nullable', 'numeric', 'min:0'],
            'commission' => ['nullable', 'numeric', 'min:0'],
            'margin' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:active,inactive'],
            'is_available' => ['sometimes', 'boolean'],
            'icon_url' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['is_active'] = true;
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['is_available'] = $validated['is_available'] ?? true;

        $data = $this->catalog->createProduct($validated);

        return response()->json([
            'success' => true,
            'message' => 'Produk PPOB berhasil dibuat.',
            'data' => $data,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'operator_id' => ['nullable', 'integer', 'exists:ppob_operators,id'],
            'category' => ['sometimes', 'string', 'max:40'],
            'product_type' => ['sometimes', 'in:prepaid,postpaid'],
            'name' => ['sometimes', 'string', 'max:160'],
            'brand' => ['nullable', 'string', 'max:120'],
            'nominal' => ['nullable', 'string', 'max:80'],
            'provider_price' => ['sometimes', 'numeric', 'min:0'],
            'admin_fee' => ['sometimes', 'numeric', 'min:0'],
            'commission' => ['sometimes', 'numeric', 'min:0'],
            'margin' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:active,inactive'],
            'is_available' => ['sometimes', 'boolean'],
            'icon_url' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['updated_by'] = $request->user()->id;

        $data = $this->catalog->updateProduct($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Produk PPOB berhasil diperbarui.',
            'data' => $data,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->catalog->deleteProduct($id);

        return response()->json([
            'success' => true,
            'message' => 'Produk PPOB berhasil dihapus.',
        ]);
    }
}
