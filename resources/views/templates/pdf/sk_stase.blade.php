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
        <div style="text-align: center">
            <h4 style="text-decoration: underline; margin-top: 10px; margin-bottom: 0">SURAT KETERANGAN</h4>
            <span>Nomor : {{$number}}</span>
        </div>
        <div>
            <p>
                Yang bertanda tangan dibawah ini :
            </p>
            <div style="margin-left: 40px">
                <table>
                    <tr>
                        <td style="width: 150px;">Nama</td>
                        <td>:</td>
                        <td>{{$kps['name']}}</td>
                    </tr>
                    <tr>
                        <td>NIP</td>
                        <td>:</td>
                        <td>{{$kps['nip']}}</td>
                    </tr>
                    <tr>
                        <td>Pangkat/Golongan</td>
                        <td>:</td>
                        <td>{{$kps['gol']}}</td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td>:</td>
                        <td>{{$kps['jabatan']}}</td>
                    </tr>
                </table>
            </div>
            <p>
                Fakultas Kedokteran, Kesehatan Masyarakat dan Keperawatan Universitas Gadjah Mada Menerangkan bahwa
            </p>
            <div style="margin-left: 40px">
                <table>
                    <tr>
                        <td style="width: 150px;">Nama</td>
                        <td>:</td>
                        <td>
                            @foreach($lectures as $lecture)
                                {{$lecture}} <br>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td>:</td>
                        <td>Dosen Program Studi Jantung dan Pembuluh Darah FK UGM</td>
                    </tr>
                </table>
            </div>
            <p>
                Pada semester {{$sem}} Th. Akademik {{$year}} telah melakukan Kegiatan pelaksanaan
                pendidikan untuk pendidikan dokter klinis – yaitu melakukan pengajaran untuk peserta pendidikan dokter
                melalui tindakan medik spesialistik terhadap mahasiswa
            </p>
            <div style="margin-left: 40px">
                <table>
                    <tr>
                        <td style="width: 150px;">Nama</td>
                        <td>:</td>
                        <td>{{$stase_log['student']['name']}}</td>
                    </tr>
                    <tr>
                        <td>NIM</td>
                        <td>:</td>
                        <td>{{$stase_log['student_profile']['code']}}</td>
                    </tr>
                    <tr>
                        <td>Stase</td>
                        <td>:</td>
                        <td>{{$stase_log['stase']['name']}}</td>
                    </tr>
                    <tr>
                        <td>Periode Stase</td>
                        <td>:</td>
                        <td>{{$start_str}} - {{$end_str}}</td>
                    </tr>
                </table>
            </div>
            <p>
                Demikian pernyataan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
            </p>
            <div style="margin-top: 30px">
                <div style="margin-left: 300px; position: relative">
                    <div style="position: absolute; padding: 30px 0 0 0">
                        <img src="data:image/jpg;base64, {{$ttd}}" style="width: 85px">
                    </div>
                    <div style="position: absolute">
                        Yogyakarta, {{$end_str}}<br>
                        Ketua Program Studi <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <b>dr. Anggoro Budi Hartopo, Sp.PD, Sp.JP(K)</b><br>
                        <u>NIP 197807182010121004</u>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
