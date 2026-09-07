<?php

namespace App\Factories;

use App\Enums\PaymentProvider;
use App\Services\PaymobPaymentService;

class PaymentFactory
{
    public function __construct(
        protected PaymobPaymentService $paymob,
    ) {}

    public function make(PaymentProvider $provider)
    {
        return match ($provider) {
            PaymentProvider::PAYMOB => $this->paymob,

            default => throw new \Exception("Unsupported payment provider"),
        };
    }
}