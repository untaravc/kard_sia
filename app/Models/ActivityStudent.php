<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ActivityStudent extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function activity(){
        return $this->belongsTo(Activity::class);
    }

    public function scopeMyOwn($q) {
        $id = Auth::guard('student')->id();
        if($id){
            return $q->whereStudentId($id);
        }
        return $q;
    }
}
