<?php

namespace App\Models\Object;

use App\Models\Lecture;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $connection = 'mysql_obj';
    protected $guarded = [];
    protected $appends = ['answer_str', 'option_arr'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function lecture()
    {
        return $this->belongsTo(Lecture::class);
    }

    public function getAnswerStrAttribute()
    {
        if (isset($this->attributes['answer'])) {
            $str = $this->attributes[$this->attributes['answer']];
            if (isset($str)) {
                return $str;
            }
        }
    }

    public function getOptionArrAttribute()
    {
        $arr = [];
        if (isset($this->attributes['option_1']) && $this->attributes['option_1'] != null) {
            $arr[] = [
                'option' => 'option_1',
                'text'   => $this->attributes['option_1'],
            ];
        }

        if (isset($this->attributes['option_2']) && $this->attributes['option_2'] != null) {
            $arr[] = [
                'option' => 'option_2',
                'text'   => $this->attributes['option_2'],
            ];
        }

        if (isset($this->attributes['option_3']) && $this->attributes['option_3'] != null) {
            $arr[] = [
                'option' => 'option_3',
                'text'   => $this->attributes['option_3'],
            ];
        }

        if (isset($this->attributes['option_4']) && $this->attributes['option_4'] != null) {
            $arr[] = [
                'option' => 'option_4',
                'text'   => $this->attributes['option_4'],
            ];
        }

        if (isset($this->attributes['option_5']) && $this->attributes['option_5'] != null) {
            $arr[] = [
                'option' => 'option_5',
                'text'   => $this->attributes['option_5'],
            ];
        }

        return $arr;
    }
}
