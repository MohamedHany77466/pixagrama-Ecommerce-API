<?php

namespace App\Http\Controllers\Api;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    use ApiResponseTrait;

    /**
     * Orders List
     */
    public function index()
    {
        $orders = Order::with([
            'user:id,name,email',
        ])
        ->latest()
        ->paginate(20);

        return $this->successResponse(
            $orders,
            'Orders retrieved successfully'
        );
    }

    /**
     * Order Details
     */
    public function show(Order $order)
    {
        $order->load([
            'user:id,name,email',
            'items.product',
            'statusHistory',
        ]);

        return $this->successResponse(
            $order,
            'Order retrieved successfully'
        );
    }

    /**
     * Update Order Status
     */
   public function updateStatus(
    Request $request,
    Order $order
)
{
    $request->validate([
        'status' => [
            'required',
            'in:' . implode(',', OrderStatus::values()),
        ],
    ]);

    $newStatus = OrderStatus::from(
        $request->status
    );

    if (! $order->transitionTo(
        $newStatus,
        $request->user()
    )) {

        return $this->errorResponse(
            'Invalid status transition',
            422
        );
    }

    if ($newStatus === OrderStatus::CANCELLED) {
        $order->restoreInventory();
    }

    return $this->successResponse(
        $order->fresh()->load([
            'user',
            'items.product',
            'statusHistory',
        ]),
        'Order status updated successfully'
    );
}
}