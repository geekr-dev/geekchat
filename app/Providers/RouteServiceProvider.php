<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('chat', function (Request $request) {
            return $request->input('api_key')
                ? Limit::none()
                : Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('audio', function (Request $request) {
            return $request->input('api_key')
                ? Limit::none()
                : Limit::perMinute(15)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('image', function (Request $request) {
            return $request->input('api_key')
                ? Limit::none()
                : Limit::perHour(5)->by($request->user()?->id ?: $request->ip());
        });
    }
}
