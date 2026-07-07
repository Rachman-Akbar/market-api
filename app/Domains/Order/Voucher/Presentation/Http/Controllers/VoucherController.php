<?php

namespace App\Domains\Order\Voucher\Presentation\Http\Controllers;

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
            $user = $request->user();
            $data = $request->validated();

            // 🛑 PRIORITAS 1: Cek Admin Terlebih Dahulu
            if ($user->hasRole('admin')) {
                // Admin bebas mengisi store_id apa saja, atau null untuk global
                $data['store_id'] = $request->input('store_id', null);
            }
            // 🛍️ PRIORITAS 2: Cek Seller
            elseif ($user->hasRole('seller')) {
                // Seller dipaksa menggunakan store_id dari tokonya
                $data['store_id'] = $user->store?->id;

                if (!$data['store_id']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akun seller Anda belum terhubung dengan toko mana pun.'
                    ], 400);
                }
            }

            $dto = new VoucherDTO(...$data);
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
            $user = $request->user();
            $data = $request->validated();

            // 🛑 PRIORITAS 1: Jika Admin, Bypass Semua Proteksi Kepemilikan Toko
            if ($user->hasRole('admin')) {
                $data['store_id'] = $request->input('store_id', null);
            }
            // 🛍️ PRIORITAS 2: Jika BUKAN Admin tapi Seller, Lakukan Validasi Ketat
            elseif ($user->hasRole('seller')) {
                $sellerStoreId = $user->store?->id;
                $currentVoucher = $this->useCase->showVoucher($id);

                // Pastikan seller tidak mengedit voucher milik toko lain / global
                if ((int) $currentVoucher->store_id !== (int) $sellerStoreId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki hak akses untuk mengubah voucher ini.'
                    ], 403);
                }

                $data['store_id'] = $sellerStoreId;
            }

            $dto = new VoucherDTO(...$data);
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
            // Menggunakan request() helper untuk menarik data user yang login
            $user = request()->user();

            // 🛡️ PROTEKSI: Seller tidak boleh menghapus voucher milik toko lain / global
            if ($user->hasRole('seller')) {
                $currentVoucher = $this->useCase->showVoucher($id);
                if ($currentVoucher->store_id !== $user->store_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki hak akses untuk menghapus voucher ini.'
                    ], 403);
                }
            }

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
