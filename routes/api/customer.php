<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\CustomerAuthController;


/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
*/

Route::prefix('customer')
    ->name('customer.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        Route::post('/register', [CustomerAuthController::class, 'register'])
            ->middleware('throttle:auth')
            ->name('register');

        Route::post('/login', [CustomerAuthController::class, 'login'])
            ->middleware('throttle:auth')
            ->name('login');


        /*
        |--------------------------------------------------------------------------
        | Protected Customer Routes
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'auth:sanctum',
            'isCustomer',
        ])->group(function () {

            Route::post('/logout', [CustomerAuthController::class, 'logout'])
                ->middleware('throttle:api')
                ->name('logout');
        });
    });

