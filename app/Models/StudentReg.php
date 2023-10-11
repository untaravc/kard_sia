<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentReg extends Model
{
    protected $guarded = [];
    protected $appends = ['status_label'];

    public function documents(){
        return $this->hasMany(Document::class, 'relation_id');
    }

    public function getStatusLabelAttribute()
    {
        if (isset($this->attributes['status'])) {
            switch ($this->attributes['status']) {
                case 0:
                    return 'Mendaftar';
                case 1:
                    return 'Diterima';
                case 2:
                    return 'Ditolak';
            }
        }
    }
}
