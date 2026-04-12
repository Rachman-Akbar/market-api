<?php

namespace App\Domains\Users\Application\Actions;

use App\Models\Address;
use Illuminate\Database\Eloquent\Collection;

final class ListAddressesAction
{
    /**
     * @return Collection<int, Address>
     */
    public function execute(string $userId): Collection
    {
        return Address::query()->where('user_id', $userId)->latest()->get();
    }
}
