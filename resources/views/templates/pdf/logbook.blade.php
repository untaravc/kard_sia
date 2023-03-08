<head>
    <title>{{$name}}</title>
    <style>
        #data-log, #data-log td, #data-log th {
            border: 1px solid black;
            vertical-align: top;
        }

        #data-log td, #data-log th {
            padding: 3px;
        }

        #data-log {
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    </style>
</head>
<body onload="window.print()">
{{--<body>--}}
<div style="width: 100%;">
    <div style="display: flex; justify-content: space-between; border: 1px solid lightgray; padding: 5px">
        <div style="display: flex">
            <div style="margin-right: 5px">
                <img src="/assets/images/logo-ugm.png" alt="logo ugm" style="width: 60px">
            </div>
            <div style="border-left: 1px solid; padding: 5px">
                <h4 style="margin: 0">Logbook Resident</h4>
                <h5 style="margin: 0">Departemen Kardiologi dan Kedokteran Vaskular</h5>
                <h5 style="margin: 0">FKKMK UGM</h5>
            </div>
        </div>
        <div>
            {!! QrCode::size(60)->generate(Request::url()); !!}
        </div>
    </div>


    <table style="font-size: 12px; margin-top: 10px">
        <tr>
            <td style="width: 120px">Nama</td>
            <td>:</td>
            <td>{{$student['name']}}</td>
        </tr>
        <tr>
            <td>Stase</td>
            <td>:</td>
            <td>{{$stase['name']}}</td>
        </tr>
        <tr>
            <td>Dicetak</td>
            <td>:</td>
            <td>{{date('d F Y H:i')}}</td>
        </tr>
    </table>

    @foreach($data as $logbook)
        <h4 style="text-align: center; margin: 5px">{{$logbook['name']}}</h4>
        @if(count($logbook['logbook']) > 0)
            <table style="width: 100%" id="data-log">
                <tr>
                    <th style="width: 20px">No</th>
                    <th style="width: 60px">Tanggal</th>
                    @foreach($logbook['parse_desc'] as $desc)
                        <th>{{$desc}}</th>
                    @endforeach
                    <th style="width: 100px">Supervisor</th>
                    <th style="width: 45px">Status</th>
                </tr>
                @foreach($logbook['logbook'] as $key => $log)
                    <tr>
                        <td>{{$key + 1}}</td>
                        <td>{{$log['date']}}</td>
                        @if(isset($logbook['parse_desc']['field_1']))
                            <td @if(strlen($log['field_1']) < 30 && $key == 0) style="width: 50px" @endif >{{$log['field_1']}}</td>
                        @endif
                        @if(isset($logbook['parse_desc']['field_2']))
                            <td @if(strlen($log['field_2']) < 30 && $key == 0) style="width: 50px" @endif >{{$log['field_2']}}</td>
                        @endif
                        @if(isset($logbook['parse_desc']['field_3']))
                            <td @if(strlen($log['field_3']) < 30 && $key == 0) style="width: 50px" @endif >{{$log['field_3']}}</td>
                        @endif
                        @if(isset($logbook['parse_desc']['field_4']))
                            <td @if(strlen($log['field_4']) < 30 && $key == 0) style="width: 50px" @endif >{{$log['field_4']}}</td>
                        @endif
                        <td>
                            @if($log['lecture'])
                                {{$log['lecture']['name_alt']}}
                            @endif
                        </td>
                        <td>
                            @if($log['status'] === 1) disetujui @endif

                            @if($log['status'] === 0 && $log['lecture'])
                                pending
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        @else
            <div style="font-style: italic; text-align: center">Belum ada data.</div>
        @endif

    @endforeach
</div>
</body>
