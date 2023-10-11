<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $guarded = [];
    protected $appends = ['image_link'];

    public function getImageLinkAttribute(){
        if(isset($this->attributes['image']) && $this->attributes['image'] != null){
            return asset('/') . 'storage/' . $this->attributes['image'];
        }else{
            return asset('/') . 'storage/students/default-avatar.jpeg';
        }
    }

    public function lecture(){
        return $this->belongsTo(Lecture::class);
    }
}
