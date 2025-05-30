<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Presensi {{$student['name']}}</title>
</head>
<body>
<style>
    table {
        font-size: 12px;
    }

    .table-custom,
    .table-custom td,
    .table-custom th {
        border: 1px solid black;
        padding: 2px;
        font-size: 12px;
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }

    input.checkbox {
        width: 10px;
        height: 10px;
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

    h3, h4 {
        margin-bottom: 2px;
        margin-top: 1px;
    }

    .line {
        height: 3px;
        border-top: 2px solid black;
        border-bottom: 1px solid black;
        margin: 5px 0 10px 0;
    }
</style>
<div style="display: flex; align-items: center">
    <div>
        <img style="height: 80px; width: auto"
             src="https://firebasestorage.googleapis.com/v0/b/unt-dev.firebasestorage.app/o/KardiologiFkkmk%2FAssets%2Flogo_ugm_mono.png?alt=media&token=ec945130-cda1-440e-b750-62e557c3e6a2"
             alt="">
    </div>
    <div style="text-align: center; margin-bottom: 5px; width: 100%">
        <h3>Laporan Presensi Program Pendidikan Dokter Spesialis</h3>
        <h4>Departemen Kardiologi dan Kedokteran Vaskular</h4>
        <h4>Fakultas Kedokteran, Kesehatan Masyarakat, dan Keperawatan</h4>
        <h4>Universitas Gadjah Mada</h4>
    </div>
</div>
<div class="line"></div>

<div>
    <table>
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td>{{$student['name']}}</td>
        </tr>
        <tr>
            <td>Periode Masuk</td>
            <td>:</td>
            <td>{{$student['year']}}</td>
        </tr>
    </table>
</div>

<table class="table-custom">
    <tr>
        <th rowspan="2"><b>Hari</b></th>
        <th rowspan="2"><b>Tanggal</b></th>
        <th colspan="2"><b>Presensi Harian</b></th>
        <th rowspan="2"><b>Presensi Agenda</b></th>
    </tr>
    <tr>
        <th>
            Masuk
        </th>
        <th>
            Keluar
        </th>
    </tr>
    @foreach($data as $item)
        <tr>
            <td>{{$item['day']}}</td>
            <td>{{$item['date']}}</td>
            <td>
                @if(isset($item['presence']))
                    {{$item['presence']['checkin']}}
                @endif
            </td>
            <td>
                @if(isset($item['presence']))
                    {{$item['presence']['checkout']}}
                @endif
            </td>
            <td>
                @if(isset($item['activities']))
                    @foreach($item['activities'] as $activity)
                        {{$activity['name']}},
                    @endforeach
                @endif
            </td>
        </tr>
    @endforeach
</table>
<div style="display: flex; justify-content: space-between; margin-top: 5px">
    <div style="color: gray; font-size: 12px">
        <i>Dicetak: {{date('Y-m-d H:i')}}</i>
    </div>
    <div style="color: gray; font-size: 12px; text-align: right;">
        <div style="color: gray; font-size: 12px">
            <i>Dokumen ini adalah salinan dari dokumen yang tersedia secara daring.</i>
        </div>
        <div>
            {!! QrCode::size(100)->generate(Request::url()); !!}
        </div>
    </div>
</div>
</body>
</html>
