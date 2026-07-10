<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationScore extends Model
{
    protected $fillable = [
        'registration_id',
        'period',
        'selection_path_multiplier',
        'pns_multiplier',
        'origin_university_type',
        'origin_university_multiplier',
        'quality_ipk',
        'quality_toefl',
        'quality_english',
        'quality_tpa',
        'score_written_exam',
        'score_ecg',
        'score_written_exam_total',
        'quality_written_exam',
        'score_journal',
        'quality_journal',
        'score_interview',
        'quality_interview',
        'score_mmpi',
        'subtotal_score',
        'total_score',
        'is_pass',
    ];

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
