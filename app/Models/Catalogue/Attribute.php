<?php

namespace App\Models\Catalogue;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Attribute extends Model implements AuditableContract
{
    use Auditable, HasUlids;

    protected $table = 'attributes';

    protected $fillable = [
        'name',
        'slug',
        'attribute_family_id',
        'type',
        'description',
        'sort_order',
        'is_active',
        'creator_id',
    ];

    protected function casts()
    {
        return [
            'type' => AttributeType::class,
            'is_active' => 'boolean',
        ];
    }

    public function attributeFamily()
    {
        return $this->belongsTo(AttributeFamily::class, 'attribute_family_id');
    }

    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
