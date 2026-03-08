<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows system admins, faculty admins, and department admins through.
 * Plain employees are blocked (they can only view show pages via the dashboard middleware).
 */
class ScopedAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->hasActiveRole(['admin', 'faculty_admin', 'department_admin'])) {
            abort(403);
        }

        return $next($request);
    }
}
