<?php

namespace App\Providers;

use App\Models\Faculty;
use App\Observers\FacultyObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Faculty::observe(FacultyObserver::class);

        // Strict throttle for login: 5 attempts per minute per IP
        RateLimiter::for('api.login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Strict throttle for password reset: 3 requests per minute per IP
        RateLimiter::for('api.password', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // General API throttle: 60 requests per minute, keyed by user or IP
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(60)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });
    }
}
