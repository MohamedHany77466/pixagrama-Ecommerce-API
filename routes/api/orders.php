<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CheckoutController;


/*
|--------------------------------------------------------------------------
| Customer Orders & Checkout Routes
|--------------------------------------------------------------------------
|
| All order and checkout endpoints require
| an authenticated customer.
|
*/

Route::middleware([
    'auth:sanctum',
    'isCustomer',
])
    ->name('orders.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Checkout
        |--------------------------------------------------------------------------
        */

        Route::post('/checkout', [CheckoutController::class, 'checkout'])
            ->middleware('throttle:checkout')
            ->name('checkout');


        /*
        |--------------------------------------------------------------------------
        | Order History
        |--------------------------------------------------------------------------
        */

        Route::get('/orders', [CheckoutController::class, 'orderHistory'])
            ->middleware('throttle:api')
            ->name('index');


        /*
        |--------------------------------------------------------------------------
        | Order Details
        |--------------------------------------------------------------------------
        */

        Route::get('/orders/{order}', [CheckoutController::class, 'orderDetails'])
            ->middleware('throttle:api')
            ->name('show');


        /*
        |--------------------------------------------------------------------------
        | Cancel Order
        |--------------------------------------------------------------------------
        */

        Route::post('/orders/{order}/cancel', [CheckoutController::class, 'cancelOrder'])
            ->middleware('throttle:api')
            ->name('cancel');


        /*
        |--------------------------------------------------------------------------
        | Order Timeline
        |--------------------------------------------------------------------------
        */

        Route::get('/orders/{order}/timeline', [CheckoutController::class, 'timeline'])
            ->middleware('throttle:api')
            ->name('timeline');
    });
