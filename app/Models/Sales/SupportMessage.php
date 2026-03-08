<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SupportMessage extends Model
{
     use HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'message',
        'reply',
        'is_read'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}

