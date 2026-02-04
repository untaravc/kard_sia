<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceToken extends Model
{
    use SoftDeletes;
    protected $fillable = [
        "auth_type",
        "auth_id",
        "token",
        "platform",
        "last_seen_at",
    ];
}
