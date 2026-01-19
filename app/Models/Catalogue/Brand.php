<?php

namespace App\Models\Catalogue;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Brand extends Model implements AuditableContract, HasMedia
{
    use Auditable, HasUlids, InteractsWithMedia;

    protected $table = 'brands';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'website',
        'logo_path',
        'creator_id',
        'sort_order',
        'is_active',
    ];

    protected function casts()
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
    }

    /**
     * Get the logo URL attribute.
     * Falls back to a creative placeholder with brand initials if no logo exists.
     */
    public function getLogoUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('logo');

        if ($media) {
            return $media->getUrl();
        }

        // Generate a creative placeholder with brand initials
        if ($this->name) {
            // Extract initials from brand name
            $words = explode(' ', $this->name);
            $initials = '';

            foreach ($words as $word) {
                if (! empty($word)) {
                    $initials .= strtoupper(substr($word, 0, 1));
                    if (strlen($initials) >= 2) {
                        break;
                    }
                }
            }

            // If only one word, take first 2 characters
            if (strlen($initials) < 2 && strlen($this->name) >= 2) {
                $initials = strtoupper(substr($this->name, 0, 2));
            }

            // Generate a consistent color based on brand name
            $colors = [
                '6366f1', // Indigo
                'ec4899', // Pink
                '8b5cf6', // Purple
                'f59e0b', // Amber
                '10b981', // Emerald
                '3b82f6', // Blue
                'ef4444', // Red
                '14b8a6', // Teal
                'f97316', // Orange
                '06b6d4', // Cyan
            ];

            $colorIndex = abs(crc32($this->name)) % count($colors);
            $backgroundColor = $colors[$colorIndex];

            return sprintf(
                'https://ui-avatars.com/api/?name=%s&background=%s&color=fff&size=256&bold=true&length=2&rounded=false&font-size=0.4',
                urlencode($initials),
                $backgroundColor
            );
        }

        return null;
    }
}
