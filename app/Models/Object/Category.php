<?php

namespace App\Models\Object;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $connection = 'mysql_obj';
    protected $guarded = [];
    public $timestamps = false;
}
