<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaseTaskLogPoint extends Model
{
    protected $fillable = [
        'stase_task_log_id',
        'task_detail_id',
        'score',
    ];
    protected $appends = ['order'];

    public function taskDetail(){
        return $this->belongsTo(TaskDetail::class);
    }

    public function staseTaskLog(){
        return $this->belongsTo(StaseTaskLog::class);
    }

    public function getOrderAttribute(){
        return $this->taskDetail()->first()->order;
    }
}
