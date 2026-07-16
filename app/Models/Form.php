<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $appends = ['public_url', 'visibility_label', 'status_label'];

    /**
     * Questions that belong to this form, ordered as arranged in the builder.
     */
    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('position');
    }

    /**
     * Submissions received for this form.
     */
    public function responses()
    {
        return $this->hasMany(FormResponse::class);
    }

    public function getPublicUrlAttribute()
    {
        if (isset($this->attributes['slug'])) {
            return url('/form/' . $this->attributes['slug']);
        }
        return null;
    }

    public function getVisibilityLabelAttribute()
    {
        $map = [
            'public' => 'Publik',
            'auth'   => 'Harus Login',
        ];
        $visibility = $this->attributes['visibility'] ?? 'public';
        return $map[$visibility] ?? $visibility;
    }

    public function getStatusLabelAttribute()
    {
        $map = [
            0 => 'Draft',
            1 => 'Publish',
            2 => 'Ditutup',
        ];
        $status = $this->attributes['status'] ?? 0;
        return $map[$status] ?? $status;
    }
}
