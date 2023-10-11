<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class StaseLog extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function stase(){
        return $this->belongsTo(Stase::class);
    }

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function student_profile(){
        return $this->belongsTo(StudentProfile::class, 'student_id', 'student_id');
    }

    public function staseTaskLogs(){
        return $this->hasMany(StaseTaskLog::class);
    }

    public function getStartDateAttribute(){
        return substr($this->attributes['start_date'], 0, 10);
    }

    public function getEndDateAttribute(){
        return substr($this->attributes['end_date'], 0, 10);
    }

    public function scopeMyOwn($q) {
        $id = Auth::guard('student')->id();
        if($id){
            return $q->whereStudentId($id);
        }
        return $q;
    }
}
