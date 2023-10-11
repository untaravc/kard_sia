<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class StaseTaskLog extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function task(){
        return $this->belongsTo(Task::class);
    }

    public function staseTask(){
        return $this->belongsTo(StaseTask::class);
    }

    public function stase(){
        return $this->belongsTo(Stase::class);
    }

    public function staseLog(){
        return $this->belongsTo(StaseLog::class);
    }

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function lecture(){
        return $this->belongsTo(Lecture::class);
    }

    public function staseTaskLogPoint(){
        return $this->hasMany(StaseTaskLogPoint::class);
    }

    public function staseTaskLogPointFilled(){
        return $this->hasMany(StaseTaskLogPoint::class)
            ->where('score', '>', 10)
            ->where('score', '!=', null);
    }

    public function files(){
        return $this->hasMany(File::class);
    }

    public function scopeMyOwn($q) {
        if(Auth::guard('student')->check()){
            $id = Auth::guard('student')->id();
            if($id){
                return $q->whereStudentId($id);
            }
        }
        return $q;
    }
}
