<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Presentation\Http\Controllers;

use App\Domains\PPOB\Application\Services\IakProviderService;
use App\Domains\PPOB\Application\Services\PpoFinanceService;
use App\Domains\PPOB\Application\Services\PricingEngine;
use App\Domains\PPOB\Domain\Repositories\PpoInquiryRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoProductRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoInquiryModel;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Postpaid bill flow: inquiry -> user confirms -> pay.
 * The tr_id from IAK is held server-side and never trusted from the client.
 */
class PpoBillController extends Controller
{
    public function __construct(
        private IakProviderService $provider,
        private PpoInquiryRepositoryInterface $inquires,
        private PpoProductRepositoryInterface $products,
        private PricingEngine $pricing,
        private PpoFinanceService $finance,
    ) {}

    public function inquiry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_code' => ['required', 'string', 'max:80', 'exists:ppob_products,provider_product_code'],
            'customer_id' => ['required', 'string', 'max:40'],
        ]);

        $product = $this->products->findByProviderCode($validated['product_code']);

        if (! $product || $product->productType !== 'postpaid') {
            return response()->json([
                'success' => false,
                'message' => 'Produk bukan tagihan (postpaid).',
            ], 422);
        }

        $referenceId = $this->generateReferenceId();

        $result = $this->provider->inquiryBill(
            $referenceId,
            $product->providerProductCode,
            $validated['customer_id'],
        );

        if (! $result['success'] || ! $result['tr_id']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal melakukan inquiry tagihan.',
            ], 422);
        }

        // Price the admin fee/commission using the bill amount as the base.
        $priced = $this->pricing->priceProduct($product);
        $pricedProduct = $priced['product'];

        $billingAmount = $result['bill_amount'] ?? 0.0;
        $adminCharge = $result['admin_charge'] ?? 0.0;
        $totalAmount = round($billingAmount + $adminCharge, 2);

        $inquiry = PpoInquiryModel::create([
            'reference_id' => $referenceId,
            'user_id' => $request->user()->id,
            'operator_id' => $product->operatorId,
            'product_code' => $product->providerProductCode,
            'category' => $product->category,
            'customer_id' => $validated['customer_id'],
            'tr_id' => $result['tr_id'],
            'customer_name' => $result['customer_name'],
            'customer_no' => $result['customer_no'],
            'bill_amount' => $billingAmount,
            'admin_charge' => $adminCharge,
            'admin_charge_message' => $result['admin_charge_message'],
            'detail' => $result['detail'],
            'status' => 'active',
            'expires_at' => now()->addMinutes((int) config('ppob.inquiry_ttl_minutes', 30)),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inquiry berhasil. Silakan konfirmasi pembayaran.',
            'data' => [
                'reference_id' => $referenceId,
                'customer_id' => $validated['customer_id'],
                'customer_name' => $result['customer_name'],
                'product_name' => $product->name,
                'category' => $product->category,
                'bill_amount' => $billingAmount,
                'admin_charge' => $adminCharge,
                'total_amount' => $totalAmount,
                'detail' => $result['detail'],
                'expires_at' => $inquiry->expires_at?->toDateTimeString(),
            ],
        ]);
    }

    public function pay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference_id' => ['required', 'string', 'max:100', 'exists:ppob_inquiries,reference_id'],
        ]);

        $inquiry = PpoInquiryModel::where('reference_id', $validated['reference_id'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if (! $inquiry) {
            return response()->json([
                'success' => false,
                'message' => 'Inquiry tidak ditemukan atau sudah kedaluwarsa. Lakukan inquiry ulang.',
            ], 422);
        }

        $product = $this->products->findByProviderCode($inquiry->product_code);

        $result = $this->provider->payBill($inquiry->tr_id, $inquiry->reference_id);

        if ($result['status'] === 'failed') {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Pembayaran tagihan gagal.',
            ], 422);
        }

        $tx = DB::transaction(function () use ($inquiry, $product, $result) {
            $tx = PpoTransactionModel::create([
                'reference_id' => $inquiry->reference_id . '-PAY',
                'user_id' => $inquiry->user_id,
                'operator_id' => $inquiry->operator_id,
                'product_id' => $product?->id,
                'provider_product_code' => $inquiry->product_code,
                'product_name' => $product?->name,
                'category' => $inquiry->category,
                'product_type' => 'postpaid',
                'customer_id' => $inquiry->customer_id,
                'customer_name' => $inquiry->customer_name,
                'bill_amount' => (float) $inquiry->bill_amount,
                'provider_price' => (float) $inquiry->bill_amount,
                'admin_fee' => (float) ($inquiry->admin_charge ?: 0),
                'commission' => 0,
                'margin' => (float) ($inquiry->admin_charge ?: 0),
                'revenue' => (float) ($inquiry->bill_amount + ($inquiry->admin_charge ?: 0)),
                'net_profit' => (float) ($inquiry->admin_charge ?: 0),
                'total_amount' => (float) ($inquiry->bill_amount + ($inquiry->admin_charge ?: 0)),
                'status' => $result['status'] === 'success' ? 'success' : 'processing',
                'tr_id' => $inquiry->tr_id,
                'provider_status' => $result['provider_status'],
                'provider_message' => $result['message'],
                'provider_raw_response' => $result['response'],
                'completed_at' => $result['status'] === 'success' ? now() : null,
                'paid_at' => $result['status'] === 'success' ? now() : null,
            ]);

            if ($result['status'] === 'success') {
                $this->finance->postForSuccess($tx);
            }

            $inquiry->status = 'paid';
            $inquiry->save();

            return $tx;
        });

        return response()->json([
            'success' => true,
            'message' => $result['status'] === 'success'
                ? 'Pembayaran tagihan berhasil.'
                : 'Pembayaran diproses, mohon menunggu konfirmasi.',
            'data' => [
                'reference_id' => $tx->reference_id,
                'tr_id' => $tx->tr_id,
                'status' => $tx->status,
                'sn' => $tx->sn,
            ],
        ]);
    }

    private function generateReferenceId(): string
    {
        $prefix = (string) config('ppob.reference_prefix', 'PPOB');

        return $prefix . 'B' . date('ymdHis') . strtoupper(Str::random(6));
    }
}
