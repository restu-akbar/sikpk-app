<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user &&
            $user->must_change_password &&
            !$request->routeIs('satgas.getting-started.*')
        ) {
            return redirect()->route('satgas.getting-started.index');
        }

        if (
            $user &&
            !$user->must_change_password &&
            $request->routeIs('satgas.getting-started.*')
        ) {
            return redirect()->route('satgas.dashboard');
        }

        return $next($request);
    }
}
