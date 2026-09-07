<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymobWebhookController;


/*
|--------------------------------------------------------------------------
| Customer Payment Routes
|--------------------------------------------------------------------------
|
| Payment creation requires an authenticated customer.
|
*/


/*
|--------------------------------------------------------------------------
| Create Payment
|--------------------------------------------------------------------------
*/

Route::post(
    '/orders/{order}/payments',
    [PaymentController::class, 'createPayment']
)
    ->middleware([
        'auth:sanctum',
        'isCustomer',
        'throttle:payments',
    ])
    ->name('payments.create');


/*
|--------------------------------------------------------------------------
| Paymob Webhook
|--------------------------------------------------------------------------
|
| Paymob calls this endpoint directly.
| Do NOT add auth:sanctum or isCustomer here.
|
*/

Route::post(
    '/payments/paymob/webhook',
    [PaymobWebhookController::class, 'handle']
)
    ->middleware('throttle:payments')
    ->name('payments.paymob.webhook');

