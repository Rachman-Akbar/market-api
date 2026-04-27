<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Infrastructure\Services;

use Closure;
use Illuminate\Support\Facades\DB;

final class OrderTransactionService
{
    public function run(Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}
