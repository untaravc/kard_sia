<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = ['duration'];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function setCheckinDataAttribute($val) {
        $this->attributes['checkin_data'] = json_encode($val);
    }

    public function setCheckoutDataAttribute($val) {
        $this->attributes['checkout_data'] = json_encode($val);
    }

    public function getCheckinDataAttribute($val) {
        return json_decode($val, true);
    }

    public function getCheckoutDataAttribute($val) {
        return json_decode($val, true);
    }

    public function getCheckinPhotoAttribute($val) {
        if($val){
            return 'https://sia.kardiologi-fkkmk.com/storage/' . $val;
        }
    }

    public function getCheckoutPhotoAttribute($val) {
        if($val){
            return 'https://sia.kardiologi-fkkmk.com/storage/' . $val;
        }
    }

    public function getCheckoutAttribute(){
        if(isset($this->attributes['checkout'])){
            if($this->attributes['checkout_note'] == 'by_system'){
                return null;
            }
        }
        return $this->attributes['checkout'];
    }

    public function getDurationAttribute(){
        if(isset($this->attributes['checkout'])){
            if($this->attributes['checkout_note'] == 'by_system'){
                return null;
            }

            if(isset($this->attributes['checkout']) && isset($this->attributes['checkin'])){
                $time = strtotime($this->attributes['checkout']) - strtotime($this->attributes['checkin']);
                $hours = floor($time / 60 / 60);
                $minutes = floor(($time / 60) % 60);
                return $hours . ' Jam ' . $minutes . ' Mnt';
            }
        }
    }
}
