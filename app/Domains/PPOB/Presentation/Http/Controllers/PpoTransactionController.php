<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Presentation\Http\Controllers;

use App\Domains\PPOB\Application\Services\IakProviderService;
use App\Domains\PPOB\Application\Services\PpoFinanceService;
use App\Domains\PPOB\Application\UseCases\PlacePpoOrderUseCase;
use App\Domains\PPOB\Domain\Repositories\PpoTransactionRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Models\PpoTransactionModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PpoTransactionController extends Controller
{
    public function __construct(
        private PlacePpoOrderUseCase $placeOrder,
        private PpoTransactionRepositoryInterface $transactions,
        private IakProviderService $provider,
        private PpoFinanceService $finance,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:ppob_products,id'],
            'customer_id' => ['required', 'string', 'max:40'],
        ]);

        $product = DB::table('ppob_products')->where('id', $validated['product_id'])->first();

        if (! $product || $product->product_type === 'postpaid') {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak mendukung pembelian langsung. Gunakan inquiry untuk tagihan.',
            ], 422);
        }

        try {
            $user = $request->user();
            $result = $this->placeOrder->execute(
                $user->id,
                (int) $validated['product_id'],
                $validated['customer_id'],
                $user->name ?? null,
                $user->email ?? null,
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }

        $tx = $result['transaction'];

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $this->transactionArray($tx, false, true),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:20'],
            'category' => ['nullable', 'string', 'max:40'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $paginator = $this->transactions->getByUser(
            $request->user()->id,
            $validated,
            (int) ($validated['per_page'] ?? 15),
        );

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tx = PpoTransactionModel::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['operator', 'product'])
            ->first();

        if (! $tx) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transactionArray($tx, true),
        ]);
    }

    public function checkStatus(Request $request, int $id): JsonResponse
    {
        $tx = PpoTransactionModel::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $tx) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        // Only re-check transactions that are still in-flight.
        if (! in_array($tx->status, ['pending', 'processing'], true)) {
            return response()->json([
                'success' => true,
                'data' => $this->terminalArray($tx),
            ]);
        }

        $result = $this->provider->checkStatus($tx->reference_id, $tx->id);

        if ($result['status'] === 'success') {
            DB::transaction(function () use ($tx, $result) {
                $tx->status = 'success';
                $tx->provider_status = $result['provider_status'];
                $tx->provider_message = $result['message'];
                $tx->tr_id = $result['tr_id'];
                $tx->sn = $result['sn'];
                $tx->pin = $result['pin'];
                $tx->provider_raw_response = $result['response'];
                $tx->completed_at = now();
                $tx->paid_at = now();
                $tx->save();

                $this->finance->postForSuccess($tx);
            });
        } elseif ($result['status'] === 'failed') {
            $tx->status = 'failed';
            $tx->provider_status = $result['provider_status'];
            $tx->provider_message = $result['message'];
            $tx->provider_raw_response = $result['response'];
            $tx->save();
        } else {
            $tx->status = 'processing';
            $tx->provider_status = $result['provider_status'];
            $tx->provider_message = $result['message'];
            $tx->save();
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $this->terminalArray($tx->fresh()),
        ]);
    }

    private function transactionArray(PpoTransactionModel $tx, bool $withBreakdown = false, bool $withPayment = false): array
    {
        $data = [
            'id' => $tx->id,
            'reference_id' => $tx->reference_id,
            'product_name' => $tx->product_name,
            'category' => $tx->category,
            'product_type' => $tx->product_type,
            'customer_id' => $tx->customer_id,
            'customer_name' => $tx->customer_name,
            'total_amount' => (float) $tx->total_amount,
            'status' => $tx->status,
            'payment_status' => $tx->payment_status ?? 'pending',
            'payment_method' => $tx->payment_method,
            'provider_message' => $tx->provider_message,
            'tr_id' => $tx->tr_id,
            'sn' => $tx->sn,
            'created_at' => $tx->created_at?->toDateTimeString(),
            'paid_at' => $tx->paid_at?->toDateTimeString(),
            'completed_at' => $tx->completed_at?->toDateTimeString(),
            'operator' => $tx->operator ? [
                'id' => $tx->operator->id,
                'name' => $tx->operator->name,
                'icon_url' => $tx->operator->icon_url,
            ] : null,
        ];

        if ($withPayment) {
            $data['snap_token'] = $tx->midtrans_snap_token;
            $data['midtrans_client_key'] = config('midtrans.client_key');
            $data['midtrans_is_production'] = (bool) config('midtrans.is_production');
        }

        return $data;
    }

    private function terminalArray(PpoTransactionModel $tx): array
    {
        return $this->transactionArray($tx);
    }
}
