<?php
use Illuminate\Http\Request;
use App\Models\Lecture;
if (!function_exists('input_req')) {
    function auth_dosen($params = 'data') {
        $token = request()->header('Authorization');
        $dosen = Lecture::whereToken($token)->first();
        if($dosen){
            switch ($params){
                case 'data':
                    return $dosen;
                case 'token':
                    return $dosen->token;
                case 'id':
                    return $dosen->id;
            }
        }
        return null;
    }
}

if (!function_exists('date_indo_str')) {
    function date_indo_str($date) {
        $str = (int) substr($date, 8, 2);
        switch (date('m', strtotime($date))){
            case '01': $month = 'Januari'; break;
            case '02': $month = 'Februari'; break;
            case '03': $month = 'Maret'; break;
            case '04': $month = 'April'; break;
            case '05': $month = 'Mei'; break;
            case '06': $month = 'Juni'; break;
            case '07': $month = 'Juli'; break;
            case '08': $month = 'Agustus'; break;
            case '09': $month = 'September'; break;
            case '10': $month = 'Oktober'; break;
            case '11': $month = 'November'; break;
            default : $month = 'Desember'; break;
        }

        $str .= ' ' . $month . ' ' . substr($date, 0, 4);

        return $str;
    }
}

if (!function_exists('letter_number')) {
    function letter_stase_number($prefix, $stase_log) {
        $date = $stase_log['start_date'] ?? date('Y-m-d');
        $this_year = \App\Models\StaseLog::whereYear('created_at', $date)
            ->pluck('id')->toArray();
        $number = array_search($stase_log['id'], $this_year);

        $mask = strlen($number) < 3 ? str_repeat('0', 3 - strlen($number)) . $number : $number;
        $rom = '';
        switch (substr($date, 5, 2)){
            case '01': $rom = 'I'; break;
            case '02': $rom = 'II'; break;
            case '03': $rom = 'III'; break;
            case '04': $rom = 'IV'; break;
            case '05': $rom = 'V'; break;
            case '06': $rom = 'VI'; break;
            case '07': $rom = 'VII'; break;
            case '08': $rom = 'VIII'; break;
            case '09': $rom = 'IX'; break;
            case '10': $rom = 'X'; break;
            case '11': $rom = 'XI'; break;
            case '12': $rom = 'XII'; break;
        }

        return $mask . '/' . $prefix . '/'. $rom .'/SIA.KARD/' .  substr($date, 0, 4);
    }
}


