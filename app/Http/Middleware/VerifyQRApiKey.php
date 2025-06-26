<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyQRApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $apiKey = substr($authHeader, 7);

        if ($apiKey !== config('app.qr_api_key')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}