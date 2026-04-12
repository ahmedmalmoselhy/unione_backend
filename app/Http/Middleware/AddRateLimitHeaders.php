<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AddRateLimitHeaders
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request and add rate limit headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Get the rate limiter being used
        $limiterName = $this->resolveLimiter($request);

        if ($limiterName && $this->limiter->remaining($limiterName, $request)) {
            $headers = $this->limiter->getHeaders($limiterName, $request);

            // Add standard rate limit headers
            $response->headers->set('X-RateLimit-Limit', $headers['X-RateLimit-Limit'] ?? '60');
            $response->headers->set('X-RateLimit-Remaining', $headers['X-RateLimit-Remaining'] ?? '59');
            $response->headers->set('X-RateLimit-Reset', $headers['X-RateLimit-Reset'] ?? time() + 60);

            // Add custom headers for better API governance
            $response->headers->set('X-RateLimit-Period', '60');
            $response->headers->set('X-RateLimit-Period-Unit', 'seconds');

            // Add role-based rate limit info
            if ($request->user()) {
                $roles = $request->user()->roles->pluck('name')->join(', ');
                $response->headers->set('X-User-Roles', $roles);
            }
        }

        return $response;
    }

    /**
     * Resolve the rate limiter name from the request.
     */
    protected function resolveLimiter(Request $request): ?string
    {
        // Try to determine which limiter is being used based on route
        $route = $request->route();

        if (!$route) {
            return null;
        }

        // Check route middleware for rate limiter
        $middleware = $route->middleware() ?? [];

        foreach ($middleware as $mw) {
            if (Str::startsWith($mw, 'throttle:')) {
                $limiterName = Str::after($mw, 'throttle:');

                // Return the limiter name if it's a named limiter
                if (in_array($limiterName, ['api', 'api.login', 'api.password', 'api.enroll', 'api.grade'])) {
                    return $limiterName;
                }
            }
        }

        return 'api'; // Default to general API limiter
    }
}
