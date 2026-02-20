<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Catalogue\Product;

class Cart extends Model
{
   use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'product_id',
        'product_name',
        'category_name',
        'price',
        'quantity',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
