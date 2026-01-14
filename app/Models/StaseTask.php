<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaseTask extends Model
{
    use SoftDeletes;
    protected $guarded = [];
//    public $timestamps = false;

    public function stase(){
        return $this->belongsTo(Stase::class);
    }

    public function lecture(){
        return $this->belongsTo(Lecture::class);
    }

    public function task(){
        return $this->belongsTo(Task::class);
    }

    public function openStaseTask(){
        return $this->hasOne(OpenStaseTask::class);
    }

    public function openStaseTasks(){
        return $this->hasMany(OpenStaseTask::class);
    }

    public function staseTaskLogs(){
        return $this->hasMany(StaseTaskLog::class);
    }
}
