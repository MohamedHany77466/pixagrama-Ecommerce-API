<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymobWebhookController extends Controller
{
    use ApiResponseTrait;

    public function handle(Request $request)
    {
        $data = $request->all();

        /*
        |--------------------------------------------------------------------------
        | Verify HMAC
        |--------------------------------------------------------------------------
        */
        if (! $this->verifyHmac($data)) {

            return response()->json([
                'success' => false,
                'message' => 'Invalid HMAC',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Order Id
        |--------------------------------------------------------------------------
        */
        $orderId = $data['merchant_order_id'] ?? null;

        if (! $orderId) {

            return response()->json([
                'success' => false,
                'message' => 'Missing order id',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Find Payment
        |--------------------------------------------------------------------------
        */
        $payment = Payment::where(
            'order_id',
            $orderId
        )->latest()->first();

        if (! $payment) {

            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Idempotency Check
        |--------------------------------------------------------------------------
        */
        if (
            $payment->status ===
            PaymentStatus::COMPLETED
        ) {

            return response()->json([
                'success' => true,
                'message' => 'Already processed',
            ]);
        }

        $success = filter_var(
            $data['success'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        DB::transaction(function () use (
            $payment,
            $data,
            $success
        ) {

            if ($success) {

                $payment->markAsCompleted(
                    $data['id'] ?? null,
                    [
                        'paymob_response' => $data,
                    ]
                );

            } else {

                $payment->markAsFailed([
                    'paymob_response' => $data,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Webhook processed',
        ]);
    }

    private function verifyHmac(array $data): bool
    {
        $hmac = $data['hmac'] ?? null;

        if (! $hmac) {
            return false;
        }

        unset($data['hmac']);

        $fields = [
            'amount_cents',
            'created_at',
            'currency',
            'order',
            'success',
        ];

        $string = '';

        foreach ($fields as $field) {
            $string .= $data[$field] ?? '';
        }

        $calculated = hash_hmac(
            'sha512',
            $string,
            config('services.paymob.hmac_secret')
        );

        return hash_equals(
            $hmac,
            $calculated
        );
    }
}