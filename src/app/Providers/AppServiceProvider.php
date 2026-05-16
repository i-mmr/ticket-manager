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
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(self::emailAndIpKey($request));
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by(self::emailAndIpKey($request));
        });

        RateLimiter::for('register-complete', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }

    private static function emailAndIpKey(Request $request): string
    {
        return Str::lower((string) $request->input('email')).'|'.$request->ip();
    }
}
