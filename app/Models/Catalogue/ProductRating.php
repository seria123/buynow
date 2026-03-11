<?php

namespace App\Models\Catalogue;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductRating extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'product_ratings';

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate average rating for a product.
     */
    public static function getAverageRating($productId)
    {
        $rating = self::where('product_id', $productId)->avg('rating');
        return $rating ? round($rating, 1) : 0;
    }

    /**
     * Get total ratings count for a product.
     */
    public static function getRatingCount($productId)
    {
        return self::where('product_id', $productId)->count();
    }
}
