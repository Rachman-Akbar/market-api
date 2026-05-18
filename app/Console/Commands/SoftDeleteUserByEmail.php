<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

final class SoftDeleteUserByEmail extends Command
{
    protected $signature = 'user:soft-delete {email}';

    protected $description = 'Soft delete a user by email and cleanup their tokens and cart.';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user instanceof User) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        $user->delete();

        $this->info('User soft deleted. Tokens and carts were cleaned up.');

        return self::SUCCESS;
    }
}