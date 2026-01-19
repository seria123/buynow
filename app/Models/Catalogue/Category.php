<?php

namespace App\Models\Catalogue;

use App\Enums\ProductStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Category extends Model implements AuditableContract
{
    use Auditable, HasUlids;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'parent_id',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function activeChildren()
    {
        return $this->children()
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function navProducts()
    {
        return $this->products()
            ->where('status', ProductStatus::Approved)
            ->where('published', true)
            ->orderBy('name')
            ->with([
                'variants' => fn ($query) => $query->orderBy('sort_order'),
            ]);
    }
}
