<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderStatusChanged;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',

        'shipping_name',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_zipcode',
        'shipping_country',
        'shipping_phone',

        'subtotal',
        'tax',
        'shipping_cost',
        'total',

        'payment_method',
        'payment_status',

        'transaction_id',
        'payment_reference',
        'payment_provider',

        'paid_at',

        'order_number',
        'notes',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'paid_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class)
            ->latest();
    }
    public function payments()
    {
    return $this->hasMany(Payment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Order Number
    |--------------------------------------------------------------------------
    */

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
    }

    /*
    |--------------------------------------------------------------------------
    | Status Transitions
    |--------------------------------------------------------------------------
    */

    public function transitionTo(
        OrderStatus $newStatus,
        ?User $changedBy = null,
        ?string $notes = null
    ): bool {
        if ($this->status === $newStatus) {
            return true;
        }

        if (! $this->status->canTransitionTo($newStatus)) {
            return false;
        }

        $oldStatus = $this->status;

        $this->update([
            'status' => $newStatus,
        ]);

        if (class_exists(OrderStatusHistory::class)) {

            $this->statusHistory()->create([
                'from_status' => $oldStatus->value,
                'to_status' => $newStatus->value,
                'changed_by' => $changedBy?->id ?? Auth::id(),
                'notes' => $notes,
            ]);
        }

        event(new OrderStatusChanged(
            $this,
            $oldStatus->value,
            $newStatus->value
        ));

        return true;
    }

    public function latestStatusChange()
    {
        return $this->statusHistory()->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    */

    public function canAcceptPayment(): bool
    {
        return in_array(
            $this->payment_status,
            [
                PaymentStatus::PENDING,
                PaymentStatus::FAILED,
            ]
        );
    }

    public function markAsPaid(string $transactionId): void
    {
        if ($this->payment_status === PaymentStatus::COMPLETED) {
            return;
        }

        $this->update([
            'status' => OrderStatus::PAID,
            'payment_status' => PaymentStatus::COMPLETED,
            'transaction_id' => $transactionId,
            'paid_at' => now(),
        ]);

        $this->loadMissing([
    'items.product',
    'items.variation',
]);

        $this->decreaseInventory();
    }

    public function markAsFailed(): void
    {
        $this->update([
            'payment_status' => PaymentStatus::FAILED,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Cancellation
    |--------------------------------------------------------------------------
    */

    public function canBeCancelled(): bool
    {
        return in_array(
            $this->status,
            [
                OrderStatus::PENDING,
                OrderStatus::PAID,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */


public function decreaseInventory(): void
{
    $this->loadMissing([
        'items.product',
        'items.variation',
    ]);

    foreach ($this->items as $item) {

        /*
        |--------------------------------------------------------------------------
        | Product Variation
        |--------------------------------------------------------------------------
        */

        if ($item->variation) {

            $variation = ProductVariation::lockForUpdate()
                ->find($item->product_variation_id);

            if (! $variation) {
                continue;
            }

            if ($variation->stock < $item->quantity) {
                throw new \Exception(
                    "Insufficient stock for {$item->product_name}"
                );
            }

            $variation->decrement(
                'stock',
                $item->quantity
            );

            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Normal Product
        |--------------------------------------------------------------------------
        */

        $product = Product::lockForUpdate()
            ->find($item->product_id);

        if (! $product) {
            continue;
        }

        if ($product->stock < $item->quantity) {
            throw new \Exception(
                "Insufficient stock for {$product->name}"
            );
        }

        $product->decrement(
            'stock',
            $item->quantity
        );
    }
}

    public function restoreInventory(): void
    {
        $this->loadMissing([
    'items.product',
    'items.variation',
]);

        foreach ($this->items as $item) {

            $product = Product::find($item->product_id);

            if (! $product) {
                continue;
            }

            $product->increment(
                'stock',
                $item->quantity
            );
        }
    }
}