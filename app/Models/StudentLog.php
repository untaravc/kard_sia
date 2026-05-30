<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class StudentLog extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'student_id',
        'lecture_id',
        'type',
        'stase_id',
        'stase_log_id',
        'stase_task_id',
        'stase_task_log_id',
        'field_1',
        'field_2',
        'field_3',
        'field_4',
        'field_5',
        'field_6',
        'date',
        'status',
        'photo',
        'category',
    ];
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

    public function stase_log_skills(){
        return $this->hasMany(StudentLogSkill::class);
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
