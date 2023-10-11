<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class AppLog extends Model
{
    protected $guarded = [];

    public function admin(){
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function lecture(){
        return $this->belongsTo(Lecture::class);
    }
}
