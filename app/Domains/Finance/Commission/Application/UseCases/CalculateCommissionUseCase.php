<?php

declare(strict_types=1);

namespace App\Domains\Finance\Commission\Application\UseCases;

use App\Domains\Finance\Commission\Application\Services\AdminFeeConfigService;
use App\Domains\Finance\Commission\Application\Services\SellerSettlementService;
use App\Domains\Finance\Commission\Domain\Entities\SellerSettlement;
use App\Domains\Order\Ordering\Domain\Entities\Order;
use Illuminate\Support\Facades\DB;

class CalculateCommissionUseCase
{
    public function __construct(
        private AdminFeeConfigService $feeConfigService,
        private SellerSettlementService $settlementService
    ) {}

    public function execute(Order $order, int $storeId, float $subOrderAmount, float $shippingCost): SellerSettlement
    {
        $adminFee = $this->feeConfigService->calculateFee($subOrderAmount);
        $netAmount = $subOrderAmount - $adminFee;

        $settlement = $this->settlementService->createSettlement([
            'store_id' => $storeId,
            'order_id' => $order->id,
            'settlement_number' => $this->generateSettlementNumber(),
            'gross_amount' => $subOrderAmount,
            'admin_fee' => $adminFee,
            'shipping_fee' => $shippingCost,
            'net_amount' => $netAmount,
            'status' => 'pending',
            'metadata' => [
                'order_number' => $order->orderNumber,
                'order_type' => $order->orderType,
            ],
        ]);

        return $settlement;
    }

    private function generateSettlementNumber(): string
    {
        return 'STL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -8));
    }
}
