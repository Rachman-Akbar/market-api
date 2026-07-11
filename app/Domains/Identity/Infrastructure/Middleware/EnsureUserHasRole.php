<?php

namespace App\Domains\Identity\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $allowedRoles = collect($roles)
            ->flatMap(fn(string $role) => explode(',', $role))
            ->map(fn(string $role) => strtolower(trim($role)))
            ->filter()
            ->unique()
            ->values();

        $token = $user->currentAccessToken();
        $activeRole = collect($token?->abilities ?? [])
            ->first(fn($ability) => is_string($ability) && str_starts_with($ability, 'active-role:'));
        $activeRole = is_string($activeRole) ? substr($activeRole, strlen('active-role:')) : null;

        $authorized = $allowedRoles->contains(
            fn(string $role) => $user->hasRole($role) && $activeRole === $role
        );

        if (!$authorized) {
            return response()->json(['message' => 'Insufficient active role access.'], 403);
        }

        $request->attributes->set('active_role', $activeRole);

        return $next($request);
    }
}
