<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormAnswer extends Model
{
    protected $guarded = [];

    protected $appends = ['display_value'];

    public function response()
    {
        return $this->belongsTo(FormResponse::class, 'form_response_id');
    }

    public function field()
    {
        return $this->belongsTo(FormField::class, 'form_field_id');
    }

    /**
     * Checkbox answers are stored as a JSON array; render them as a comma
     * separated string, otherwise return the raw scalar value.
     */
    public function getDisplayValueAttribute()
    {
        $value = $this->attributes['value'] ?? null;
        if ($value === null || $value === '') {
            return '';
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return implode(', ', $decoded);
        }

        return $value;
    }
}
