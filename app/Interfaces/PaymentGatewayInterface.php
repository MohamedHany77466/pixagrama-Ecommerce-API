<?php
namespace App\Interfaces;

use App\Models\Order;

interface PaymentGatewayInterface
{
    public function pay(Order $order): array;

    public function verify(array $data): bool;
}