<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentExams extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'status',
        'attempts',
        'score',
        'start_at',
        'finish_at',
    ];

    public function exam(){
        return $this->belongsTo(Exam::class);
    }
}
