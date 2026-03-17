<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Models\Sales\Cart;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail, HasMedia, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles, InteractsWithMedia;

    /**
     * The attributes that are always appended to the array.
     *
     * @var list<string>
     */
    protected $appends = [
        'avatar',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'google_id',
        'facebook_id',
        'phone',
        'avatar',
        'customer_group_id',
        'marketing_opt_in',
        'marketing_opt_in_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'marketing_opt_in' => 'boolean',
            'marketing_opt_in_at' => 'datetime',
        ];
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Opt in to marketing communications.
     */
    public function optInToMarketing(): self
    {
        $this->update([
            'marketing_opt_in' => true,
            'marketing_opt_in_at' => now(),
        ]);

        return $this;
    }

    /**
     * Opt out of marketing communications.
     */
    public function optOutOfMarketing(): self
    {
        $this->update([
            'marketing_opt_in' => false,
            'marketing_opt_in_at' => null,
        ]);

        return $this;
    }

    /**
     * Toggle marketing opt-in status.
     */
    public function toggleMarketingOptIn(): self
    {
        return $this->marketing_opt_in 
            ? $this->optOutOfMarketing() 
            : $this->optInToMarketing();
    }

    /**
     * Get the avatar URL attribute.
     * Falls back to ui-avatars.com API if no avatar is uploaded.
     */
    public function getAvatarAttribute(): ?string
    {
        $media = $this->getFirstMedia('avatar');
        
        if ($media) {
            // Add cache-busting query parameter using media's updated_at
            $url = $media->getUrl();
            $version = $media->updated_at->timestamp;
            return $url . '?v=' . $version;
        }

        // Fallback to ui-avatars.com API when no avatar exists
        // The API automatically extracts initials from the name
        if ($this->name) {
            return sprintf(
                'https://ui-avatars.com/api/?name=%s&background=FFEB3B&color=222&size=128&bold=true&length=2&rounded=true',
                urlencode($this->name)
            );
        }

        return null;
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['Admin', 'Super Admin', 'Developer']);
    }

    public function orders()
{
    return $this->hasMany(\App\Models\Sales\Order::class, 'user_id', 'id');
}
public function cartItems()
{
    return $this->hasMany(Cart::class, 'user_id', 'id')->with('product');
}
public function wishlist()
{
    return $this->hasMany(Wishlist::class, 'user_id', 'id');
}

/**
 * Get the customer group that the user belongs to
 */
public function customerGroup()
{
    return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
}

/**
 * Get the announcements received by this user
 */
public function announcements()
{
    return $this->belongsToMany(Announcement::class, 'announcement_recipients')
        ->withPivot('sent_at', 'opened_at')
        ->withTimestamps();
}

/**
 * Get the discount rate for the user based on their customer group
 */
public function getDiscountRateAttribute(): float
{
    return $this->customerGroup?->discount_rate ?? 0;
}

/**
 * Apply customer group discount to a price
 */
public function applyGroupDiscount(float $price): float
{
    $discountRate = $this->discount_rate;
    return $price - ($price * ($discountRate / 100));
}

/**
 * Get total lifetime value (total amount spent)
 */
public function getLifetimeValueAttribute(): float
{
    return $this->orders()->sum('total_amount') ?? 0;
}

/**
 * Get total number of orders placed
 */
public function getTotalOrdersAttribute(): int
{
    return $this->orders()->count() ?? 0;
}

/**
 * Get average order value
 */
public function getAverageOrderValueAttribute(): float
{
    $totalOrders = $this->total_orders;
    if ($totalOrders === 0) {
        return 0;
    }
    return $this->lifetime_value / $totalOrders;
}

/**
 * Check if customer is returning (has more than 1 order)
 */
public function getIsReturningAttribute(): bool
{
    return $this->total_orders > 1;
}

/**
 * Get the customer's first order date
 */
public function getFirstOrderDateAttribute(): ?Carbon
{
    // If already set as an attribute (from selectRaw), convert to Carbon
    if (isset($this->attributes['first_order_date'])) {
        $value = $this->attributes['first_order_date'];
        if ($value === null) {
            return null;
        }
        return Carbon::parse($value);
    }
    
    $value = $this->orders()->min('created_at');
    
    if ($value === null) {
        return null;
    }
    
    return $value instanceof Carbon ? $value : Carbon::parse($value);
}

/**
 * Get the customer's last order date
 */
public function getLastOrderDateAttribute(): ?Carbon
{
    // If already set as an attribute (from selectRaw), convert to Carbon
    if (isset($this->attributes['last_order_date'])) {
        $value = $this->attributes['last_order_date'];
        if ($value === null) {
            return null;
        }
        return Carbon::parse($value);
    }
    
    $value = $this->orders()->max('created_at');
    
    if ($value === null) {
        return null;
    }
    
    return $value instanceof Carbon ? $value : Carbon::parse($value);
}

/**
 * Check if customer is active (has ordered in last 90 days)
 */
public function getIsActiveAttribute(): bool
{
    $lastOrder = $this->last_order_date;
    if (!$lastOrder) {
        return false;
    }
    return $lastOrder->diffInDays(now()) <= 90;
}

}
