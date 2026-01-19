<?php

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ProductAttributeValue extends Model implements AuditableContract
{
    use Auditable, HasFactory, HasUlids;

    protected $table = 'product_attribute_values';

    protected $fillable = [
        'product_id',
        'attribute_id',
        'value',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
