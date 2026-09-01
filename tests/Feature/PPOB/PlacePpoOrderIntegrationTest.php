<?php

declare(strict_types=1);

namespace Tests\Feature\PPOB;

use App\Domains\PPOB\Application\Services\PpoFinanceService;
use App\Domains\PPOB\Application\Services\PricingEngine;
use App\Domains\PPOB\Application\UseCases\PlacePpoOrderUseCase;
use App\Domains\PPOB\Domain\Repositories\PpoOperatorRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoPricingRuleRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoProductRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoTransactionRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Support\InteractsAsUser;
use Tests\TestCase;

class PlacePpoOrderIntegrationTest extends TestCase
{
    use InteractsAsUser;
    use RefreshDatabase;

    public function test_full_ppob_order_flow_with_pricing_and_finance(): void
    {
        Http::fake([
            '*/top-up' => Http::response([
                'data' => [
                    'ref_id' => 'ignored',
                    'status' => '1',
                    'product_code' => 'X-TEST-1',
                    'customer_id' => '08123456789',
                    'message' => 'TRANSACTION SUCCESS',
                    'balance' => 1000,
                    'tr_id' => 'TR-INT-1',
                    'sn' => 'SN-INT-1',
                    'rc' => '00',
                ],
                'meta' => [],
            ], 200),
        ]);

        $testUser = $this->makeUser();

        $operatorRepo = app(PpoOperatorRepositoryInterface::class);
        $productRepo = app(PpoProductRepositoryInterface::class);
        $ruleRepo = app(PpoPricingRuleRepositoryInterface::class);
        $pricing = app(PricingEngine::class);
        $useCase = app(PlacePpoOrderUseCase::class);
        $finance = app(PpoFinanceService::class);

        $operator = $operatorRepo->create([
            'name' => 'Test Operator',
            'slug' => 'test-operator-int',
            'category' => 'pulsa',
            'brand' => 'Test',
            'operator_prefix' => '081',
        ]);

        $product = $productRepo->create([
            'operator_id' => $operator->id,
            'category' => 'pulsa',
            'product_type' => 'prepaid',
            'provider_product_code' => 'X-TEST-1',
            'name' => 'Pulsa Test 10K',
            'nominal' => '10.000',
            'provider_price' => 10000,
            'admin_fee' => 0,
            'commission' => 0,
            'margin' => 0,
            'selling_price' => 10000,
            'status' => 'active',
            'is_available' => true,
            'is_active' => true,
        ]);

        $rule = $ruleRepo->create([
            'level' => 'operator',
            'operator_id' => $operator->id,
            'margin_type' => 'percentage',
            'margin_value' => 3,
            'admin_fee_type' => 'fixed',
            'admin_fee_value' => 100,
            'commission_type' => 'fixed',
            'commission_value' => 200,
            'priority' => 0,
            'is_active' => true,
        ]);

        $priced = $pricing->priceProduct($productRepo->findById($product->id));
        $pp = $priced['product'];

        $expected = 10000 + 300 + 100;
        $this->assertEqualsWithDelta($expected, $pp->sellingPrice, 0.01, 'PricingEngine selling_price');

        $order = $useCase->execute((string) $testUser->id, $product->id, '08123456789');
        $tx = $order['transaction'] ?? $order;

        $this->assertEquals('success', $tx->status, 'Order status should be success.');
        $this->assertEqualsWithDelta(10400, (float) $tx->total_amount, 0.01, 'Order total_amount');

        $entries = $finance->entriesForReference($tx->reference_id);
        $this->assertGreaterThanOrEqual(5, count($entries), 'Finance ledger should have >= 5 entries.');

        // Idempotency: posting again must not duplicate.
        $finance->postForSuccess($tx);
        $entries2 = $finance->entriesForReference($tx->reference_id);
        $this->assertCount(count($entries), $entries2, 'Finance posting must be idempotent.');

        // No secret leaked into logs.
        $logs = DB::table('ppob_transaction_logs')->where('reference_id', $tx->reference_id)->get();
        $blob = $logs->map(fn ($log) => json_encode([$log->request_payload, $log->response_payload]))->implode('|');
        $this->assertStringNotContainsString('iaK_DEV_API', $blob, 'No API key in logs.');
        $this->assertStringNotContainsString('dev_key', $blob, 'No dev key in logs.');

        $byRef = app(PpoTransactionRepositoryInterface::class)->findByReferenceId($tx->reference_id);
        $this->assertNotNull($byRef);
        $this->assertSame($tx->id, $byRef->id);
    }
}
