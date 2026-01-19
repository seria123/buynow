<?php

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ProductVariantOption extends Model implements AuditableContract
{
    use Auditable, HasFactory, HasUlids;

    protected $table = 'product_variant_options';

    protected $fillable = [
        'product_variant_id',
        'attribute_id',
        'value',
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
