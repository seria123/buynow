<?php

namespace App\Models\Catalogue;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class AttributeFamily extends Model implements AuditableContract
{
    use Auditable, HasUlids;

    protected $table = 'attribute_families';

    protected $fillable = [
        'name',
        'slug',
        'description',
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

    public function attributes()
    {
        return $this->hasMany(Attribute::class, 'attribute_family_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'attribute_family_id');
    }
}
