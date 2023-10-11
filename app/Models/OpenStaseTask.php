<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class OpenStaseTask extends Model
{
    use SoftDeletes;
    use SearchableTrait;

    public $searchable = [
        'columns'   => [
            'lectures.name' => 10,
            'students.name' => 10,
            'stase_tasks.name' => 10,
        ],
        'joins'=>[
            'lectures'  => ['lectures.id', 'open_stase_tasks.lecture_id'],
            'students'   => ['students.id', 'open_stase_tasks.student_id'],
            'stase_tasks'   => ['stase_tasks.id', 'open_stase_tasks.stase_task_id'],
        ],
    ];
    protected $appends = ['score'];

    protected $guarded = [];

    public function lecture(){
        return $this->belongsTo(Lecture::class);
    }

    public function staseTask(){
        return $this->belongsTo(StaseTask::class);
    }

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function files(){
        return $this->hasMany(File::class);
    }

    public function getScoreAttribute() {
        $stase_task_log = StaseTaskLog::whereStudentId($this->attributes['student_id'])
            ->whereLectureId($this->attributes['lecture_id'])
            ->whereStaseTaskId($this->attributes['stase_task_id'])
            ->first();
        if($stase_task_log && $stase_task_log['point_average'] > 0){
            return $stase_task_log['point_average'];
        }
        return null;
    }
}
