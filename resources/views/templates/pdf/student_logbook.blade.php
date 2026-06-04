<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <title>Student Logbook {{ $student['name'] }}</title>
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

        .stase-block {
            page-break-inside: auto;
        }

        /* Keep a single row's content from splitting across pages */
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        @media print {
            @page {
                size: portrait;
            }

            .pagebreak {
                page-break-before: always;
            }

            /* Each stase starts on a new page (first one stays in place) */
            .stase-block + .stase-block {
                page-break-before: always;
            }
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
                DEPARTEMEN KARDIOLOGI DAN KEDOKTERAN VASKULAR <br>
                FKKMK UNIVERSITAS GADJAH MADA
            </div>
        </td>
        <td style="width: 120px; vertical-align: middle; text-align: center; padding: 10px;">
            {!! QrCode::size(80)->generate(Request::fullUrl()) !!}
        </td>
    </tr>
</table>

<div class="text-center mt-2 mb-3" style="font-weight: bold; font-size: 18px;">
    LOGBOOK PESERTA DIDIK
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
    </table>
</div>

{{-- Section: Logbook skills (count / target) --}}
@if(count($logbook_skills) > 0)
    <div class="font-weight-bold mb-1">Keterampilan</div>
    <table class="table-custom mb-3">
        <tr class="text-center">
            <th style="width: 25px">No</th>
            <th>Keterampilan</th>
            <th>Stase</th>
            <th style="width: 70px">Capaian</th>
            <th style="width: 70px">Target</th>
        </tr>
        @foreach($logbook_skills as $skill)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $skill->name }}</td>
                <td>{{ $skill->stase_name }}</td>
                <td class="text-center">{{ $skill->count ?? 0 }}</td>
                <td class="text-center">{{ $skill->desc ?? '-' }}</td>
            </tr>
        @endforeach
    </table>
@endif

{{-- Section: Logbook detail per stase --}}
@php($stase_number = 0)
@foreach($stases as $stase)
    @if(count($stase->logbook_sections) > 0)
        @php($stase_number++)
        <div class="stase-block">
        <div class="font-weight-bold mt-3 mb-2" style="font-size: 14px">
            {{ $stase_number }}. {{ $stase->name }}
        </div>

        @foreach($stase->logbook_sections as $section)
            {{-- Parent (form option) as title --}}
            <div class="font-weight-bold mb-1">
                {{ $stase_number }}.{{ $loop->iteration }} {{ $section->name }}
            </div>

            @if(count($section->data) > 0)
                <table class="table-custom mb-3">
                    <tr class="text-center">
                        <th style="width: 25px">No</th>
                        <th style="width: 70px">Tanggal</th>
                        @foreach($section->parse_desc ?? [] as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                        <th style="width: 110px">Supervisor</th>
                    </tr>
                    @foreach($section->data as $log)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $log->date }}</td>
                            @foreach($section->parse_desc ?? [] as $field => $label)
                                <td>{{ $log[$field] ?? '' }}</td>
                            @endforeach
                            <td>{{ $log->lecture ? $log->lecture->name_alt : '' }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <div class="mb-3" style="font-style: italic">Belum ada data.</div>
            @endif
        @endforeach
        </div>
    @endif
@endforeach
</body>
</html>
