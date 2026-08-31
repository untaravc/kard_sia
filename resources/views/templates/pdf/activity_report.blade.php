<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <title>Laporan Kegiatan</title>
    <style>
        body, table {
            font-size: 10px;
        }

        .table-custom, .table-custom td, .table-custom th {
            border: 1px solid black;
            padding: 2px 4px;
            font-size: 10px;
            vertical-align: top;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .section-title {
            font-weight: bold;
            margin: 8px 0 3px;
        }

        .activity-block {
            margin-bottom: 18px;
            page-break-inside: avoid;
        }

        .activity-heading {
            font-weight: bold;
            font-size: 12px;
            margin: 10px 0 4px;
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
            @page { size: portrait; }
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
    LAPORAN KEGIATAN
</div>

<div class="text-center mb-3 muted">
    Periode: {{ $start_date ? \Illuminate\Support\Carbon::parse($start_date)->format('d M Y') : '-' }}
    s/d {{ $end_date ? \Illuminate\Support\Carbon::parse($end_date)->format('d M Y') : '-' }}
    &nbsp;&bull;&nbsp; {{ $activities->count() }} kegiatan
    &nbsp;&bull;&nbsp; Dicetak {{ now()->format('d M Y H:i') }}
</div>

@php
    $fmtRange = function ($start, $end) {
        if (!$start) {
            return '-';
        }
        $s = strtotime($start);
        $out = date('d M Y H:i', $s);
        if ($end) {
            $e = strtotime($end);
            $out .= date('Y-m-d', $s) === date('Y-m-d', $e)
                ? ' - ' . date('H:i', $e)
                : ' - ' . date('d M Y H:i', $e);
        }
        return $out;
    };
@endphp

@forelse ($activities as $activity)
    <div class="activity-block">
        <div class="activity-heading">
            {{ $loop->iteration }}. {{ $activity->name ?: '(Tanpa nama)' }}
        </div>

        {{-- Detail activity --}}
        <table class="table-custom">
            @if ($activity->title)
                <tr><td style="width: 150px;">Judul</td><td>{{ $activity->title }}</td></tr>
            @endif
            @if ($activity->speaker)
                <tr><td>Pembicara</td><td>{{ $activity->speaker }}</td></tr>
            @endif
            @if ($activity->place)
                <tr><td>Tempat</td><td>{{ $activity->place }}</td></tr>
            @endif
            <tr><td>Waktu</td><td>{{ $fmtRange($activity->start_date, $activity->end_date) }}</td></tr>
            @if ($activity->category_label)
                <tr><td>Kategori</td><td>{{ $activity->category_label }}</td></tr>
            @endif
            @if ($activity->type_label)
                <tr><td>Tipe</td><td>{{ $activity->type_label }}</td></tr>
            @endif
            @if ($activity->status)
                <tr><td>Status</td><td>{{ $activity->status }}</td></tr>
            @endif
            @if ($activity->desc)
                <tr><td>Deskripsi</td><td>{{ $activity->desc }}</td></tr>
            @endif
            @if ($activity->note)
                <tr><td>Catatan</td><td>{{ $activity->note }}</td></tr>
            @endif
        </table>

        {{-- Presence of lecture: from activities.lecture_pembimbing / _penguji / _pengampu --}}
        @php
            $staff = collect()
                ->concat($activity->pembimbing_list->map(fn ($l) => ['role' => 'Pembimbing', 'lecture' => $l]))
                ->concat($activity->penguji_list->map(fn ($l) => ['role' => 'Penguji', 'lecture' => $l]))
                ->concat($activity->pengampu_list->map(fn ($l) => ['role' => 'Pengampu', 'lecture' => $l]));
        @endphp
        <div class="section-title">Kehadiran Dosen / Staf ({{ $staff->count() }})</div>
        <table class="table-custom">
            <tr class="text-center">
                <th style="width: 25px;">No</th>
                <th style="width: 110px;">Peran</th>
                <th>Nama</th>
            </tr>
            @forelse ($staff as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row['role'] }}</td>
                    <td>{{ optional($row['lecture'])->name_alt ?: (optional($row['lecture'])->name ?? '(Tidak ditemukan)') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center muted">Tidak ada dosen terdaftar.</td></tr>
            @endforelse
        </table>

        {{-- Presence of student: from activity_students (hidden when none) --}}
        @if ($activity->activity_students->count())
            <div class="section-title">Kehadiran Peserta Didik ({{ $activity->activity_students->count() }})</div>
            <table class="table-custom">
                <tr class="text-center">
                    <th style="width: 25px;">No</th>
                    <th>Nama</th>
                    <th style="width: 110px;">Waktu Hadir</th>
                </tr>
                @foreach ($activity->activity_students as $presence)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ optional($presence->student)->name ?? '(Peserta tidak ditemukan)' }}</td>
                        <td class="text-center">
                            {{ $presence->created_at ? date('d M Y H:i', strtotime($presence->created_at)) : '-' }}
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>
@empty
    <p class="text-center muted" style="margin-top: 24px;">Tidak ada kegiatan pada periode ini.</p>
@endforelse

</body>
</html>
