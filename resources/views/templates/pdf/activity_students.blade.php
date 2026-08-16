<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <title>Aktivitas {{ $student['name'] }}</title>
    <style>
        body, table {
            font-size: 10px;
        }

        .table-custom, .table-custom td, .table-custom th {
            border: 1px solid black;
            padding: 2px;
            font-size: 10px;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
        }

        @media print {
            @page {
                size: portrait;
            }

            .pagebreak {
                page-break-before: always;
            }
        }

        /* Keep a single row's content from splitting across pages */
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    </style>
</head>
<body>
{{-- Section: Header --}}
<table class="table-custom mb-2">
    <tr>
        <td style="width: 120px; vertical-align: middle; text-align: center; padding: 10px;">
            <img src="/assets/images/logo-ugm.png" style="width: 75px" alt="">
        </td>
        <td style="vertical-align: middle; text-align: center;">
            <div style="font-weight: bold; font-size: 16px; line-height: 1.2;">
                PROGRAM PENDIDIKAN DOKTER SPESIALIS I <br>
                JANTUNG DAN PEMBULUH DARAH
            </div>
            <div style="font-weight: bold; font-size: 11px;">
                {{ setting('app.department-name', 'DEPARTEMEN') }} <br>
                FKKMK {{ setting('app.university-name', 'UNIVERSITAS') }}
            </div>
        </td>
        <td style="width: 120px; vertical-align: middle; text-align: center; padding: 10px;">
            {!! QrCode::size(80)->generate(Request::fullUrl()) !!}
        </td>
    </tr>
</table>

<div class="text-center mt-2 mb-3" style="font-weight: bold; font-size: 18px;">
    AKTIVITAS PESERTA DIDIK
</div>

{{-- Section: Student information --}}
<div class="mb-3">
    <table class="table-custom">
        <tr>
            <td style="width: 30%">Nama Peserta Program Pendidikan</td>
            <td>{{ $student['name'] }}</td>
        </tr>
        <tr>
            <td>No Pokok Mahasiswa</td>
            <td>{{ $student_profile['code'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Email</td>
            <td>{{ $student['email'] }}</td>
        </tr>
        <tr>
            <td>Tahun Masuk</td>
            <td>{{ $student['year'] }}</td>
        </tr>
        <tr>
            <td>Periode</td>
            <td>{{ $start_date }} s/d {{ $end_date }}</td>
        </tr>
    </table>
</div>

{{-- Section: Daily presence & scientific agenda --}}
@php
    $dayNames = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];
@endphp
<table class="table-custom">
    <tr class="text-center">
        <th style="width: 25px">No</th>
        <th style="width: 70px">Hari</th>
        <th style="width: 80px">Tanggal</th>
        <th style="width: 70px">Jam Datang</th>
        <th style="width: 70px">Jam Pulang</th>
        <th style="width: 50px">Ilmiah</th>
        <th>Keterangan Agenda Ilmiah</th>
    </tr>
    @foreach($days as $day)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td>{{ $dayNames[date('l', strtotime($day['date']))] ?? '' }}</td>
            <td>{{ date('d-m-Y', strtotime($day['date'])) }}</td>
            <td class="text-center">{{ $day['presence'] && $day['presence']->checkin ? date('H:i', strtotime($day['presence']->checkin)) : '-' }}</td>
            <td class="text-center">{{ $day['presence'] && $day['presence']->checkout ? date('H:i', strtotime($day['presence']->checkout)) : '-' }}</td>
            <td class="text-center">{{ count($day['activities']) }}</td>
            <td>
                @foreach($day['activities'] as $activityStudent)
                    {{ $loop->iteration }}. {{ $activityStudent->activity->name ?? '' }} - {{ $activityStudent->activity->title ?? '' }}@if(!$loop->last)<br>@endif
                @endforeach
            </td>
        </tr>
    @endforeach
</table>

</body>
</html>
