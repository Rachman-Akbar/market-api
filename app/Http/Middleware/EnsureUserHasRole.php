<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if ($user === null || ! $user->roles()->where('name', $role)->exists()) {
            return response()->json([
                'message' => 'Insufficient role access.',
            ], 403);
        }

        return $next($request);
    }
}
