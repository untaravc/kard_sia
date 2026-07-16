<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormResponse extends Model
{
    protected $guarded = [];

    protected $appends = ['respondent_label'];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function answers()
    {
        return $this->hasMany(FormAnswer::class);
    }

    /**
     * A human readable label for who submitted this response.
     */
    public function getRespondentLabelAttribute()
    {
        $name = $this->attributes['respondent_name'] ?? null;
        $email = $this->attributes['respondent_email'] ?? null;
        $type = $this->attributes['respondent_type'] ?? 'guest';

        $label = $name ?: ($email ?: 'Anonim');
        return $label . ' (' . $type . ')';
    }
}
