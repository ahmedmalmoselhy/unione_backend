<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->must_change_password) {
            // Allow the change-password routes and logout through; block everything else
            if (! $request->routeIs('dashboard.password.change', 'dashboard.password.update', 'dashboard.logout')) {
                return redirect()->route('dashboard.password.change');
            }
        }

        return $next($request);
    }
}
