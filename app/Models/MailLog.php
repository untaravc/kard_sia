<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    protected $fillable = [
        'name',
        'email',
        'origin',
        'title',
        'label',
        'data',
        'status',
        'sent_at',
        'response',
    ];
}


