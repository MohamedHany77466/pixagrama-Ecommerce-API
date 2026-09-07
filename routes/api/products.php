<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;


/*
|--------------------------------------------------------------------------
| Public Product Routes
|--------------------------------------------------------------------------
|
| These endpoints are available without authentication.
|
*/

Route::prefix('products')
    ->name('products.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Product Listing
        |--------------------------------------------------------------------------
        */

        Route::get('/', [ProductController::class, 'index'])
            ->middleware('throttle:api')
            ->name('index');


        /*
        |--------------------------------------------------------------------------
        | Product Filter
        |--------------------------------------------------------------------------
        */

        Route::get('/filter', [ProductController::class, 'filter'])
            ->middleware('throttle:api')
            ->name('filter');


        /*
        |--------------------------------------------------------------------------
        | Product Details
        |--------------------------------------------------------------------------
        */

        Route::get('/{product}', [ProductController::class, 'show'])
            ->middleware('throttle:api')
            ->name('show');
    });

