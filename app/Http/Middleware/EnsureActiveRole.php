<?php

namespace App\Http\Middleware;

use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureActiveRole
{
    public function __construct(
        private readonly UserRepository $users,
    ) {}

    public function handle(Request $request, Closure $next, string $role): Response
    {
        $role = strtolower(trim($role));

        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $token = $user->currentAccessToken();

        if ($token === null) {
            return response()->json([
                'message' => 'Missing access token.',
            ], 401);
        }

        if (! $this->users->hasRole($user, $role)) {
            return response()->json([
                'message' => 'Role does not belong to current user.',
            ], 403);
        }

        if (! $token->can("active-role:{$role}")) {
            return response()->json([
                'message' => "Active role must be {$role}.",
            ], 403);
        }

        if ($role === 'seller' && ! $this->users->hasSellerAccess($user)) {
            return response()->json([
                'message' => 'Seller access is not active.',
            ], 403);
        }

        $request->attributes->set('active_role', $role);

        return $next($request);
    }
}
