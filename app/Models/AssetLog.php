<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "auth_id",
        "auth_type",
        "asset_id",
        "status",
        "note",
        "location",
        "photo_urls",
    ];

    protected $casts = [
        "photo_urls" => "array",
    ];

    public function asset(){
        return $this->belongsTo(Asset::class);
    }
}
