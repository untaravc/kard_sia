
<!doctype html>
<html lang="en">
<head>
    @php
        $tipe = "Laporan Kasus";
        $stase_name = '';

        if(isset($data['activity']['stase'])){
            $stase_name = $data['activity']['stase']['name'];
        }

        if(in_array($stase_name, ['Referat Basic', 'Referat Clinic', 'Referat Thesis','Tesis'])){
            $tipe = '';
        }
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Undangan Presentasi {{$tipe}}</title>
</head>
<style>
    body {
        font-size: 12px;
    }

    .content {
        margin: 0 20px;
    }

    table tr td {
        vertical-align: top;
        font-size: 12px;
        padding: 0;
    }

    p{
        margin-top: 2px;
        margin-bottom: 2px;
    }
</style>
<body>
<div style="width: 100%;">
    @include('templates.pdf.header')
    <hr>
    <div class="content">
        <div style="text-align: right">
            <span>Yogyakarta, {{$data['date']}}</span>
        </div>
        <div>
            <table>
                <tr>
                    <td>Nomor</td>
                    <td>:</td>
                    <td>{{$data['number']}}</td>
                </tr>
                <tr>
                    <td>Perihal</td>
                    <td>:</td>
                    <td>
<<<<<<< HEAD
                        Undangan Presentasi Laporan Kasus
                        @if(isset($data['activity']['stase'])) {{$data['activity']['stase']['name']}} @endif
=======
                        Undangan Presentasi {{$tipe}}
                        @if(isset($data['activity']['stase']))
                            {{$data['activity']['stase']['name']}}
                        @endif
>>>>>>> 3a0ad237c4a32c5d3821fb143edff98da043fa9c
                        <br>
                        {{$data['activity']['speaker']}}
                    </td>
                </tr>
            </table>
        </div>
        <div>
            Yth.
            <ol style="margin: 1px">
                @foreach($data['all_staff'] as $staff)
                    <li>{{$staff}}</li>
                @endforeach
            </ol>
        </div>
        <div>
            <p>
                Mengharap kehadiran Sejawat pada presentasi seminar Laporan Kasus yang akan dilaksanakan:
            </p>
            <div style="margin-left: 40px">
                <table>
                    <tr>
                        <td style="width: 15%">hari, tgl</td>
                        <td>:</td>
                        <td>{{$data['day']}}, {{$data['date']}}</td>
                    </tr>
                    <tr>
                        <td>jam</td>
                        <td>:</td>
                        <td>{{substr($data['activity']['start_date'], 11,5)}}</td>
                    </tr>
                    <tr>
                        <td>tempat</td>
                        <td>:</td>
                        <td>{{$data['activity']['place']}}</td>
                    </tr>
                    <tr>
                        <td>acara</td>
                        <td>:</td>
                        <td>
                            Presentasi {{$data['type']}}
<<<<<<< HEAD
                            @if(isset($data['activity']['stase'])) {{$data['activity']['stase']['name']}} @endif
=======
                            @if(isset($data['activity']['stase']))
                                {{$data['activity']['stase']['name']}}
                            @endif
>>>>>>> 3a0ad237c4a32c5d3821fb143edff98da043fa9c
                        </td>
                    </tr>
                    <tr>
                        <td>judul</td>
                        <td>:</td>
                        <td>{{$data['activity']['title']}}</td>
                    </tr>
                </table>
            </div>
            <p>
                Demikian undangan ini kami sampaikan, atas perhatian dan kehadiran Sejawat kami mengucapkan terima
                kasih.
            </p>
            <div style="margin-top: 10px">
                <div style="margin-left: 200px; position: relative">
                    <div style="position: absolute; padding: 5px 0 0 0">
                        <img src="data:image/jpg;base64, {{$ttd}}" style="width: 65px">
                    </div>
                    <div style="position: absolute">
                        Ketua Program Studi <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <b>dr. Anggoro Budi Hartopo, Sp.PD, Sp.JP(K), MSc, PhD</b><br>
                        <u>NIP. 197807182010121004</u>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.print();
</script>
</body>
</html>
