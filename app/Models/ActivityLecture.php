<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityLecture extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function lecture(){
        return $this->belongsTo(Lecture::class);
    }

    public function activity(){
        return $this->belongsTo(Activity::class);
    }
}
