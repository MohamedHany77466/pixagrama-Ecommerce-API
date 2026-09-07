<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AttributeController;
use App\Http\Controllers\Api\AttributeValueController;
use App\Http\Controllers\Api\ProductVariationController;
use App\Http\Controllers\Api\AdminOrderController;

use App\Http\Controllers\Api\Auth\AdminAuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;


/* Admin Authentication */

Route::prefix('admin')->group(function () {

// Authentication
   

    Route::post('/login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:auth');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword'])
        ->middleware('throttle:auth');

    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])
        ->middleware('throttle:auth');

    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])
        ->middleware('throttle:auth');


//   Protected Admin Routes
   
    Route::middleware([
        'auth:sanctum',
        'isAdmin',
    ])->group(function () {

    //Logout

        Route::post('/logout', [AdminAuthController::class, 'logout'])
            ->middleware('throttle:api');


      //Products
       

        Route::prefix('products')->group(function () {

            Route::get('/admin', [ProductController::class, 'adminIndex'])
                ->middleware('throttle:api');

            Route::get('/deleted', [ProductController::class, 'deletedProducts'])
                ->middleware('throttle:api');

            Route::post('/', [ProductController::class, 'store'])
                ->middleware([
                    'permission:create products',
                    'throttle:api',
                ]);

            Route::put('/{product}', [ProductController::class, 'update'])
                ->middleware([
                    'permission:edit products',
                    'throttle:api',
                ]);

            Route::delete('/{product}', [ProductController::class, 'destroy'])
                ->middleware([
                    'permission:delete products',
                    'throttle:critical',
                ]);

            Route::post('/{product}/restore', [ProductController::class, 'undoDelete'])
                ->middleware('throttle:api');

            Route::delete('/{product}/force', [ProductController::class, 'permanentDelete'])
                ->middleware('throttle:critical');
        });


    //  Attributes

        Route::prefix('attributes')->group(function () {

            Route::get('/', [AttributeController::class, 'index'])
                ->middleware('throttle:api');

            Route::get('/{attribute}', [AttributeController::class, 'show'])
                ->middleware('throttle:api');

            Route::post('/', [AttributeController::class, 'store'])
                ->middleware([
                    'permission:create products',
                    'throttle:api',
                ]);

            Route::put('/{attribute}', [AttributeController::class, 'update'])
                ->middleware([
                    'permission:edit products',
                    'throttle:api',
                ]);

            Route::delete('/{attribute}', [AttributeController::class, 'destroy'])
                ->middleware([
                    'permission:delete products',
                    'throttle:critical',
                ]);
        });


        // Attribute Values
        

        Route::prefix('attribute-values')->group(function () {

            Route::get('/', [AttributeValueController::class, 'index'])
                ->middleware('throttle:api');

            Route::get('/{attributeValue}', [AttributeValueController::class, 'show'])
                ->middleware('throttle:api');

            Route::post('/', [AttributeValueController::class, 'store'])
                ->middleware([
                    'permission:create products',
                    'throttle:api',
                ]);

            Route::put('/{attributeValue}', [AttributeValueController::class, 'update'])
                ->middleware([
                    'permission:edit products',
                    'throttle:api',
                ]);

            Route::delete('/{attributeValue}', [AttributeValueController::class, 'destroy'])
                ->middleware([
                    'permission:delete products',
                    'throttle:critical',
                ]);
        });


     // Product Variations
        
        Route::prefix('product-variations')->group(function () {

            Route::get('/', [ProductVariationController::class, 'index'])
                ->middleware('throttle:api');

            Route::get('/{productVariation}', [ProductVariationController::class, 'show'])
                ->middleware('throttle:api');

            Route::post('/', [ProductVariationController::class, 'store'])
                ->middleware([
                    'permission:create products',
                    'throttle:api',
                ]);

            Route::put('/{productVariation}', [ProductVariationController::class, 'update'])
                ->middleware([
                    'permission:edit products',
                    'throttle:api',
                ]);

            Route::delete('/{productVariation}', [ProductVariationController::class, 'destroy'])
                ->middleware([
                    'permission:delete products',
                    'throttle:critical',
                ]);
        });


     //Categories
       

        Route::prefix('categories')->group(function () {

            Route::post('/', [CategoryController::class, 'store'])
                ->middleware([
                    'permission:create categories',
                    'throttle:categories',
                ]);

            Route::put('/{category}', [CategoryController::class, 'update'])
                ->middleware([
                    'permission:edit categories',
                    'throttle:categories',
                ]);

            Route::delete('/{category}', [CategoryController::class, 'destroy'])
                ->middleware([
                    'permission:delete categories',
                    'throttle:critical',
                ]);
        });


     // Admin Orders
       
        Route::prefix('orders')
            ->middleware('throttle:admin-orders')
            ->group(function () {

                Route::get('/', [AdminOrderController::class, 'index']);

                Route::get('/{order}', [AdminOrderController::class, 'show']);

                Route::patch('/{order}/status', [AdminOrderController::class, 'updateStatus']);
            });
    });
});