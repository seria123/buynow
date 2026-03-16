<?php

namespace App\Models\Sales;

use App\Notifications\RefundStatusChangedNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Refund extends Model
{
    use HasFactory;

    protected $table = 'refunds';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'order_id',
        'user_id',
        'amount',
        'original_amount',
        'reason',
        'status',
        'notes',
        'refund_type',
        'mpesa_transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($refund) {
            if (!$refund->id) {
                $refund->id = (string) Str::uuid();
            }
        });

        // Send notification when refund status changes
        static::updating(function ($refund) {
            if ($refund->isDirty('status')) {
                $oldStatus = $refund->getOriginal('status');
                $newStatus = $refund->status;
                
                // Load the user and order relationship before sending notification
                $refund->load(['order', 'order.user']);
                
                if ($refund->user) {
                    $refund->user->notify(new RefundStatusChangedNotification($refund, $oldStatus, $newStatus));
                }
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFullRefund(): bool
    {
        return $this->refund_type === 'full';
    }

    public function isPartialRefund(): bool
    {
        return $this->refund_type === 'partial';
    }

    /**
     * Send refund status change notification to customer
     */
    public function sendStatusNotification(string $oldStatus, string $newStatus): void
    {
        // Ensure relationships are loaded
        $this->load(['order', 'order.user']);
        
        if ($this->user) {
            $this->user->notify(new RefundStatusChangedNotification($this, $oldStatus, $newStatus));
        }
    }

    public function markAsProcessing(): void
    {
        $oldStatus = $this->status;
        $this->update(['status' => 'processing']);
        $this->sendStatusNotification($oldStatus, 'processing');
    }

    public function markAsCompleted(string $mpesaTransactionId = null): void
    {
        $oldStatus = $this->status;
        $this->update([
            'status' => 'completed',
            'mpesa_transaction_id' => $mpesaTransactionId,
        ]);
        $this->sendStatusNotification($oldStatus, 'completed');
    }

    public function markAsFailed(string $notes = null): void
    {
        $oldStatus = $this->status;
        $this->update([
            'status' => 'failed',
            'notes' => $notes,
        ]);
        $this->sendStatusNotification($oldStatus, 'failed');
    }

    public function markAsRejected(string $notes = null): void
    {
        $oldStatus = $this->status;
        $this->update([
            'status' => 'rejected',
            'notes' => $notes,
        ]);
        $this->sendStatusNotification($oldStatus, 'rejected');
    }
}
