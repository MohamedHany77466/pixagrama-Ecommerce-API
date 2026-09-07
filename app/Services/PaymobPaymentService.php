<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class PaymobPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    /**
     * AUTH TOKEN (cached)
     */
    public function auth(): string
    {
        return Cache::remember('paymob_token', 50 * 60, function () {

            $response = $this->request('POST', '/auth/tokens', [
                'api_key' => config('services.paymob.api_key'),
            ]);

            if (!isset($response['token'])) {
                throw new \Exception('Paymob Auth Failed: ' . json_encode($response));
            }

            return $response['token'];
        });
    }

    /**
     * MAIN PAY FLOW
     */
    public function pay(Order $order): array
    {
        $amount = (int) $order->total * 100;

        $token = $this->auth();

        $paymobOrder = $this->createOrder($token, $amount, $order);

        $paymentKey = $this->paymentKey(
            $token,
            $paymobOrder['id'],
            $amount,
            $order
        );

        return [
            'checkout_url' => $this->checkoutUrl($paymentKey['token']),
            'transaction_id' => $paymobOrder['id'],
        ];
    }

    /**
     * CREATE ORDER
     */
    public function createOrder(string $token, int $amount, Order $order)
    {
        return $this->request('POST', '/ecommerce/orders', [
            'auth_token' => $token,
            'amount_cents' => $amount,
            'currency' => 'EGP',
            'delivery_needed' => false,
            'merchant_order_id' => $order->id,
            'items' => [],
        ]);
    }

    /**
     * PAYMENT KEY
     */
    public function paymentKey(string $token, int $orderId, int $amount, Order $order)
    {
        return $this->request('POST', '/acceptance/payment_keys', [
            'auth_token' => $token,
            'amount_cents' => $amount,
            'expiration' => 3600,
            'order_id' => $orderId,
            'currency' => 'EGP',

            'billing_data' => [
                "first_name" => $order->shipping_name,
                "last_name" => "Customer",
                "email" => $order->user->email ?? "test@test.com",
                "phone_number" => $order->shipping_phone,
                "apartment" => "NA",
                "floor" => "NA",
                "street" => $order->shipping_address,
                "building" => "NA",
                "city" => $order->shipping_city,
                "country" => "EG",
                "state" => $order->shipping_state ?? "NA",
            ],

            'integration_id' => config('services.paymob.integration_id'),
        ]);
    }

    /**
     * CHECKOUT URL
     */
    public function checkoutUrl(string $token): string
    {
        return "https://accept.paymob.com/api/acceptance/iframes/"
            . config('services.paymob.iframe_id')
            . "?payment_token=" . $token;
    }

    /**
     * VERIFY WEBHOOK
     */
    public function verify(array $data): bool
    {
        return isset($data['success']) && $data['success'] === true;
    }
}