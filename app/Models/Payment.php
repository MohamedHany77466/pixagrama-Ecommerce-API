<?php

namespace App\Models;

use App\Enum\PaymentProvider;
use App\Enum\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'provider',
        'payment_intent_id',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'provider' => PaymentProvider::class,
        'status' => PaymentStatus::class,
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsCompleted(
        ?string $transactionId = null,
        array $metadata = []
    ): void {

        if ($this->status === PaymentStatus::COMPLETED) {
            return;
        }

        $this->update([
            'status' => PaymentStatus::COMPLETED,
            'transaction_id' => $transactionId,
            'completed_at' => now(),
            'metadata' => array_merge(
                $this->metadata ?? [],
                $metadata
            ),
        ]);

        $this->order?->markAsPaid(
            $transactionId
        );
    }

    public function markAsFailed(
        array $metadata = []
    ): void {

        $this->update([
            'status' => PaymentStatus::FAILED,
            'metadata' => array_merge(
                $this->metadata ?? [],
                $metadata
            ),
        ]);

        $this->order?->markAsFailed();
    }

    public function markAsRefunded(
        array $metadata = []
    ): void {

        $this->update([
            'status' => PaymentStatus::REFUNDED,
            'metadata' => array_merge(
                $this->metadata ?? [],
                $metadata
            ),
        ]);
    }
}