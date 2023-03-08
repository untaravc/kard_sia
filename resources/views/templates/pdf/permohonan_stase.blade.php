<style>
    .header {
        font-size: 14px;
        color: #001c44;
    }

    .content {
        margin: 0 20px;
    }

    table tr td {
        vertical-align: top;
    }
</style>
<div style="width: 100%;">
    <table class="header">
        <tr>
            <td>
                <img src="data:image/jpg;base64, {{$logo}}" style="width: 85px">
            </td>
            <td style="width: 85%">
                UNIVERSITAS GADJAH MADA <br>
                FAKULTAS KEDOKTERAN, KESEHATAN MASYARAKAT, DAN KEPERAWATAN <br>
                DEPARTEMEN KARDIOLOGI DAN KEDOKTERAN VASKULAR <br>
                <b>PROGRAM SPESIALIS JANTUNG DAN PEMBULUH DARAH</b> <br>
                <small>Gedung Radioputro Lt 2 Sayap Barat Fakultas Kedokteran Kesehatan Masyarakat dan Keperawatan UGM
                    Jl. Farmako Sekip Utara Sleman</small>
            </td>
        </tr>
    </table>
    <hr>
    <div class="content">
        <div style="display: flex; justify-content: space-between">
            <div>
                <table>
                    <tr>
                        <td>Nomor</td>
                        <td>:</td>
                        <td>{{$number}}</td>
                    </tr>
                    <tr>
                        <td>Perihal</td>
                        <td>:</td>
                        <td><b>Permohonan Stase</b></td>
                    </tr>
                </table>
            </div>
            <div>

            </div>
        </div>
        <div style="margin-top: 10px">
            Kepada Yth. <br>
            {{$stase['lecture_name']}} <br>
            Pengampu Stase <br>
            {{$stase_log['stase']['name']}}
        </div>
        <div>
            <p>
                Dengan hormat, <br>
                Dalam rangka meningkatkan keahlian profesi dan kompetensi bagi peserta PPDS Jantung
                dan Pembuluh Darah FK UGM, bersama ini kami kirimkan residen:
            </p>
            <p style="text-align: center">
                <b>{{$stase_log['student']['name']}}</b>
            </p>
            <p>
                untuk melaksanakan stase <b>{{$stase_log['stase']['name']}}</b> selama {{$count_week}} minggu mulai tanggal {{$start_str}} sampai dengan {{$end_str}}.
                Adapun tugas selama stase adalah
                @foreach($stase_tasks as $key => $stase_task)
                    @if($key == (count($stase_tasks) - 2))
                        @if(count($stase_tasks) > 2)
                            {{$stase_task['name']}}, dan
                        @else
                            {{$stase_task['name']}} dan
                        @endif
                    @elseif($key == (count($stase_tasks) - 1))
                        {{$stase_task['name']}}.
                    @else
                        {{$stase_task['name']}},
                    @endif
                @endforeach
            </p>
            <p>
                Sehubungan dengan hal tersebut, kami mohon bantuan Sejawat untuk dapat mengijinkan yang bersangkutan bertugas sebagaimana mestinya.
            </p>
            <p>
                Demikian, atas perhatian dan kerja samanya kami ucapkan terima kasih.
            </p>
            <div style="margin-top: 30px">
                <div style="margin-left: 300px; position: relative">
                    <div style="position: absolute; padding: 30px 0 0 0">
                        <img src="data:image/jpg;base64, {{$ttd}}" style="width: 85px">
                    </div>
                    <div style="position: absolute">
                        Yogyakarta, {{$date_str}}
                        <br>
                        Ketua Program Studi <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <b>{{$kps['name']}}</b><br>
                        <u>NIP {{$kps['nip']}}</u>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
