<html>
<head>
    <style>
        body {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
        }

        table {
            font-size: 12px;
        }

        .stase-title {
            background-color: lightgrey;
            padding: 5px;
            text-align: center;
        }

        @page {
            margin: 60px 60px;
        }

        header {
            position: fixed;
            top: -45px;
            left: 0;
            right: 0;
            height: 40px;
            font-size: 12px !important;
            color: #000;
            text-align: left;
        }

        header:after {
            position: absolute;
            content: '';
            height: 2px;
            bottom: 0;
            margin: 0;
            left: 0;
            right: 0;
            width: 100%;
            background: #790202;
        }

        footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 40px;
            font-size: 12px !important;
        }

        footer:after {
            position: absolute;
            content: '';
            height: 2px;
            bottom: 40px;
            margin: 0;
            left: 0;
            right: 0;
            width: 100%;
            background: #790202;
        }

        .pagenum:before {
            content: counter(page);
        }

        .pagebreak {
            page-break-before: always;
        }

        thead {
            display: table-header-group
        }

        tfoot {
            display: table-row-group
        }

        tr {
            page-break-inside: avoid
        }

        .table, .table td, .table th {
            border: 1px solid gray;
            border-collapse: collapse;
        }

        .table {
            width: 100%;
        }

        .table td, .table th {
            padding: 3px 3px;
            vertical-align: top;
        }

        .no-data {
            text-align: center;
            color: gray;
            padding: 5px;
            font-size: 12px
        }
        .bg-profile{
            width: 100%;
            padding-top: 125%;
            background-color: lightskyblue;
            background-position: center;
            background-size: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<header>
    <div style="margin-top: 20px">
        <i style="margin-left: 5px">Log Book PPDS :
            {{$student['name']}}
        </i>
        <span style="float: right"></span>
    </div>
</header>

<footer>
    <div style="display: flex; justify-content: space-between">
        <span>
            <i>Program Studi Jantung dan Pembuluh Darah, Departemen Kardiologi dan Kedokteran Vaskular, FKKMK, UGM</i>
        </span>
        <span class="pagenum" style="float: right"></span>
    </div>
</footer>

<main>
    @include('templates.logbook.intro')
    @foreach($stases as $stase)
        <h5 class="stase-title ">{{$stase['name']}}</h5>
        <table>
            <tr>
                <td>Tahap</td>
                <td>:</td>
                <td>{{substr($stase['desc'], -1, 1)}}</td>
            </tr>
            <tr>
                <td>Pelaksanaan</td>
                <td>:</td>
                <td>
                    @if($stase['stase_log'])
                        @if($stase['stase_log']['start_date'])
                            {{date('d M Y',strtotime($stase['stase_log']['start_date']))}} -
                        @endif
                        @if($stase['stase_log']['end_date'])
                            {{date('d M Y',strtotime($stase['stase_log']['end_date']))}}
                        @endif
                    @endif
                </td>
            </tr>
        </table>
        @foreach($stase['log_book'] as $key => $log_book)
            <div style="margin-top: 10px; font-size: 12px; margin-bottom: 3px">
                <b>{{$key + 1}}. {{$log_book['name']}}</b>
            </div>
            <table class="table">
                <thead>
                <tr>
                    <th style="max-width: 20px">No</th>
                    <th style="width: 60px">Tanggal</th>
                    @foreach($log_book['parse_desc'] as $desc)
                        <th>{{$desc}}</th>
                    @endforeach
                    <th style="max-width: 100px">Supervisor</th>
                    <th style="max-width: 45px">Status</th>
                </tr>
                </thead>
                @if(count($log_book['logbook']) > 0)
                    @foreach($log_book['logbook'] as $log_key => $log)
                        <tr>
                            <td style="text-align: center">{{$log_key + 1}}</td>
                            <td style="text-align: center">{{date('d M y',strtotime($log['date']))}}</td>
                            @if(isset($log_book['parse_desc']['field_1']))
                                <td @if(strlen($log['field_1']) < 30 && $key == 0) style="max-width: 80px" @endif >{{$log['field_1']}}</td>
                            @endif
                            @if(isset($log_book['parse_desc']['field_2']))
                                <td @if(strlen($log['field_2']) < 30 && $key == 0) style="max-width: 80px" @endif >{{$log['field_2']}}</td>
                            @endif
                            @if(isset($log_book['parse_desc']['field_3']))
                                <td @if(strlen($log['field_3']) < 30 && $key == 0) style="max-width: 80px" @endif >{{$log['field_3']}}</td>
                            @endif
                            @if(isset($log_book['parse_desc']['field_4']))
                                <td @if(strlen($log['field_4']) < 30 && $key == 0) style="max-width: 80px" @endif >{{$log['field_4']}}</td>
                            @endif
                            <td>
                                @if($log['lecture'])
                                    {{$log['lecture']['name_alt']}}
                                @endif
                            </td>
                            <td style="text-align: center">
                                @if($log['status'] === 1)
                                    <i>disetujui </i>
                                @endif
                                @if($log['status'] === 0 && $log['lecture'])
                                    -
                                @endif
                                @if($log['photo'])
                                    <div style="font-size: 11px; font-style: italic">
                                        <a target="_blank" href="https://sia.kardiologi-fkkmk.com/storage/{{$log['photo']}}">photo link</a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    @php
                        $count = count($log_book['parse_desc']) + 4;
                    @endphp
                    <tr>
                        <td style="text-align: center;" colspan="{{$count}}">
                            <i>Belum ada data</i>
                        </td>
                    </tr>
                @endif
            </table>
        @endforeach
        <div class="pagebreak"></div>
    @endforeach
</main>
</body>
</html>

