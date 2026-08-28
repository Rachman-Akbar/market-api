<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Repositories;

use App\Domains\PPOB\Domain\Entities\PpoPricingRule;
use App\Domains\PPOB\Domain\Repositories\PpoPricingRuleRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoPricingRuleModel;

class EloquentPpoPricingRuleRepository implements PpoPricingRuleRepositoryInterface
{
    public function findById(int $id): ?PpoPricingRule
    {
        $model = PpoPricingRuleModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    /**
     * Resolve the most specific active rule for the given product/operator/category.
     * Order of specificity: product > operator > category > global.
     */
    public function findMostSpecific(
        ?int $productId,
        ?int $operatorId,
        ?string $category
    ): ?PpoPricingRule {
        $active = PpoPricingRuleModel::where('is_active', true)->get();

        $candidates = $active->filter(function ($rule) use ($productId, $operatorId, $category) {
            if ($rule->product_id !== null) {
                return $rule->product_id === $productId;
            }

            if ($rule->operator_id !== null) {
                return $rule->operator_id === $operatorId;
            }

            if ($rule->category !== null) {
                return $rule->category === $category;
            }

            return $rule->level === 'global';
        });

        // Score: product=300, operator+category=200, category=150, operator=120, global=0
        $scored = $candidates->map(function ($rule) {
            $score = 0;
            if ($rule->product_id !== null) {
                $score += 300;
            }
            if ($rule->operator_id !== null) {
                $score += 100;
            }
            if ($rule->category !== null) {
                $score += 50;
            }
            if ($rule->level === 'global') {
                $score += 0;
            }

            return ['rule' => $rule, 'score' => $score];
        });

        if ($scored->isEmpty()) {
            return null;
        }

        $best = $scored->sortByDesc('score')->first();

        return $this->toEntity($best['rule']);
    }

    public function getActive(): array
    {
        return PpoPricingRuleModel::where('is_active', true)
            ->orderBy('level')
            ->orderBy('priority', 'desc')
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->all();
    }

    public function create(array $data): PpoPricingRule
    {
        $model = PpoPricingRuleModel::create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): PpoPricingRule
    {
        $model = PpoPricingRuleModel::findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        PpoPricingRuleModel::findOrFail($id)->delete();
    }

    private function toEntity(PpoPricingRuleModel $model): PpoPricingRule
    {
        return new PpoPricingRule(
            id: $model->id,
            level: $model->level,
            category: $model->category,
            operatorId: $model->operator_id,
            productId: $model->product_id,
            marginType: $model->margin_type,
            marginValue: (float) $model->margin_value,
            adminFeeType: $model->admin_fee_type,
            adminFeeValue: (float) $model->admin_fee_value,
            commissionType: $model->commission_type,
            commissionValue: (float) $model->commission_value,
            minSellingPrice: $model->min_selling_price !== null ? (float) $model->min_selling_price : null,
            maxSellingPrice: $model->max_selling_price !== null ? (float) $model->max_selling_price : null,
            priority: (int) $model->priority,
            isActive: (bool) $model->is_active,
            description: $model->description,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString(),
        );
    }
}
