<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLogSkill extends Model
{
    protected $fillable = [
        'student_id',
        'stase_id',
        'student_log_id',
        'form_option_id',
    ];

    public function formOption()
    {
        return $this->belongsTo(FormOption::class, 'form_option_id');
    }
}
