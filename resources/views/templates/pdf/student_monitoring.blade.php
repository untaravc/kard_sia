<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <title>Monitoring Peserta Didik</title>
    <style>
        body, table {
            font-size: 10px;
        }

        .table-custom, .table-custom td, .table-custom th {
            border: 1px solid black;
            padding: 2px 4px;
            font-size: 10px;
            vertical-align: middle;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .table-matrix th, .table-matrix td {
            text-align: center;
        }

        .table-matrix td.name-cell,
        .table-matrix th.name-cell {
            text-align: left;
            white-space: nowrap;
        }

        .muted { color: #555; }

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

        @media print {
            @page { size: landscape; }
            .toolbar { display: none; }
            .pagebreak { page-break-before: always; }
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
            <img src="/assets/images/logo-ugm.png" style="width: 70px" alt="">
        </td>
        <td style="vertical-align: middle; text-align: center;">
            <div style="font-weight: bold; font-size: 15px; line-height: 1.2;">
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

<div class="text-center mt-2 mb-2" style="font-weight: bold; font-size: 16px;">
    MONITORING PENILAIAN PESERTA DIDIK
</div>

<div class="text-center mb-3 muted">
    {{ $rows->count() }} peserta didik
    &nbsp;&bull;&nbsp; {{ $stases->count() }} stase
    &nbsp;&bull;&nbsp; Dicetak {{ now()->format('d M Y H:i') }}
</div>

{{-- Section: Score matrix (rows = students, columns = stases) --}}
<table class="table-custom table-matrix">
    <thead>
        <tr>
            <th style="width: 24px;">No</th>
            <th class="name-cell">Nama</th>
            <th style="width: 55px;">Angkatan</th>
            @foreach ($stases as $stase)
                <th title="{{ $stase->name }}">{{ $stase->alias ?: $stase->name }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td class="name-cell">{{ $row['name'] }}</td>
                <td>{{ $row['year'] ?: '-' }}</td>
                @foreach ($stases as $stase)
                    <td>{{ $row['scores'][$stase->id] ?? '-' }}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ $stases->count() + 3 }}">Tidak ada data peserta didik.</td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
