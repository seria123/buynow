<?php

namespace App\Models\Sales;

use App\Notifications\NewOrderAdminNotification;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\PaymentStatusChangedNotification;
use App\Notifications\ShipmentStatusChangedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Sales\OrderItem;
use Illuminate\Support\Str;
use App\Models\Sales\Refund;
use App\Models\Sales\Transaction;
use App\Models\User;
use App\Services\InventoryService;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'order_number',
        'checkout_request_id',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'return_status',
        'promotion_id',
        'discount_amount',
        'promotion_code',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            if (!$order->id) {
                $order->id = (string) Str::uuid();
            }
        });

        // Send notifications after order is created
        static::created(function ($order) {
            $order->sendOrderPlacedNotification();
            $order->sendNewOrderAdminNotification();
            $order->deductInventoryStock();
        });
    }

    // ✅ Relationships
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // THIS MUST MATCH WHAT YOU USE IN VUE
    public function orderItems()
    {
        return $this->hasMany(\App\Models\Sales\OrderItem::class, 'order_id', 'id');
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
        
    }

    /**
     * Get the invoice associated with this order
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id', 'id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'order_id', 'id');
    }

    /**
     * Get all transactions for this order
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'order_id', 'id');
    }

    /**
     * Get the primary/first transaction for this order
     */
    public function primaryTransaction()
    {
        return $this->hasOne(Transaction::class, 'order_id', 'id')->latest();
    }

    /**
     * Get the successful payment transaction for this order
     */
    public function successfulTransaction()
    {
        return $this->hasOne(Transaction::class, 'order_id', 'id')
            ->whereIn('status', ['completed', 'refunded'])
            ->latest();
    }

    /**
     * Check if order has any successful transactions
     */
    public function hasSuccessfulTransaction(): bool
    {
        return $this->transactions()->successful()->exists();
    }

    /**
     * Get the promotion applied to this order
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }

    public function hasRefunds(): bool
    {
        return $this->refunds()->exists();
    }

    public function hasCompletedRefunds(): bool
    {
        return $this->refunds()->where('status', 'completed')->exists();
    }

    public function getTotalRefundedAttribute(): float
    {
        return $this->refunds()->where('status', 'completed')->sum('amount');
    }

    public function isFullyRefunded(): bool
    {
        return $this->total_refunded >= $this->total_amount;
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isShipped()
    {
        return $this->status === 'shipped';
    }

    /**
     * Send order placed notification to customer
     */
    public function sendOrderPlacedNotification(): void
    {
        if ($this->user) {
            $this->user->notify(new OrderPlacedNotification($this));
        }
    }

    /**
     * Send new order notification to admin users
     */
    public function sendNewOrderAdminNotification(): void
    {
        $adminUsers = User::role(['Admin', 'Super Admin', 'Developer'])->get();
        
        foreach ($adminUsers as $admin) {
            $admin->notify(new NewOrderAdminNotification($this));
        }
    }

    /**
     * Send payment status change notification to customer
     */
    public function sendPaymentStatusNotification(string $oldStatus, string $newStatus): void
    {
        if ($this->user && $oldStatus !== $newStatus) {
            $this->user->notify(new PaymentStatusChangedNotification($this, $oldStatus, $newStatus));
        }
    }

    /**
     * Send shipment status change notification to customer
     */
    public function sendShipmentStatusNotification(string $oldStatus, string $newStatus): void
    {
        if ($this->user && $oldStatus !== $newStatus) {
            $this->user->notify(new ShipmentStatusChangedNotification($this, $oldStatus, $newStatus));
        }
    }

    /**
     * Update payment status and send notification
     */
    public function updatePaymentStatus(string $newStatus): void
    {
        $oldStatus = $this->payment_status;
        $this->update(['payment_status' => $newStatus]);
        $this->sendPaymentStatusNotification($oldStatus, $newStatus);
    }

    /**
     * Update order status (shipment) and send notification
     */
    public function updateStatus(string $newStatus): void
    {
        $oldStatus = $this->status;
        $this->update(['status' => $newStatus]);
        $this->sendShipmentStatusNotification($oldStatus, $newStatus);
    }

    /**
     * Deduct inventory stock for all items in the order.
     */
    public function deductInventoryStock(): void
    {
        $inventoryService = app(InventoryService::class);

        foreach ($this->orderItems as $item) {
            if ($item->product) {
                $inventoryService->deductStockForOrder($item->product, $item->quantity);
            }
        }
    }
}
