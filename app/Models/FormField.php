<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $guarded = [];

    protected $casts = [
        'options'     => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * Field types that store a list of choices in `options`.
     */
    const CHOICE_TYPES = ['select', 'radio', 'checkbox'];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function answers()
    {
        return $this->hasMany(FormAnswer::class);
    }
}
