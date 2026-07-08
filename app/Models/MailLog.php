<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    protected $fillable = [
        'name',
        'destination_email',
        'destination_phone',
        'origin_email',
        'title',
        'template',
        'label',
        'document_url',
        'data',
        'status',
        'sent_at',
    ];
}


