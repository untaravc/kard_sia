<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationDetail extends Model
{
    protected $fillable = [
        'registration_id',
        'label',
        'name',
        'desc',
        'date',
        'file_url',
        'desc_1',
        'desc_2',
        'contact',
        'duration',
        'place',
        'year',
    ];
}
