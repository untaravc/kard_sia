<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LectureProfile extends Model
{
    protected $fillable = [
        'lecture_id',
        'code',
        'degree',
        'pob',
        'dob',
        'phone',
        'address',
        'image',
        'register_date',
    ];
}
