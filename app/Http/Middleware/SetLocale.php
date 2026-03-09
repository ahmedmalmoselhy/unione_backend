<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->is('api/*')
            ? $this->resolveApiLocale($request)
            : $this->resolveWebLocale($request);

        app()->setLocale($locale);

        return $next($request);
    }

    /**
     * For web (dashboard): session-stored preference wins, then Accept-Language.
     */
    private function resolveWebLocale(Request $request): string
    {
        if ($sessionLocale = session('locale')) {
            if (in_array($sessionLocale, self::SUPPORTED, true)) {
                return $sessionLocale;
            }
        }

        return $this->fromHeader($request);
    }

    /**
     * For API: explicit X-Locale header wins, then ?locale= query param, then Accept-Language.
     */
    private function resolveApiLocale(Request $request): string
    {
        if ($explicit = $request->header('X-Locale')) {
            $explicit = strtolower(trim($explicit));
            if (in_array($explicit, self::SUPPORTED, true)) {
                return $explicit;
            }
        }

        if ($param = $request->query('locale')) {
            $param = strtolower(trim($param));
            if (in_array($param, self::SUPPORTED, true)) {
                return $param;
            }
        }

        return $this->fromHeader($request);
    }

    /**
     * Parse the primary language tag from the Accept-Language header.
     * e.g. "ar-SA,ar;q=0.9,en;q=0.8" → "ar"
     */
    private function fromHeader(Request $request): string
    {
        $header  = $request->header('Accept-Language', 'en');
        $primary = strtolower(explode(',', explode('-', $header)[0])[0]);

        return in_array($primary, self::SUPPORTED, true) ? $primary : 'en';
    }
}
