<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudyProgram extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'desc',
        'address',
        'head_name',
        'deputy_head_name',
    ];
}
