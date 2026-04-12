<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MobileApiOptimizer
{
    /**
     * Handle an incoming request and optimize for mobile clients.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Detect mobile client via header or user agent
        $isMobile = $request->header('X-Mobile-Client') === 'true'
            || $this->isMobileUserAgent($request);

        if ($isMobile) {
            // Add mobile optimization headers
            $response->headers->set('X-Mobile-Optimized', 'true');
            $response->headers->set('Cache-Control', 'max-age=300, public'); // 5 min cache
            $response->headers->set('Vary', 'Accept-Encoding');

            // Enable compression for mobile
            $response->headers->set('Content-Encoding', 'gzip');
        }

        return $response;
    }

    /**
     * Check if request is from a mobile user agent.
     */
    protected function isMobileUserAgent(Request $request): bool
    {
        $userAgent = $request->header('User-Agent', '');

        $mobileAgents = [
            'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry',
            'Windows Phone', 'Opera Mini', 'Mobile',
        ];

        foreach ($mobileAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                return true;
            }
        }

        return false;
    }
}
