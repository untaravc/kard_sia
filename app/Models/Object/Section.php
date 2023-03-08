<?php

namespace App\Models\Object;

use App\Models\Lecture;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $connection = 'mysql_obj';
    protected $guarded = [];

    public function lecture(){
        return $this->belongsTo(Lecture::class);
    }
}
