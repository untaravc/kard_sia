<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "number",
        "owner",
        "name",
        "price",
        "estimate_month",
        "supplier",
        "photo_url",
        "photo_urls",
        "brand",
        "purchase_date",
        "category",
        "status",
        "description",
        "warranty_until",
        "location",
    ];

    protected $casts = [
        "photo_urls" => "array",
    ];
}
