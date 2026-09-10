<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Undangan Presentasi {{ $data['type'] }}</title>
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
        <div class="letter-date">Yogyakarta, {{ $data['date'] }}</div>

        <table class="kv">
            <tr>
                <td class="k">Nomor</td>
                <td class="s">:</td>
                <td>{{ $data['number'] }}</td>
            </tr>
            <tr>
                <td class="k">Perihal</td>
                <td class="s">:</td>
                <td>
                    Undangan Presentasi {{ $data['type'] }}@if($data['activity']['stase']) {{ $data['activity']['stase']['name'] }}@endif
                    <br>
                    {{ $data['activity']['speaker'] }}
                </td>
            </tr>
        </table>

        <div class="recipients">
            Yth.
            <ol>
                @foreach($data['all_staff'] as $staff)
                    <li>{{ $staff }}</li>
                @endforeach
            </ol>
        </div>

        <p>
            Mengharap kehadiran Sejawat pada presentasi seminar {{ $data['type'] }} yang akan dilaksanakan:
        </p>

        <div class="indent">
            <table class="kv narrow">
                <tr>
                    <td class="k">hari, tgl</td>
                    <td class="s">:</td>
                    <td>{{ $data['day'] }}, {{ $data['date'] }}</td>
                </tr>
                <tr>
                    <td class="k">jam</td>
                    <td class="s">:</td>
                    <td>{{ $data['time'] }}</td>
                </tr>
                <tr>
                    <td class="k">tempat</td>
                    <td class="s">:</td>
                    <td>{{ $data['activity']['place'] }}</td>
                </tr>
                <tr>
                    <td class="k">acara</td>
                    <td class="s">:</td>
                    <td>
                        Presentasi {{ $data['type'] }}@if($data['activity']['stase']) {{ $data['activity']['stase']['name'] }}@endif
                    </td>
                </tr>
                <tr>
                    <td class="k">judul</td>
                    <td class="s">:</td>
                    <td>{{ $data['activity']['title'] }}</td>
                </tr>
            </table>
        </div>

        <p>
            Demikian undangan ini kami sampaikan, atas perhatian dan kehadiran Sejawat kami mengucapkan
            terima kasih.
        </p>

        @include('templates.pdf.partials.qr_signature', ['show_sign_date' => false])
    </div>
</div>
@if($auto_print)
    <script>
        window.print();
    </script>
@endif
</body>
</html>
