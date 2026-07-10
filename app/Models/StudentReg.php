<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentReg extends Model
{
    protected $fillable = [
        'name',
        'photo',
        'phone',
        'email',
        'address',
        'cv',
        'permission',
        'written_exam',
        'notes',
        'psychology',
        'health',
        'interview',
        'journal',
        'status',
        'date',
    ];
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
