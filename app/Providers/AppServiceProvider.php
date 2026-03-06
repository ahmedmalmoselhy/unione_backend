<?php

namespace App\Providers;

use App\Models\Faculty;
use App\Observers\FacultyObserver;
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
    }
}
