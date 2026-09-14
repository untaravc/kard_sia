<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/css/app.css">
    <title>Hasil Penilaian {{ $student->name }}</title>
    <style>
        body, table {
            font-size: 10px;
        }

        .table-custom, .table-custom td, .table-custom th {
            border: 1px solid black;
            padding: 2px 4px;
            font-size: 10px;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
        }

        .toolbar {
            margin-bottom: 14px;
        }

        .toolbar button {
            font-size: 12px;
            padding: 6px 16px;
            border: 1px solid #333;
            border-radius: 4px;
            background: #111;
            color: #fff;
            cursor: pointer;
        }

        .toolbar button.secondary {
            background: #fff;
            color: #111;
        }

        .sign-block {
            width: 100%;
            margin-top: 32px;
            border-collapse: collapse;
        }

        .sign-spacer {
            width: 55%;
        }

        .sign-cell {
            width: 45%;
            line-height: 1.6;
            text-align: center;
        }

        .sign-qr {
            padding: 8px 0 6px;
        }

        .sign-qr img, .sign-qr svg {
            display: block;
            width: 100px;
            height: 100px;
            margin: 0 auto;
        }

        @media print {
            @page {
                size: portrait;
            }

            .toolbar {
                display: none;
            }
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    </style>
</head>
<body>

<div class="toolbar">
    <button type="button" onclick="window.print()">Print</button>
    <button type="button" class="secondary" onclick="window.close()">Tutup</button>
</div>

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
    </tr>
</table>

<div class="text-center mt-2 mb-3" style="font-weight: bold; font-size: 18px;">
    HASIL PENILAIAN TUGAS STASE
</div>

{{-- Section: Student / task information --}}
<table class="table-custom mb-2">
    <tr>
        <td style="width: 30%">Nama Peserta Program Pendidikan</td>
        <td>{{ $student->name }}</td>
    </tr>
    <tr>
        <td>Stase</td>
        <td>{{ $stase_task_log->stase->name ?? '-' }}</td>
    </tr>
    <tr>
        <td>Nama Tugas</td>
        <td>{{ $stase_task_log->task->name ?? '-' }}</td>
    </tr>
    @if($stase_task_log->title)
        <tr>
            <td>Judul</td>
            <td>{{ $stase_task_log->title }}</td>
        </tr>
    @endif
    <tr>
        <td>Tanggal Penilaian</td>
        <td>{{ $tanggal_penilaian ?: '-' }}</td>
    </tr>
    <tr>
        <td>Dinilai Oleh</td>
        <td>{{ $stase_task_log->lecture->name_alt ?? ($stase_task_log->lecture->name ?? '-') }}</td>
    </tr>
</table>

{{-- Section: Score breakdown --}}
<table class="table-custom">
    <tr class="text-center">
        <th style="width: 30px;">No</th>
        <th>Komponen Penilaian</th>
        <th style="width: 80px;">Nilai</th>
    </tr>
    @forelse($stase_task_log->staseTaskLogPoint as $point)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td>{{ $point->taskDetail->name ?? '-' }}</td>
            <td class="text-center">{{ $point->score ?? '-' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="text-center">Tidak ada rincian komponen penilaian.</td>
        </tr>
    @endforelse
    <tr>
        <td colspan="2" style="font-weight: bold; text-align: right;">Nilai Rata-rata</td>
        <td class="text-center" style="font-weight: bold;">{{ $stase_task_log->point_average ?? '-' }}</td>
    </tr>
    @if($stase_task_log->symbol)
        <tr>
            <td colspan="2" style="font-weight: bold; text-align: right;">Simbol</td>
            <td class="text-center" style="font-weight: bold;">{{ $stase_task_log->symbol }}</td>
        </tr>
    @endif
</table>

@if($stase_task_log->conclusion)
    <div class="mt-2">
        <b>Kesimpulan:</b> {{ $stase_task_log->conclusion }}
    </div>
@endif

{{-- Section: Signature. The QR replaces the scanned signature image: it
     encodes this same page's URL, so scanning the printed copy re-opens
     the exact same document for verification. --}}
<table class="sign-block">
    <tr>
        <td class="sign-spacer"></td>
        <td class="sign-cell">
            <div>Yogyakarta, {{ $tanggal_penilaian ?: date_indo_str(now()->format('Y-m-d')) }}</div>
            <div>Dosen Penilai</div>
            <div class="sign-qr">
                {!! QrCode::size(100)->generate(Request::fullUrl()) !!}
            </div>
            <div><b>{{ $stase_task_log->lecture->name_alt ?? ($stase_task_log->lecture->name ?? '-') }}</b></div>
        </td>
    </tr>
</table>

</body>
</html>
