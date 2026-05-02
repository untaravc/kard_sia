<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use SoftDeletes;

    protected $casts = [
        'ip_s1' => 'decimal:2',
        'ip_profession' => 'decimal:2',
        'ip_commulative' => 'decimal:2',
    ];

    protected $fillable = [
        "registration_period",
        "name",
        "email",
        "phone",
        "image_uri",
        "email_confirmed_at",
        "password",
        "test_order",
        "selection_path",
        "working_status",
        "video_url",
        "education_permit",
        "education_permit_signer",
        "education_permit_url",
        "nik",
        "gender",
        "birth_place",
        "birth_date",
        "origin_address",
        "marital_status",
        "religion",
        "origin_university_status",
        "origin_university_address",
        "origin_university_accreditation",
        "statement_letter_url",
        "graduate_url",
        "graduate_reason",
        "graduate_place",
        "profession_finish_year",
        "profession_init_year",
        "s1_finish_year",
        "s1_init_year",
        "ip_s1",
        "ip_profession",
        "ip_commulative",
        "origin_university",
        "str_end_date",
        "str_url",
        "acls_end_date",
        "acls_url",
        "score_acept",
        "score_tpa",
        "father_name",
        "father_religion",
        "father_job",
        "father_address",
        "mother_name",
        "mother_religion",
        "mother_job",
        "spouse_name",
        "spouse_birth_place",
        "spouse_job",
        "spouse_address",
        "spouse_birth_date",
        "wedding_date",
        "institution_user_id",
        "institution_name",
        "institution_address",
        "institution_city",
        "status",
        "reset_password_token",
    ];

    protected $hidden = [
        'password',
    ];

    protected $appends = ['age', 'status_label'];

    public function details()
    {
        return $this->hasMany(RegistrationDetail::class);
    }

    public function score()
    {
        return $this->hasOne(RegistrationScore::class);
    }

    public function getAgeAttribute()
    {
        if (isset($this->birth_date)) {
            return \Carbon\Carbon::parse($this->birth_date)->age;
        }

        return null;
    }

    /**
     * 100 = Pengisian Pendaftaran
     * 101 = Pengisian Pendaftaran Selesai
     * 200 = Lolos Administrasi
     * 201 = Tidak Lolos Administrasi
     * 300 = Lolos Ujian Tulis - Jurnal
     * 301 = Tidak Lolos Ujian Tulis - Jurnal
     * 400 = Diterima
     * 401 = Tidak Lolos Ujian Wawancara
     * 500 = Dibatalkan
     */
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 100:
                return 'Pengisian Pendaftaran';
            case 101:
                return 'Pengisian Pendaftaran Selesai';
            case 200:
                return 'Lolos Administrasi';
            case 201:
                return 'Tidak Lolos Administrasi';
            case 300:
                return 'Lolos Ujian Tulis - Jurnal';
            case 301:
                return 'Tidak Lolos Ujian Tulis - Jurnal';
            case 400:
                return 'Diterima';
            case 401:
                return 'Tidak Loloas Ujian Wawancara';
            case 500:
                return 'Dibatalkan';
        }

        return null;
    }
}
