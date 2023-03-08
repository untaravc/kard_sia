<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Nicolaslopezj\Searchable\SearchableTrait;

class Document extends Model
{
    use SearchableTrait;
    use SoftDeletes;
    protected $guarded = [];

    public $searchable = [
        'columns'=>[
            'documents.category'    => 10,
            'documents.title'    => 10,
            'documents.desc'    => 10,
            'lectures.name'    => 10,
            'students.name'    => 10,
        ],
        'joins'=>[
            'lectures' => ['documents.lecture_id', 'lectures.id'],
            'students' => ['documents.student_id', 'students.id'],
        ]
    ];

    public function lecture(){
        return $this->belongsTo(Lecture::class);
    }

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function openStaseTask(){
        return $this->belongsTo(OpenStaseTask::class);
    }

    public function staseTaskLog(){
        return $this->belongsTo(StaseTaskLog::class);
    }

    public function scopeMyOwn($q) {
        if(Auth::guard('student')->check()){
            return $q->whereStudentId(Auth::guard('student')
                ->id());
        }
    }

    public function scopeMyOwnLecture($q) {
        if(Auth::guard('lecture')->check()){
            return $q->whereLectureId(Auth::guard('lecture')
                ->id());
        }
    }
}
