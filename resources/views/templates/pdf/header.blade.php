<table style="font-size: 13px; color: #001c44;">
    <tr>
        <td>
            <img src="data:image/jpg;base64, {{$logo}}" style="width: 85px">
        </td>
        <td style="width: 85%">
            <div>{{ setting('app.university-name', 'UNIVERSITAS') }} </div>
            <div>{{ setting('app.faculty-name', 'FAKULTAS') }} </div>
            {{ setting('app.department-name', 'DEPARTEMEN') }} <br>
            <b>PROGRAM SPESIALIS JANTUNG DAN PEMBULUH DARAH</b> <br>
            <small>
                Gedung Radioputro Lt 2 Sayap Barat Fakultas Kedokteran Kesehatan Masyarakat dan Keperawatan
                <br>
                Jl. Farmako Sekip Utara Sleman Sleman DI Yogyakarta  55281 Telp. {{ setting('app.contact-phone', '0274-631011') }}, Email : {{ setting('app.contact-email', 'contact-email') }}
            </small>
        </td>
    </tr>
</table>
