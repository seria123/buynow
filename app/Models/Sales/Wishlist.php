<?php

namespace App\Models\Sales;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wishlist extends Model
{

    protected $table = 'wishlist';
    protected $fillable = ['user_id', 'product_id'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Catalogue\Product::class);
    }
}