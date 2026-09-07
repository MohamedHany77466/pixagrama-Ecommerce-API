<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;


/*
|--------------------------------------------------------------------------
| Public Category Routes
|--------------------------------------------------------------------------
*/

Route::prefix('categories')
    ->name('categories.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Category List
        |--------------------------------------------------------------------------
        */

        Route::get('/', [CategoryController::class, 'index'])
            ->middleware('throttle:api')
            ->name('index');


        /*
        |--------------------------------------------------------------------------
        | Category Products
        |--------------------------------------------------------------------------
        */

        Route::get('/{category}/products', [CategoryController::class, 'products'])
            ->middleware('throttle:api')
            ->name('products');


        /*
        |--------------------------------------------------------------------------
        | Category Details
        |--------------------------------------------------------------------------
        */

        Route::get('/{category}', [CategoryController::class, 'show'])
            ->middleware('throttle:api')
            ->name('show');
    });