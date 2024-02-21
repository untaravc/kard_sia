<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/css/app.css">
    <title>Stase {{$stase['name']}} | {{$student['name']}}</title>
    <style>
        body {
            font-size: 10px;
        }

        .table-custom, .table-custom td, .table-custom th {
            border: 1px solid black;
            padding: 2px;
        }

        .table-custom {
            width: 100%;
        }

        @media print {
            @page {
                size: landscape
            }
        }
    </style>
</head>
<body>
<div class="border-bottom position-relative">
    <div style="width: 120px; height: 120px"
         class="position-absolute margin-auto d-flex justify-content-center align-items-center">
        <img src="/assets/images/logo-ugm.png" style="width: 75px" alt="">
    </div>
    <div class="p-2 text-center">
        <div class="font-weight-bold">
            PROGRAM PENDIDIKAN DOKTER SPESIALIS I <br>
            JANTUNG DAN PEMBULUH DARAH
        </div>
        <div>
            DEPARTEMEN KARDIOLOGI DAN KEDOKTERAN VASKULAR <br>
            FKKMK UNIVERSITAS GADJAH MADA
        </div>
        <div>
            Sekretariat : RSUP Dr. Sardjito, Jl. Kesehatan No.1 Yogyakarta <br>
            Telp. 0274 – 587333, ext. 364 Fax. 0274– 547783 e-mail : kardiologi_fkugm@yahoo.co.id
        </div>
    </div>
</div>
<div class="text-center font-weight-bold mt-2 mb-3">
    LEMBAR RANGKUMAN
    <br>
    MATERI PENERAPAN AKADEMIK
</div>
<div class="mb-5">
    <table class="table-custom">
        <tr>
            <td style="width: 30%">Nama Peserta Program Pendidikan</td>
            <td>{{$student['name']}}</td>
        </tr>
        <tr>
            <td>No Pokok Mahasiswa</td>
            <td>{{$student_profile['code'] ?? ''}}</td>
        </tr>
        <tr>
            <td>Tanggal Mulai Stase</td>
            <td>{{$stase_log['start_date']}}</td>
        </tr>
        <tr>
            <td>Tanggal Selesai Stase</td>
            <td>{{$stase_log['end_date']}}</td>
        </tr>
    </table>
</div>
<table class="table-custom text-center">
    <tr>
        <th rowspan="2">NO</th>
        <th rowspan="2">Materi Penerapan Akademik</th>
        <th rowspan="2">Mata Kuliah/ Modul / Stase</th>
        <th rowspan="2">Judul</th>
        <th rowspan="2">Tanggal Ujian</th>
        <th rowspan="2">Penilai</th>
        <th colspan="2">NILAI</th>
    </tr>
    <tr>
        <th>Angka</th>
        <th>Markah</th>
    </tr>
    @foreach($stase_task_logs as $log)
        <tr>
            <td>{{$loop->iteration}}</td>
            <td class="text-left">{{$log->task->name}}</td>
            <td class="text-left">{{$stase->name}}</td>
            <td class="text-left">{{$log->title}}</td>
            <td class="text-left">
                @if($log->plan)
                    {{date('d M Y', strtotime($log->plan))}}
                @endif
            </td>
            <td class="text-left">{{$log->lecture->name_alt ?? ""}}</td>
            <td class="text-left">{{$log->point_average}}</td>
            <td class="text-left">
                @if($log->point_average)
                    {{markah($log->point_average)}}
                @endif
            </td>
        </tr>
    @endforeach
</table>
</body>
</html>
