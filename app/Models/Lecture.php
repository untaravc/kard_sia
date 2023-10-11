<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Lecture extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    protected $connection = 'mysql';

    protected $hidden = [
        'password',
    ];

    protected $guarded = [];

    public function lectureProfile(){
        return $this->hasOne(LectureProfile::class);
    }

    public function staseTaskLogs(){
        return $this->hasMany(StaseTaskLog::class);
    }

    public function activity_lectures() {
        return $this->hasMany(ActivityLecture::class);
    }

    public function open_stase_task()
    {
        return $this->hasMany(OpenStaseTask::class);
    }
}
