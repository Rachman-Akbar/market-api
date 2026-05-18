<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domains\Cart\Infrastructure\Persistence\Models\CartModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UserObserver
{
    public function deleting(User $user): void
    {
        DB::transaction(function () use ($user): void {
            /**
             * Hapus semua Sanctum token user.
             * Ini membuat semua browser/device langsung logout.
             */
            $user->tokens()->delete();

            /**
             * Soft-delete cart dan cart_items.
             * Order/payment tidak disentuh.
             */
            CartModel::query()
                ->where('user_id', $user->id)
                ->orWhere('active_user_id', $user->id)
                ->with('items')
                ->get()
                ->each(function (CartModel $cart): void {
                    $cart->items()->delete();
                    $cart->delete();
                });
        });
    }

    public function forceDeleted(User $user): void
    {
        /**
         * Safety kalau suatu saat user di-force-delete lewat Eloquent.
         */
        $user->tokens()->delete();
    }
}