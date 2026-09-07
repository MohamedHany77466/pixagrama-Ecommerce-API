<?php

namespace App\Http\Controllers\Api;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Factories\PaymentFactory;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use ApiResponseTrait;

    public function createPayment(
        PaymentRequest $request,
        Order $order,
        PaymentFactory $factory
    ) {

        if ($order->user_id !== $request->user()->id) {

            return $this->errorResponse(
                'Unauthorized',
                403
            );
        }

        if (! $order->canAcceptPayment()) {

            return $this->errorResponse(
                'Order not payable',
                422
            );
        }

        $existingPayment = $order->payments()
            ->where('status', PaymentStatus::PENDING)
            ->first();

        if ($existingPayment) {

            return $this->errorResponse(
                'Payment already in progress',
                409
            );
        }

        try {

            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,

                'provider' => $request->provider,

                'amount' => $order->total,

                'currency' => 'EGP',

                'status' => PaymentStatus::PENDING,

                'metadata' => [
                    'order_number' => $order->order_number,
                ],
            ]);

            $gateway = $factory->make(
    PaymentProvider::from(
        $request->provider
    )
);

            $result = $gateway->pay($order);

            $payment->update([
                'transaction_id' =>
                    $result['transaction_id'],

                'metadata' => array_merge(
                    $payment->metadata ?? [],
                    [
                        'checkout_url' =>
                            $result['checkout_url'],
                    ]
                ),
            ]);

            return $this->successResponse([
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'checkout_url' =>
                    $result['checkout_url'],
            ], 'Payment created successfully');

        } catch (\Throwable $e) {

            Log::error('Paymob Error', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }
}