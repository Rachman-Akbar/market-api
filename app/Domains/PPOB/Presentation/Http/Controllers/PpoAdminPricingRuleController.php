<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Presentation\Http\Controllers;

use App\Domains\PPOB\Domain\Repositories\PpoPricingRuleRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PpoAdminPricingRuleController extends Controller
{
    public function __construct(
        private PpoPricingRuleRepositoryInterface $pricingRules,
    ) {}

    public function index(): JsonResponse
    {
        $data = collect($this->pricingRules->getActive())->map(fn ($r) => [
            'id' => $r->id,
            'level' => $r->level,
            'category' => $r->category,
            'operator_id' => $r->operatorId,
            'product_id' => $r->productId,
            'margin_type' => $r->marginType,
            'margin_value' => $r->marginValue,
            'admin_fee_type' => $r->adminFeeType,
            'admin_fee_value' => $r->adminFeeValue,
            'commission_type' => $r->commissionType,
            'commission_value' => $r->commissionValue,
            'min_selling_price' => $r->minSellingPrice,
            'max_selling_price' => $r->maxSellingPrice,
            'priority' => $r->priority,
            'is_active' => $r->isActive,
        ])->all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateRule($request, false);

        $validated['created_by'] = $request->user()->id;
        $validated['is_active'] = true;

        $rule = $this->pricingRules->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Aturan harga PPOB berhasil dibuat.',
            'data' => ['id' => $rule->id, 'level' => $rule->level],
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $this->validateRule($request, true);

        $validated['updated_by'] = $request->user()->id;

        if (isset($validated['priority']) && $validated['priority'] === null) {
            unset($validated['priority']);
        }

        $rule = $this->pricingRules->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Aturan harga PPOB berhasil diperbarui.',
            'data' => ['id' => $rule->id, 'level' => $rule->level],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->pricingRules->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Aturan harga PPOB berhasil dihapus.',
        ]);
    }

    private function validateRule(Request $request, bool $partial): array
    {
        $rules = [
            'level' => ['required', 'in:global,category,operator,product'],
            'category' => ['nullable', 'string', 'max:40'],
            'operator_id' => ['nullable', 'integer', 'exists:ppob_operators,id'],
            'product_id' => ['nullable', 'integer', 'exists:ppob_products,id'],
            'margin_type' => ['sometimes', 'in:fixed,percentage'],
            'margin_value' => ['sometimes', 'numeric', 'min:0'],
            'admin_fee_type' => ['sometimes', 'in:fixed,percentage'],
            'admin_fee_value' => ['sometimes', 'numeric', 'min:0'],
            'commission_type' => ['sometimes', 'in:fixed,percentage'],
            'commission_value' => ['sometimes', 'numeric', 'min:0'],
            'min_selling_price' => ['nullable', 'numeric', 'min:0'],
            'max_selling_price' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['sometimes', 'integer'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if ($partial) {
            foreach ($rules as $key => $_) {
                if (! $request->has($key)) {
                    unset($rules[$key]);
                }
            }

            return $request->validate($rules);
        }

        $validated = $request->validate($rules);

        // default values on create
        $validated['margin_type'] ??= 'fixed';
        $validated['margin_value'] ??= 0;
        $validated['admin_fee_type'] ??= 'fixed';
        $validated['admin_fee_value'] ??= 0;
        $validated['commission_type'] ??= 'fixed';
        $validated['commission_value'] ??= 0;
        $validated['priority'] ??= 0;

        return $validated;
    }
}
