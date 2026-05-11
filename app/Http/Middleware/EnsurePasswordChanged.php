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
            !$request->routeIs([
                'getting-started.*',
            ])
        ) {
            return redirect()->route('getting-started.index');
        }

        if (
            $user &&
            !$user->must_change_password &&
            $request->routeIs('getting-started.*')
        ) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
