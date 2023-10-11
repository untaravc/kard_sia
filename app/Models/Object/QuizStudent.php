<?php

namespace App\Models\Object;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;

class QuizStudent extends Model
{
    protected $connection = 'mysql_obj';
    protected $guarded = [];
    protected $appends = ['link', 'status_label', 'is_closed_label'];

    public function section(){
        return $this->belongsTo(Section::class);
    }

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function getLinkAttribute(){
        if(isset($this->attributes['token'])){
            return env('BASE_URL') . '/f/quiz?t=' . $this->attributes['token'];
        }
    }

    public function getStatusLabelAttribute(){
        if(isset($this->attributes['status'])){
            switch ($this->attributes['status']){
                case 0: return 'Draft';
                case 1: return 'open';
                case 2: return 'Finish';
            }
        }
    }

    public function getIsClosedLabelAttribute(){
        if(isset($this->attributes['is_closed'])){
            switch ($this->attributes['is_closed']){
                case 0: return 'Terbuka';
                case 1: return 'Tertutup';
            }
        }
    }
}

