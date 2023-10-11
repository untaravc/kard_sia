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
            <h4 style="text-decoration: underline; margin-top: 5px; margin-bottom: 10px">
                Pembimbing {{$data['type']}}
            </h4>
            <span>Nomor : {{$data['number']}}</span>
        </div>
        <div>
            <p>
                Yang bertanda tangan dibawah ini Ketua Program Studi Jantung dan Pembuluh Darah Fakultas Kedokteran,
                Kesehatan Masyarakat, dan Keperawatan Universitas Gadjah Mada, dengan ini menerangkan bahwa telah
                dilaksanakan bimbingan {{$data['type']}} terhadap PPDS Program Studi Jantung dan Pembuluh Darah :
            </p>
            <div style="margin-left: 40px">
                <table>
                    <tr>
                        <td>Nama</td>
                        <td>:</td>
                        <td>{{$data['activity']['speaker']}}</td>
                    </tr>
                    <tr>
                        <td>Judul</td>
                        <td>:</td>
                        <td>{{$data['activity']['title']}}</td>
                    </tr>
                    <tr>
                        <td>Dosen Pembimbing</td>
                        <td>:</td>
                        <td>
                            @foreach($data['activity']['pembimbing'] as $key => $pbb)
                                {{$key + 1}}. {{$pbb['name_alt']}} <br>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td>Hari, Tanggal</td>
                        <td>:</td>
                        <td>{{$data['day']}}, {{$data['date']}}</td>
                    </tr>
                </table>
            </div>
            <p>
                Demikian surat keterangan ini dibuat dengan sebenarnya, agar dipergunakan sebagaimana mestinya.
            </p>
            <div style="margin-top: 30px">
                <div style="margin-left: 300px; position: relative">
                    <div style="position: absolute; padding: 30px 0 0 0">
                        <img src="data:image/jpg;base64, {{$ttd}}" style="width: 85px">
                    </div>
                    <div style="position: absolute">
                        Yogyakarta, {{$data['date']}} <br>
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
