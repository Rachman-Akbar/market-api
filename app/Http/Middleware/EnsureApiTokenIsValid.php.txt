<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureApiTokenIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
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

        if (! $token->can('web')) {
            return response()->json([
                'message' => 'Invalid token scope.',
            ], 403);
        }

        return $next($request);
    }
}
