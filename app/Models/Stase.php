<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class Stase extends Model
{
    use SoftDeletes, SearchableTrait;

    protected $fillable = [
        "name",
        "desc",
        "font_color",
        "stase_order",
        "alias",
        "color",
        "lecture_names",
        "evaluation_link",
        "lecture_name",
        "is_mandatory",
        "study_program_code",
        "section",
        "semester",
        "sks",
        "duration",
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    protected $searchable = [
        'columns' => [
            'stases.name' => 10,
            'stases.desc' => 10,
        ]
    ];

    public function staseLogs()
    {
        return $this->hasMany(StaseLog::class);
    }

    public function staseTasks()
    {
        return $this->hasMany(StaseTask::class);
    }

    public function staseLogOngoing()
    {
        return $this->hasMany(StaseLog::class)->whereStatus('ongoing');
    }
}
