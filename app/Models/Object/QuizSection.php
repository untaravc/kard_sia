<?php

namespace App\Models\Object;

use Illuminate\Database\Eloquent\Model;

class QuizSection extends Model
{
    protected $connection = 'mysql_obj';
    protected $guarded = [];
    protected $table = 'quiz_section';
    public $timestamps = false;

    public function quiz(){
        return $this->belongsTo(Quiz::class);
    }
}
