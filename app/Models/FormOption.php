<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormOption extends Model
{
    protected $appends = ['parse_desc'];
    public function getParseDescAttribute(){
        if(isset($this->attributes['desc'])){
//            return json_encode([
//                's'=>'s',
//                'm'=>'m'
//            ]);
            return json_decode($this->attributes['desc'], true);
        }
    }

    public function stase(){
        return $this->hasOne(Stase::class, 'id', 'relation_id');
    }
}
