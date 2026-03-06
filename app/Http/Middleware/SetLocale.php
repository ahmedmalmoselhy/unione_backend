<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $header = $request->header('Accept-Language', 'en');

        // Extract the primary language tag (e.g. "ar" from "ar-SA,ar;q=0.9,en;q=0.8")
        $primary = strtolower(explode(',', explode('-', $header)[0])[0]);

        return in_array($primary, self::SUPPORTED, true) ? $primary : 'en';
    }
}
