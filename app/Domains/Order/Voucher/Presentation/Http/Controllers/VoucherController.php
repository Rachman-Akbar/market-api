<?php

declare(strict_types=1);

namespace App\Domains\Order\Voucher\Presentation\Http\Controllers;

use App\Domains\Order\Voucher\Application\DTOs\VoucherDTO;
use App\Domains\Order\Voucher\Application\UseCases\ManageVoucherUseCase;
use App\Domains\Order\Voucher\Presentation\Http\Requests\StoreVoucherRequest;
use App\Domains\Order\Voucher\Presentation\Http\Resources\VoucherResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class VoucherController extends Controller
{
    public function __construct(private ManageVoucherUseCase $useCase) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'active_now' => $request->boolean('active_now', true),
        ];

        if ($request->has('is_active')) {
            $filters['is_active'] = $request->boolean('is_active');
        }

        if ($request->filled('store_id')) {
            $filters['store_id'] = $request->integer('store_id');
        }

        return response()->json([
            'success' => true,
            'data' => VoucherResource::collection($this->useCase->listVouchers($filters)),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => new VoucherResource($this->useCase->showVoucher($id)),
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 404);
        }
    }

    public function store(StoreVoucherRequest $request): JsonResponse
    {
        try {
            $data = $this->prepareData($request);
            $voucher = $this->useCase->createVoucher(new VoucherDTO(...$data));

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil dibuat.',
                'data' => new VoucherResource($voucher),
            ], 201);
        } catch (Throwable $exception) {
            $this->deleteUploadedImage($request->attributes->get('uploaded_voucher_image'));

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function update(int $id, StoreVoucherRequest $request): JsonResponse
    {
        $oldImage = null;

        try {
            $currentVoucher = $this->useCase->showVoucher($id);
            $this->assertOwnership($request, $currentVoucher->store_id);
            $oldImage = $currentVoucher->image;
            $data = $this->prepareData($request, $currentVoucher->store_id);
            $voucher = $this->useCase->updateVoucher($id, new VoucherDTO(...$data));

            if ($data['image'] !== null && $oldImage && $oldImage !== $data['image']) {
                $this->deleteUploadedImage($oldImage);
            }

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil diperbarui.',
                'data' => new VoucherResource($voucher),
            ]);
        } catch (Throwable $exception) {
            $this->deleteUploadedImage($request->attributes->get('uploaded_voucher_image'));

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $voucher = $this->useCase->showVoucher($id);
            $this->assertOwnership($request, $voucher->store_id);
            $image = $voucher->image;
            $this->useCase->deleteVoucher($id);
            $this->deleteUploadedImage($image);

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil dihapus.',
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    private function prepareData(StoreVoucherRequest $request, mixed $currentStoreId = null): array
    {
        $data = $request->validated();
        $activeRole = strtolower((string) $request->attributes->get('active_role', ''));

        if ($activeRole === 'admin') {
            $data['store_id'] = $request->input('store_id');
        } elseif ($activeRole === 'seller') {
            $storeId = $request->user()?->store?->id;

            if (!$storeId) {
                throw new \RuntimeException('Akun seller belum terhubung dengan toko.');
            }

            if ($currentStoreId !== null && (int) $currentStoreId !== (int) $storeId) {
                throw new \RuntimeException('Anda tidak memiliki akses ke voucher ini.');
            }

            $data['store_id'] = $storeId;
        }

        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;
        $data['image'] = null;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('vouchers', 'public');
            $request->attributes->set('uploaded_voucher_image', $data['image']);
        }

        return $data;
    }

    private function assertOwnership(Request $request, mixed $storeId): void
    {
        $activeRole = strtolower((string) $request->attributes->get('active_role', ''));

        if ($activeRole !== 'seller') {
            return;
        }

        if ((int) $storeId !== (int) $request->user()?->store?->id) {
            throw new \RuntimeException('Anda tidak memiliki akses ke voucher ini.');
        }
    }

    private function deleteUploadedImage(?string $path): void
    {
        if ($path && !str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
