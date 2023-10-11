<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class KsmSchedule extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $appends = ['action_theme', 'mine'];

    public function lecture(){
        return $this->belongsTo(Lecture::class);
    }

    public function getActionThemeAttribute(){
        if($this->attributes['action']){
            switch ($this->attributes['action']){
                case 'struktural':
                    return 'badge-primary';
                case 'koroner':
                    return 'badge-warning';
                case 'vaskuler':
                    return 'badge-info';
                case 'aritmia':
                    return 'badge-secondary';
                case 'pediatric':
                    return 'badge-success';
            }
        }
    }

    public function getMineAttribute(){
        $lecture_id = Auth::guard('lecture')->id();

        if($this->attributes['lecture_id']){
            return $this->attributes['lecture_id'] === $lecture_id;
        }
    }
}
