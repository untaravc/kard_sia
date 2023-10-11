<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class Exam extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $appends = ['status_label', 'direct_link'];

    public function lecture() {
        return $this->belongsTo(Lecture::class);
    }

    public function stase() {
        return $this->belongsTo(Stase::class);
    }

    public function stase_task() {
        return $this->belongsTo(StaseTask::class);
    }

    public function getStatusLabelAttribute(){
        if(isset($this->attributes['status'])){
            switch ($this->attributes['status']){
                case 0:
                    return 'non-aktif';
                case 1:
                    return 'aktif';
            }
        }
    }

    public function getDirectLinkAttribute(){
        if(isset($this->attributes['token'])){
            return Config::get('app.url') . '/e/' . $this->attributes['token'];
        }
    }
}
