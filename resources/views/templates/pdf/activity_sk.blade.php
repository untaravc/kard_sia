<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Surat Keterangan {{ $data['type'] }}</title>
    @include('templates.pdf.partials.letter_style')
</head>
<body>
<div class="sheet">
    @unless($auto_print)
        <div class="verify-bar">
            <b>Verifikasi Surat</b><br>
            Dokumen ini adalah salinan resmi dari sistem. Nomor {{ $data['number'] }}, diterbitkan
            {{ optional($letter->first_printed_at)->format('d-m-Y H:i') }}.
        </div>
    @endunless

    @include('templates.pdf.header')
    <hr>
    <div class="content">
        <div class="letter-title">
            <h4>SURAT KETERANGAN</h4>
            <h4>Pembimbing {{ $data['type'] }}</h4>
            <div>Nomor : {{ $data['number'] }}</div>
        </div>

        <p>
            Yang bertanda tangan di bawah ini {{ $kps['jabatan'] }}
            {{ setting('app.faculty-name', 'Fakultas Kedokteran, Kesehatan Masyarakat, dan Keperawatan') }}
            {{ setting('app.university-name', 'Universitas Gadjah Mada') }}, dengan ini menerangkan bahwa telah
            dilaksanakan bimbingan {{ $data['type'] }} terhadap PPDS Program Studi Jantung dan Pembuluh Darah:
        </p>

        <div class="indent">
            <table class="kv">
                <tr>
                    <td class="k">Nama</td>
                    <td class="s">:</td>
                    <td>{{ $data['activity']['speaker'] }}</td>
                </tr>
                <tr>
                    <td class="k">Judul</td>
                    <td class="s">:</td>
                    <td>{{ $data['activity']['title'] }}</td>
                </tr>
                <tr>
                    <td class="k">Dosen Pembimbing</td>
                    <td class="s">:</td>
                    <td>
                        @forelse($data['pembimbing'] as $key => $pbb)
                            {{ $key + 1 }}. {{ $pbb['name_alt'] ?: $pbb['name'] }}<br>
                        @empty
                            -
                        @endforelse
                    </td>
                </tr>
                @if(count($data['penguji']))
                    <tr>
                        <td class="k">Dosen Penguji</td>
                        <td class="s">:</td>
                        <td>
                            @foreach($data['penguji'] as $key => $pgj)
                                {{ $key + 1 }}. {{ $pgj['name_alt'] ?: $pgj['name'] }}<br>
                            @endforeach
                        </td>
                    </tr>
                @endif
                <tr>
                    <td class="k">Hari, Tanggal</td>
                    <td class="s">:</td>
                    <td>{{ $data['day'] }}, {{ $data['date'] }}</td>
                </tr>
                <tr>
                    <td class="k">Tempat</td>
                    <td class="s">:</td>
                    <td>{{ $data['activity']['place'] }}</td>
                </tr>
            </table>
        </div>

        <p>
            Demikian surat keterangan ini dibuat dengan sebenarnya, agar dipergunakan sebagaimana mestinya.
        </p>

        @include('templates.pdf.partials.qr_signature')
    </div>
</div>
@if($auto_print)
    <script>
        window.print();
    </script>
@endif
</body>
</html>
