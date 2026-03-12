<?php

namespace App\Providers;

use App\Models\Faculty;
use App\Observers\FacultyObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Query\Builder;
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

        // Cross-database case-insensitive LIKE: uses native ilike on pgsql,
        // falls back to LOWER() LIKE on SQLite (used in tests).
        Builder::macro('whereIlike', function (string $column, string $value): static {
            if ($this->connection->getDriverName() === 'pgsql') {
                return $this->where($column, 'ilike', $value);
            }
            return $this->whereRaw('LOWER(' . $this->grammar->wrap($column) . ') LIKE ?', [strtolower($value)]);
        });

        Builder::macro('orWhereIlike', function (string $column, string $value): static {
            if ($this->connection->getDriverName() === 'pgsql') {
                return $this->orWhere($column, 'ilike', $value);
            }
            return $this->orWhereRaw('LOWER(' . $this->grammar->wrap($column) . ') LIKE ?', [strtolower($value)]);
        });

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

        // Enrollment mutations: 10 per minute per user to prevent flooding
        RateLimiter::for('api.enroll', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(10)->by('enroll|' . $request->user()->id)
                : Limit::perMinute(5)->by($request->ip());
        });

        // Grade submission: 30 per minute per professor
        RateLimiter::for('api.grade', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(30)->by('grade|' . $request->user()->id)
                : Limit::perMinute(5)->by($request->ip());
        });
    }
}
