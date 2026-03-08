<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Sales\OrderItem;
use Illuminate\Support\Str;

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
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'return_status',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            if (!$order->id) {
                $order->id = (string) Str::uuid();
            }
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
public function isDelivered()
{
    return $this->status === 'delivered';
}

public function isShipped()
{
    return $this->status === 'shipped';
}
}