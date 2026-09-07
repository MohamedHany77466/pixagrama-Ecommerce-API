<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use App\Factories\PaymentFactory;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentFactory::class);
    }

    public function boot(): void {}
}