<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandardPoint extends Model
{
    protected $guarded = [];

    public function form_options(){
        return $this->belongsToMany(
            FormOption::class,
            'standard_point_form_option',
            'standard_point_id',
            'form_option_id'
        );
    }
}
