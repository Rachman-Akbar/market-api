<?php
    
    namespace App\Domains\Identity\Application\Actions;

    use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
    use App\Models\User;

    final class RegisterUserAction
    {
        public function __construct(private readonly UserRepository $users) {}

        /**
         * @param array<string, mixed> $claims
         */
        public function execute(array $claims): User
        {
            $user = $this->users->createFromFirebaseClaims($claims);
            $this->users->assignRoleByName($user, 'buyer');

            return $user->refresh();
        }
    }
