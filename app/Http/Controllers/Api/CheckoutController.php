<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    use ApiResponseTrait;

    public function checkout(CheckoutRequest $request)
    {
        $user = $request->user();

        $data = $request->validated();

       $cartItems = Cart::forUser($user->id)
    ->with([
        'product',
        'variation',
    ])
    ->get();

        if ($cartItems->isEmpty()) {

            return $this->errorResponse(
                'Cart is empty',
                422
            );
        }

        try {

            $order = DB::transaction(function () use (
                $user,
                $data,
                $cartItems
            ) {

                $subtotal = 0;

                /*
                |--------------------------------------------------------------------------
                | Validate Stock
                |--------------------------------------------------------------------------
                */
                foreach ($cartItems as $item) {

                    if (! $item->product) {

                        throw new \Exception(
                            'Product not found'
                        );
                    }

                    if (! $item->product->is_active) {

                        throw new \Exception(
                            "{$item->product->name} is unavailable"
                        );
                    }

                    // if ($item->quantity > $item->product->stock) {

                    //     throw new \Exception(
                    //         "Only {$item->product->stock} available for {$item->product->name}"
                    //     );
                    // }

                    // $subtotal += $item->subtotal;
                    $availableStock = $item->variation
    ? $item->variation->stock
    : $item->product->stock;

if ($item->quantity > $availableStock) {

    throw new \Exception(
        "Only {$availableStock} available for {$item->product->name}"
    );
}

$subtotal += $item->subtotal;
                }

                /*
                |--------------------------------------------------------------------------
                | Totals
                |--------------------------------------------------------------------------
                */
                $tax = 0;
                $shippingCost = 0;

                $total = $subtotal + $tax + $shippingCost;

                /*
                |--------------------------------------------------------------------------
                | Create Order
                |--------------------------------------------------------------------------
                */
                $order = Order::create([

                    'user_id' => $user->id,

                    'status' => OrderStatus::PENDING,

                    'payment_status' => PaymentStatus::PENDING,

                    'shipping_name' => $data['shipping_name'],
                    'shipping_address' => $data['shipping_address'],
                    'shipping_city' => $data['shipping_city'],
                    'shipping_state' => $data['shipping_state'] ?? null,
                    'shipping_zipcode' => $data['shipping_zipcode'],
                    'shipping_country' => $data['shipping_country'],
                    'shipping_phone' => $data['shipping_phone'],

                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'shipping_cost' => $shippingCost,
                    'total' => $total,

                    'payment_method' => $data['payment_method'],

                    'order_number' => Order::generateOrderNumber(),

                    'notes' => $data['notes'] ?? null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Initial Status History
                |--------------------------------------------------------------------------
                */
                OrderStatusHistory::create([

                    'order_id' => $order->id,

                    'from_status' => null,

                    'to_status' => OrderStatus::PENDING->value,

                    'changed_by' => $user->id,

                    'notes' => 'Order created',
                ]);

                /*
                |--------------------------------------------------------------------------
                | Create Items
                |--------------------------------------------------------------------------
                */
//                 foreach ($cartItems as $item) {

//                     OrderItem::create([

//                         'order_id' => $order->id,

//                         'product_id' => $item->product_id,

//                         'product_name' => $item->product->name,

//                         'product_sku' => $item->product->sku,

//                         'price' => $item->product->price,

//                         'quantity' => $item->quantity,

//                         'subtotal' => $item->subtotal,
//                     ]);

//                     /*
//                     |--------------------------------------------------------------------------
//                     | COD => Deduct Stock
//                     |--------------------------------------------------------------------------
//                     */
//                   if ( $data['payment_method'] === PaymentProvider::COD->value) 
                  
// {
//     $order->decreaseInventory();

//     $order->transitionTo(
//         OrderStatus::PROCESSING,
//         $user,
//         'COD Order'
//     );
// }
foreach ($cartItems as $item) {

    OrderItem::create([

        'order_id' => $order->id,

        'product_id' => $item->product_id,

        'product_variation_id' => $item->product_variation_id,

        'product_name' => $item->product->name,

        'product_sku' => $item->variation?->sku
            ?? $item->product->sku,

        'price' => $item->variation?->sale_price
            ?? $item->variation?->price
            ?? $item->product->price,

        'quantity' => $item->quantity,

        'subtotal' => $item->subtotal,
    ]);
}

if ($data['payment_method'] === PaymentProvider::COD->value) {

    $order->decreaseInventory();

    $order->transitionTo(
        OrderStatus::PROCESSING,
        $user,
        'COD Order'
    );
}

Cart::forUser($user->id)->delete();

return $order;

                /*
                |--------------------------------------------------------------------------
                | Clear Cart
                |--------------------------------------------------------------------------
                */
                Cart::forUser($user->id)
                    ->delete();

                return $order;
               
            });

            return $this->successResponse(
                $order->load('items.product'),
                'Order created successfully',
                201
            );

        } catch (\Throwable $e) {

            return $this->errorResponse(
                $e->getMessage(),
                422
            );
        }
    }


public function orderHistory(Request $request)
{
    $orders = $request->user()
        ->orders()
        ->with('items.product')
        ->latest()
        ->get();

    return $this->successResponse(
        $orders,
        'Orders retrieved successfully'
    );
}


public function orderDetails(Request $request, $id)
{
    $order = $request->user()
        ->orders()
        ->with('items.product')
        ->find($id);

    if (! $order) {

        return $this->errorResponse(
            'Order not found',
            404
        );
    }

    return $this->successResponse(
        $order,
        'Order details retrieved successfully'
    );
}

public function cancelOrder(
    Request $request,
    Order $order
)
{
    if (
        $order->user_id !==
        $request->user()->id
    ) {
        return $this->errorResponse(
            'Unauthorized',
            403
        );
    }

    if (! $order->canBeCancelled()) {
        return $this->errorResponse(
            'Order cannot be cancelled',
            422
        );
    }

    DB::transaction(function () use (
        $order,
        $request
    ) {

        $order->restoreInventory();

        $order->transitionTo(
            OrderStatus::CANCELLED,
            $request->user(),
            'Cancelled by customer'
        );

        if (
            $order->payment_status ===
            PaymentStatus::PENDING
        ) {
            $order->markAsFailed();
        }
    });

    return $this->successResponse(
        $order->fresh(),
        'Order cancelled successfully'
    );
}
public function timeline(
    Request $request,
    Order $order
)
{
    if (
        $order->user_id !==
        $request->user()->id
    ) {
        return $this->errorResponse(
            'Unauthorized',
            403
        );
    }

    return $this->successResponse(
        $order->statusHistory,
        'Timeline retrieved successfully'
    );
}
}