<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Nicolaslopezj\Searchable\SearchableTrait;

class Activity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "name",
        "title",
        "place",
        "speaker",
        "start_date",
        "end_date",
        "note",
        "desc",
        "created_by",
        "status",
        "lecture_pembimbing",
        "lecture_penguji",
        "lecture_pengampu",
        "link",
        "passcode",
        "category",
        "type",
        "activity_id",
        "stase_id",
    ];
    protected $appends = [
        'is_author',
        'type_label',
        'category_label',
    ];

    public function lectures()
    {
        return $this->belongsToMany(Lecture::class, 'activity_lectures');
    }

    public function stase()
    {
        return $this->belongsTo(Stase::class);
    }

    public function activity_lectures()
    {
        return $this->hasMany(ActivityLecture::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'activity_students');
    }

    public function activity_students()
    {
        return $this->hasMany(ActivityStudent::class);
    }

    public function creator()
    {
        return $this->belongsTo(Student::class, 'created_by');
    }

    public function getLecturePengujiAttribute()
    {
        if ($this->attributes['lecture_penguji'] == null) {
            return [];
        }
        return $this->attributes['lecture_penguji'];
    }

    public function penguji()
    {
        if (!isset($this->attributes['lecture_penguji'])) {
            return [];
        }
        $ids = json_decode($this->attributes['lecture_penguji']);
        return Lecture::whereIn('id', $ids)->get();
    }

    public function getLecturePengampuAttribute()
    {
        if ($this->attributes['lecture_pengampu'] == null) {
            return [];
        }
        return $this->attributes['lecture_pengampu'];
    }

    public function pengampu()
    {
        if (!isset($this->attributes['lecture_pengampu'])) {
            return [];
        }
        $ids = json_decode($this->attributes['lecture_pengampu']);
        return Lecture::whereIn('id', $ids)->get();
    }

    public function getLecturePembimbingAttribute()
    {
        if ($this->attributes['lecture_pembimbing'] == null) {
            return [];
        }
        return $this->attributes['lecture_pembimbing'];
    }

    public function pembimbing()
    {
        if (!isset($this->attributes['lecture_pembimbing'])) {
            return [];
        }
        $ids = json_decode($this->attributes['lecture_pembimbing']);
        return Lecture::whereIn('id', $ids)->get();
    }

    public function getHasPasscodeAttribute()
    {
        if ($this->attribute['passcode']) {
            return true;
        }
        return false;
    }

    public function getIsAuthorAttribute()
    {
        if (Auth::guard('student')->check()) {
            $id = Auth::guard('student')->user()->id;
            if ($this->attributes['created_by'] == $id) {
                return true;
            }
        }
        return false;
    }

    public function scopeMyOwn($q)
    {
        if (Auth::guard('student')->check()) {
            return $q->whereCreatedBy(Auth::guard('student')->user()->id);
        }
        return $q;
    }

    public function getAttendedAttribute()
    {
        if (Auth::guard('student')->check()) {
            return ActivityStudent::whereActivityId($this->attributes['id'])
                ->whereStudentId(Auth::guard('student')->id())->first();
        } else if (Auth::guard('lecture')->check()) {
            return ActivityLecture::whereActivityId($this->attributes['id'])
                ->whereLectureId(Auth::guard('lecture')->id())->first();
        }
        return null;
    }

    public function getTypeLabelAttribute()
    {
        if (isset($this->attributes['type'])) {
            switch ($this->attributes['type']) {
                case 0:
                    return "Umum";
                case 1:
                    return 'Residen';
                case 2:
                    return 'Residen Wajib';
                case 3:
                    return 'Staff';
                default:
                    return 'no label';
            }
        }
        return null;
    }

    public function getCategoryLabelAttribute()
    {
        if (isset($this->attributes['category'])) {
            switch ($this->attributes['category']) {
                case 0:
                    return "Lain-lain";
                case 1:
                    return 'Stase';
                case 2:
                    return 'Tesis';
                case 3:
                    return 'Laporan Jaga';
                case 4:
                    return 'Laporan Kasus';
                case 5:
                    return 'Club';
                case 6:
                    return 'Ilmiah Divisi';
                default:
                    return 'no category';
            }
        }
        return null;
    }
}
