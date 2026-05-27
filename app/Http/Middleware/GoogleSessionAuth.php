<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GoogleSessionAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            return $next($request);
        }

        if (session()->has('google_user')) {
            return $next($request);
        }

        return redirect('/login');
    }
}
