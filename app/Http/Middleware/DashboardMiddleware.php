<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DashboardMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('dashboard.login');
        }

        if (! Auth::user()->hasActiveRole(['admin', 'employee'])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('dashboard.login')
                ->withErrors(['email' => __('auth.dashboard_no_access')]);
        }

        return $next($request);
    }
}
