<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
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
}
