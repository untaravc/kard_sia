<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class StudentLog extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $appends = ['photo_link'];

    public function lecture(){
        return $this->belongsTo(Lecture::class);
    }

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function stase(){
        return $this->belongsTo(Stase::class);
    }

    public function scopeStudentHas($logs){
        $auth = Auth::guard('student')->id();

        if($auth){
            return $logs->whereStudentId($auth);
        }

        return false;
    }

    public function scopeLectureHas($logs){
        $auth = Auth::guard('lecture')->id();

        if($auth){
            return $logs->whereLectureId($auth);
        }

        return false;
    }

    public function getPhotoLinkAttribute(){
        if(isset($this->attributes['photo'])){
            return '/storage/' . $this->attributes['photo'];
        }
        return null;
    }
}
