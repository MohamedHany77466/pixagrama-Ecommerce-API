<?php

/*  API Routes Main API route file. Route modules are separated by responsibility. */

require base_path('routes/api/admin.php');
require base_path('routes/api/customer.php');
require base_path('routes/api/products.php');
require base_path('routes/api/categories.php');
require base_path('routes/api/cart.php');
require base_path('routes/api/orders.php');
require base_path('routes/api/payments.php');



Route::get('/locale', function () {
    return [
        'locale' => app()->getLocale(),
    ];
});
