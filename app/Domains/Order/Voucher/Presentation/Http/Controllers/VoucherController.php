<?php

namespace App\Domains\Order\Voucher\Presentation\Http\Controllers; // <-- Pastikan ini tepat sama!

use App\Http\Controllers\Controller;
use App\Domains\Order\Voucher\Application\UseCases\ManageVoucherUseCase;
use App\Domains\Order\Voucher\Application\DTOs\VoucherDTO;
use App\Domains\Order\Voucher\Presentation\Http\Requests\StoreVoucherRequest;
use App\Domains\Order\Voucher\Presentation\Http\Resources\VoucherResource;
use Illuminate\Http\JsonResponse;

class VoucherController extends Controller
{
    public function __construct(private ManageVoucherUseCase $useCase) {}

    public function index(): JsonResponse
    {
        $vouchers = $this->useCase->listVouchers();
        return response()->json([
            'success' => true,
            'data'    => VoucherResource::collection($vouchers)
        ]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $voucher = $this->useCase->showVoucher($id);
            return response()->json([
                'success' => true,
                'data'    => new VoucherResource($voucher)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function store(StoreVoucherRequest $request): JsonResponse
    {
        try {
            $dto = new VoucherDTO(...$request->validated());
            $voucher = $this->useCase->createVoucher($dto);

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil dibuat.',
                'data'    => new VoucherResource($voucher)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function update(int $id, StoreVoucherRequest $request): JsonResponse
    {
        try {
            $dto = new VoucherDTO(...$request->validated());
            $voucher = $this->useCase->updateVoucher($id, $dto);

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil diperbarui.',
                'data'    => new VoucherResource($voucher)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->useCase->deleteVoucher($id);
            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
