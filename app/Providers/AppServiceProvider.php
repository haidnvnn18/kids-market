<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        RateLimiter::for('seller-login', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)
                ->by($email.'|'.$request->ip());
        });

        RateLimiter::for('seller-register', function (Request $request) {
            return Limit::perHour(10)->by($request->ip());
        });

        RateLimiter::for('seller-password-reset', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perHour(6)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('api-login', function (Request $request) {
            $username = Str::lower((string) $request->input('username'));

            return Limit::perMinute(10)->by($username.'|'.$request->ip());
        });

        RateLimiter::for('api-register', function (Request $request) {
            return Limit::perHour(15)->by($request->ip());
        });

        RateLimiter::for('api-social-login', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(20)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('api-check-username', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
