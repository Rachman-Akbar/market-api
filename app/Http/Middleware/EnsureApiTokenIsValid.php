<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureApiTokenIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->currentAccessToken() === null) {
            return response()->json([
                'message' => 'Valid API token required.',
            ], 401);
        }

        return $next($request);
    }
}
