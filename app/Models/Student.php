<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $connection = 'mysql';
    protected $fillable = [
        "status",
        "year",
        "name",
        "password",
        "email",
    ];
    protected $appends = ['image_link'];

    protected $hidden = [
        'password',
    ];

    public function staseLogs()
    {
        return $this->hasMany(StaseLog::class);
    }

    public function staseLogsActive()
    {
        return $this->hasOne(StaseLog::class)->where('status', 'ongoing');
    }

    public function staseTasks()
    {
        return $this->hasMany(StaseTask::class);
    }

    public function staseTaskLogs()
    {
        return $this->hasMany(StaseTaskLog::class);
    }

    public function staseTaskLogFilled()
    {
        return $this->hasMany(StaseTaskLog::class)
            ->where('point_average', '!=', null);
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function studentLogPending()
    {
        return $this->hasMany(StudentLog::class)->where('status', 0);
    }

    public function today_presence()
    {
        return $this->hasOne(Presence::class)
            ->where('checkin', '>', date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . '-15 hours')))
            ->orderByDesc('id');
    }

    public function presences()
    {
        return $this->hasMany(Presence::class)->whereDate('checkin', now());
    }

    public function today_activity()
    {
        return $this->hasMany(ActivityStudent::class)
            ->whereDate('created_at', now());
    }

    public function open_stase_task()
    {
        return $this->hasMany(OpenStaseTask::class);
    }

    public function getImageLinkAttribute()
    {
        return asset('/') . 'storage/students/default-avatar.jpeg';
    }

    public function last_presence()
    {
        return $this->hasOne(Presence::class)->orderByDesc('id');
    }
}
