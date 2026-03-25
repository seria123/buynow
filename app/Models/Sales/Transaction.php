<?php

namespace App\Models\Sales;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'order_id',
        'user_id',
        'transaction_number',
        'amount',
        'currency',
        'type',
        'status',
        'payment_method',
        'gateway',
        'gateway_transaction_id',
        'mpesa_transaction_id',
        'mpesa_phone_number',
        'gateway_response_code',
        'gateway_response_message',
        'gateway_response_data',
        'customer_email',
        'customer_phone',
        'description',
        'metadata',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response_data' => 'array',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($transaction) {
            if (!$transaction->id) {
                $transaction->id = (string) Str::uuid();
            }
            if (!$transaction->transaction_number) {
                $transaction->transaction_number = $transaction->generateTransactionNumber();
            }
        });
    }

    /**
     * Generate a unique transaction number
     */
    public function generateTransactionNumber(): string
    {
        $prefix = 'TXN';
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(Str::random(6));
        return "{$prefix}-{$timestamp}-{$random}";
    }

    // ==================== Relationships ====================

    /**
     * Get the order this transaction belongs to
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Get the user (customer) who made this transaction
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // ==================== Status Helpers ====================

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === TransactionStatus::PENDING->value;
    }

    /**
     * Check if transaction is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === TransactionStatus::PROCESSING->value;
    }

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === TransactionStatus::COMPLETED->value;
    }

    /**
     * Check if transaction failed
     */
    public function isFailed(): bool
    {
        return $this->status === TransactionStatus::FAILED->value;
    }

    /**
     * Check if transaction is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === TransactionStatus::CANCELLED->value;
    }

    /**
     * Check if transaction is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status === TransactionStatus::REFUNDED->value;
    }

    /**
     * Check if transaction is expired
     */
    public function isExpired(): bool
    {
        return $this->status === TransactionStatus::EXPIRED->value;
    }

    /**
     * Check if transaction is successful (completed or refunded)
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, [
            TransactionStatus::COMPLETED->value,
            TransactionStatus::REFUNDED->value,
        ]);
    }

    // ==================== Payment Status Sync ====================

    /**
     * Update the associated order's payment status based on transaction status
     */
    public function syncOrderPaymentStatus(): void
    {
        if (!$this->order) {
            return;
        }

        $transactionStatus = TransactionStatus::from($this->status);
        $paymentStatus = $transactionStatus->toPaymentStatus();

        $this->order->updatePaymentStatus($paymentStatus);
    }

    /**
     * Mark transaction as completed and sync order payment status
     */
    public function markAsCompleted(array $gatewayData = []): void
    {
        $this->update([
            'status' => TransactionStatus::COMPLETED->value,
            'processed_at' => now(),
            'gateway_response_data' => $gatewayData,
        ]);

        $this->syncOrderPaymentStatus();
    }

    /**
     * Mark transaction as failed and sync order payment status
     */
    public function markAsFailed(string $message = '', array $gatewayData = []): void
    {
        $this->update([
            'status' => TransactionStatus::FAILED->value,
            'gateway_response_message' => $message,
            'gateway_response_data' => $gatewayData,
        ]);

        $this->syncOrderPaymentStatus();
    }

    /**
     * Mark transaction as processing
     */
    public function markAsProcessing(array $gatewayData = []): void
    {
        $this->update([
            'status' => TransactionStatus::PROCESSING->value,
            'gateway_response_data' => $gatewayData,
        ]);
    }

    /**
     * Mark transaction as cancelled
     */
    public function markAsCancelled(string $message = ''): void
    {
        $this->update([
            'status' => TransactionStatus::CANCELLED->value,
            'gateway_response_message' => $message,
        ]);

        $this->syncOrderPaymentStatus();
    }

    /**
     * Mark transaction as refunded
     */
    public function markAsRefunded(array $gatewayData = []): void
    {
        $this->update([
            'status' => TransactionStatus::REFUNDED->value,
            'processed_at' => now(),
            'gateway_response_data' => $gatewayData,
        ]);

        $this->syncOrderPaymentStatus();
    }

    // ==================== Scopes ====================

    /**
     * Scope to get only payment transactions
     */
    public function scopePayments($query)
    {
        return $query->where('type', TransactionType::PAYMENT->value);
    }

    /**
     * Scope to get only refund transactions
     */
    public function scopeRefunds($query)
    {
        return $query->where('type', TransactionType::REFUND->value);
    }

    /**
     * Scope to get successful transactions
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', [
            TransactionStatus::COMPLETED->value,
            TransactionStatus::REFUNDED->value,
        ]);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by order
     */
    public function scopeForOrder($query, string $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }
}
