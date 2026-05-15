<?php

namespace App\Domains\Seller\Application\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ResolveCurrentSellerStoreAction
{
    public function execute(User $user): object
    {
        $store = DB::table('stores')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($store === null) {
            abort(403, 'Seller store not found.');
        }

        return $store;
    }
}
