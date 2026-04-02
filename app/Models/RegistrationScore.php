<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationScore extends Model
{
    protected $guarded = [];

    public function getOriginUniversityMultiplierAttribute($value)
    {
        $data = strval(round($value * 100) / 100);

        return str_replace('.', ',', $data);
    }

    public function getQualityIpkAttribute($value)
    {
        $data = strval(round($value * 100) / 100);

        return str_replace('.', ',', $data);
    }

    public function getScoreEcgAttribute($value)
    {
        $data = strval(round($value * 100) / 100);

        return str_replace('.', ',', $data);
    }

    public function getScoreWrittenExamTotalAttribute($value)
    {
        $data = strval(round($value * 100) / 100);

        return str_replace('.', ',', $data);
    }

    public function getQualityWrittenExamAttribute($value)
    {
        $data = strval(round($value * 100) / 100);

        return str_replace('.', ',', $data);
    }

    public function getSubtotalScoreAttribute($value)
    {
        $data = strval(round($value * 100) / 100);

        return str_replace('.', ',', $data);
    }

    public function getScoreJournalAttribute($value)
    {
        $data = strval(round($value * 100) / 100);

        return str_replace('.', ',', $data);
    }

    public function getQualityJournalAttribute($value)
    {
        $data = strval(round($value * 100) / 100);

        return str_replace('.', ',', $data);
    }
}
