<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    protected $guarded = [];
    public function students(){
        return $this->belongsToMany(Student::class, 'letter_students');
    }

    public function lectures(){
        return $this->belongsToMany(Lecture::class, 'letter_lectures');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
