<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class File extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function openStaseTask(){
        return $this->belongsTo(OpenStaseTask::class);
    }

    public function staseTaskLog(){
        return $this->belongsTo(StaseTaskLog::class);
    }

    public function scopeMyOwn($q) {
        if(Auth::guard('student')->check()){
            return $q->whereStudentId(Auth::guard('student')
                ->id());
        }
    }

    public function type_list(){
        return [
          [
              'name'    => 'later',
              'desc'    => 'Permintaan Surat',
          ],
          [
              'name'    => 'document',
              'desc'    => 'Upload Dokumen',
          ],
        ];
    }
}
