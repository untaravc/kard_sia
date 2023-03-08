<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
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
}
