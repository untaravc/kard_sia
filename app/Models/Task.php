<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;
    protected $fillable = [
        "name",
        "desc",
        "is_latter",
    ];

    // public function stase(){
    //     return $this->belongsTo(Stase::class);
    // }

    public function taskDetails(){
        return $this->hasMany(TaskDetail::class);
    }
}
