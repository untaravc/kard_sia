<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class Stase extends Model
{
    use SoftDeletes, SearchableTrait;

    protected $guarded = [];

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
