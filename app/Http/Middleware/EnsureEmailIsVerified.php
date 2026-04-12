<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if ($user === null || ! $user->is_email_verified) {
            return response()->json([
                'message' => 'Email verification required.',
            ], 403);
        }

        return $next($request);
    }
}
