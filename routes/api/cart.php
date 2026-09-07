<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;


/*
|--------------------------------------------------------------------------
| Customer Cart Routes
|--------------------------------------------------------------------------
|
| All cart endpoints require an authenticated customer.
|
*/

Route::middleware([
    'auth:sanctum',
    'isCustomer',
])
    ->prefix('cart')
    ->name('cart.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Get Cart
        |--------------------------------------------------------------------------
        */

        Route::get('/', [CartController::class, 'index'])
            ->middleware('throttle:api')
            ->name('index');


        /*
        |--------------------------------------------------------------------------
        | Add Product To Cart
        |--------------------------------------------------------------------------
        */

        Route::post('/', [CartController::class, 'store'])
            ->middleware('throttle:api')
            ->name('store');


        /*
        |--------------------------------------------------------------------------
        | Update Cart Item
        |--------------------------------------------------------------------------
        */

        Route::put('/{cart}', [CartController::class, 'update'])
            ->middleware('throttle:api')
            ->name('update');


        /*
        |--------------------------------------------------------------------------
        | Clear Cart
        |--------------------------------------------------------------------------
        |
        | Keep this route BEFORE /{cart}.
        |
        */

        Route::delete('/clear/all', [CartController::class, 'clear'])
            ->middleware('throttle:critical')
            ->name('clear');


        /*
        |--------------------------------------------------------------------------
        | Apply Coupon
        |--------------------------------------------------------------------------
        */

        Route::post('/coupon/apply', [CartController::class, 'applyCoupon'])
            ->middleware('throttle:api')
            ->name('coupon.apply');


        /*
        |--------------------------------------------------------------------------
        | Remove Cart Item
        |--------------------------------------------------------------------------
        */

        Route::delete('/{cart}', [CartController::class, 'destroy'])
            ->middleware('throttle:critical')
            ->name('destroy');
    });

