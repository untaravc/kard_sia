<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentExams extends Model
{
    protected $guarded = [];

    public function exam(){
        return $this->belongsTo(Exam::class);
    }
}
