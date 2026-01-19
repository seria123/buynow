<?php

namespace App\Models\Catalogue;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class AttributeValue extends Model implements AuditableContract
{
    use Auditable, HasUlids;

    protected $table = 'attribute_values';

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
        'sort_order',
        'is_active',
        'creator_id',
    ];

    protected function casts()
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
