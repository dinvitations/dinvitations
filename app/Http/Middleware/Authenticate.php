<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function handle($request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
