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
                'getting-started',
                'change-password.update',
            ])
        ) {
            return redirect()->route('getting-started');
        }

        return $next($request);
    }
}
