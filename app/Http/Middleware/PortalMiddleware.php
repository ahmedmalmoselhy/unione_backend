<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PortalMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('portal.login');
        }

        if (! Auth::user()->hasActiveRole(['student', 'professor', 'employee'])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('portal.login')
                ->withErrors(['email' => 'This account does not have portal access.']);
        }

        return $next($request);
    }
}
