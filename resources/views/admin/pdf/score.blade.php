<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <title>Report {{$student['name']}}</title>
    <style>
        body {
            font-size: 10px;
        }

        .table-custom, .table-custom td, .table-custom th {
            border: 1px solid black;
            padding: 2px;
            font-size: 10px;
        }

        .table-custom {
            width: 100%;
        }

        .border-bottom-alt {
            border-bottom: 1px solid grey;
        }

        @media print {
            @page {
                size: portrait;
            }

            .pagebreak {
                page-break-before: always;
            }
        }

    </style>
</head>
<body>
<div class="border-bottom position-relative">
    <div style="width: 120px; height: 100px"
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
    LEMBAR RANGKUMAN NILAI
    <br>
    TAHAP {{$tahap}}
</div>
<div class="mb-2">
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
            <td>Tanggal Mulai Pendidikan Tahap I</td>
            <td>{{$date_start}}</td>
        </tr>
        <tr>
            <td>Tanggal Selesai Pendidikan Tahap I</td>
            <td>{{$date_end}}</td>
        </tr>
    </table>
</div>
<table class="table-custom text-center">
    <tr>
        {{--        <th rowspan="2">Sem</th>--}}
        <th rowspan="2">No</th>
        <th rowspan="2">Mata Kuliah/ Modul</th>
        <th colspan="2">NILAI</th>
    </tr>
    <tr>
        <th>Angka</th>
        <th>Markah</th>
    </tr>
    @foreach($stase_logs as $stase_log)
        <tr>
            <td>{{$loop->iteration}}</td>
            <td>{{$stase_log->stase->name}}</td>
            <td>{{$stase_log->log->point_average ?? ""}}</td>
            <td>
                @if($stase_log->log && $stase_log->log->point_average)
                    {{markah($stase_log->log->point_average)}}
                @endif
            </td>
        </tr>
    @endforeach
</table>
<div>
    <div>Catatan Pendidikan</div>
    <div></div>
</div>
<div>
    <div>Kesimpulan</div>
    <div></div>
</div>
<table style="width: 100%">
    <tr>
        <td class="pr-2" style="vertical-align: top;">
            <table class="table-custom">
                <tr>
                    <th>No</th>
                    <th>
                        Presentasi Laporan kasus /Refera/Karnas
                    </th>
                    <th>Nilai</th>
                </tr>
            </table>
        </td>
        <td class="pl-2" style="vertical-align: top;">
            <table class="table-custom">
                <tr>
                    <th colspan="3">
                        Ketidakhadiran
                    </th>
                </tr>
                <tr>
                    <td>Sakit</td>
                    <td></td>
                    <td>Hari</td>
                </tr>
                <tr>
                    <td>Izin</td>
                    <td></td>
                    <td>Hari</td>
                </tr>
                <tr>
                    <td>Tanpa Keterangan</td>
                    <td></td>
                    <td>Hari</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<table style="width: 100%">
    <tr>
        <td style="width: 33%">
            <table style="width: 100%">
                <tr>
                    <td class="text-center">Peserta Pendidikan</td>
                </tr>
                <tr>
                    <td style="height: 150px;"></td>
                </tr>
                <tr>
                    <td class="px-5">
                        <div class="border-bottom-alt"></div>
                    </td>
                </tr>
            </table>
        </td>
        <td style="width: 34%">
            <table style="width: 100%">
                <tr>
                    <td class="text-center">Dosen Pembimbing Akademik</td>
                </tr>
                <tr>
                    <td style="height: 150px;"></td>
                </tr>
                <tr>
                    <td class="px-5">
                        <div class="border-bottom-alt"></div>
                    </td>
                </tr>
            </table>
        </td>
        <td style="width: 33%">
            <table style="width: 100%">
                <tr>
                    <td class="text-center">Ketua Program Studi</td>
                </tr>
                <tr>
                    <td style="height: 150px;"></td>
                </tr>
                <tr>
                    <td class="px-5">
                        <div class="border-bottom-alt"></div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div class="pagebreak"></div>
</body>
</html>
