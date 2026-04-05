<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LetterParticipant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'letter_id',
        'auth_type',
        'auth_id',
        'auth_name',
        'type',
        'label',
        'status',
        'token',
        'validated_at',
        'phone',
        'email',
    ];
}
