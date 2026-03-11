<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Catalogue\Product;
use Illuminate\Support\Str;

class OrderItem extends Model
{ use HasFactory, HasUuids;

    protected $table = 'order_items';

    // UUID settings
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'order_id',
        'product_name',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];


    /**
     * Relationships
     */

      protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (!$item->id) {
                $item->id = (string) Str::uuid();
            }
        });
    }


    // Relation to parent Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    // Relation to Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}