<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <title>Penilaian {{ $student['name'] }}</title>
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
    PENILAIAN PESERTA DIDIK
</div>

{{-- Section: Student information --}}
<div class="mb-3">
    <table class="table-custom">
        <tr>
            <td style="width: 30%">Nama Peserta Program Pendidikan</td>
            <td>{{ $student['name'] }}</td>
        </tr>
        {{-- <tr>
            <td>No Pokok Mahasiswa</td>
            <td>{{ $student_profile['code'] ?? '' }}</td>
        </tr> --}}
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

{{-- Section: Stase task log scores --}}
<table class="table-custom">
    <tr class="text-center">
        <th style="width: 22%">Stase</th>
        <th style="width: 28%">Tugas Stase</th>
        <th style="width: 80px">Tanggal</th>
        <th style="width: 60px">Nilai</th>
        <th>Penilai</th>
    </tr>
    @foreach($stases as $stase)
        @unless($loop->first)
            <tr><td colspan="5">&nbsp;</td></tr>
        @endunless
        @php
            $staseShown = false;
            $staseRowSpan = 0;
            foreach ($stase->staseTasks as $st) {
                $staseRowSpan += max(count($st->logs), 1);
            }
        @endphp
        @foreach($stase->staseTasks as $staseTask)
            @php($taskName = $staseTask->name ?? $staseTask->task->name )
            @if(count($staseTask->logs) > 0)
                @foreach($staseTask->logs as $log)
                    <tr>
                        @unless($staseShown)
                            <td rowspan="{{ $staseRowSpan }}" style="vertical-align: top;">
                                <div style="font-weight: bold;">{{ $stase->name }}</div>
                                @if($stase->stase_log)
                                    <div style="font-size: 9px;">
                                        {{ $stase->stase_log->start_date ? date('d M Y', strtotime($stase->stase_log->start_date)) : '-' }}
                                        s/d
                                        {{ $stase->stase_log->end_date ? date('d M Y', strtotime($stase->stase_log->end_date)) : '-' }}
                                    </div>
                                @endif
                            </td>
                            @php($staseShown = true)
                        @endunless
                        <td>{{ $loop->first ? $taskName : '' }}</td>
                        <td class="text-center">{{ $log->date ? date('d M Y', strtotime($log->date)) : '-' }}</td>
                        <td class="text-center">{{ $log->point_average ?? '-' }}</td>
                        <td>{{ $log->lecture->name_alt ?? '' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    @unless($staseShown)
                        <td rowspan="{{ $staseRowSpan }}" style="vertical-align: top;">
                            <div style="font-weight: bold;">{{ $stase->name }}</div>
                            @if($stase->stase_log)
                                <div style="font-size: 9px;">
                                    {{ $stase->stase_log->start_date ? date('d M Y', strtotime($stase->stase_log->start_date)) : '-' }}
                                    s/d
                                    {{ $stase->stase_log->end_date ? date('d M Y', strtotime($stase->stase_log->end_date)) : '-' }}
                                </div>
                            @endif
                        </td>
                        @php($staseShown = true)
                    @endunless
                    <td>{{ $taskName }}</td>
                    <td class="text-center">-</td>
                    <td class="text-center">-</td>
                    <td></td>
                </tr>
            @endif
        @endforeach
    @endforeach
</table>

</body>
</html>
