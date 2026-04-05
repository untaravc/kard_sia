<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LetterParticipant;

class Letter extends Model
{
    protected $fillable = [
        'number',
        'auth_type',
        'auth_id',
        'date',
        'title',
        'subtitle',
        'intro',
        'body',
        'outro',
        'status',
        'token',
    ];

    public function participants()
    {
        return $this->hasMany(LetterParticipant::class);
    }
}
