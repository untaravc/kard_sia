<?php

namespace App\Models\Object;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $connection = 'mysql_obj';
    protected $guarded = [];
}
