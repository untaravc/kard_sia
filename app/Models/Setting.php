<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'name',
        'label',
        'value',
        'status',
        'is_native',
    ];

    protected $casts = [
        'is_native' => 'boolean',
    ];
}
