<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | API
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('api', function (Request $request) {

            return Limit::perMinute(60)
                ->by(
                    $request->user()?->id
                    ?? $request->ip()
                );
        });

        /*
        |--------------------------------------------------------------------------
        | AUTH
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('auth', function (Request $request) {

            return Limit::perMinute(5)
                ->by($request->ip());
        });

        /*
        |--------------------------------------------------------------------------
        | CATEGORIES
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('categories', function (Request $request) {

            return Limit::perMinute(30)
                ->by(
                    $request->user()?->id
                    ?? $request->ip()
                );
        });

        /*
        |--------------------------------------------------------------------------
        | CHECKOUT
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('checkout', function (Request $request) {

            return Limit::perMinute(5)
                ->by(
                    $request->user()?->id
                    ?? $request->ip()
                );
        });

        /*
        |--------------------------------------------------------------------------
        | PAYMENTS
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('payments', function (Request $request) {

            return Limit::perMinute(10)
                ->by(
                    $request->user()?->id
                    ?? $request->ip()
                );
        });

        /*
        |--------------------------------------------------------------------------
        | ADMIN ORDERS
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('admin-orders', function (Request $request) {

            return Limit::perMinute(60)
                ->by(
                    $request->user()?->id
                    ?? $request->ip()
                );
        });

        /*
        |--------------------------------------------------------------------------
        | CRITICAL ACTIONS
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('critical', function (Request $request) {

            return Limit::perMinute(10)
                ->by(
                    $request->user()?->id
                    ?? $request->ip()
                );
        });
    }
}