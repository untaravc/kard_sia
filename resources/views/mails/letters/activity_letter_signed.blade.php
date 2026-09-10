<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pemberitahuan Penandatanganan Surat</title>
</head>
<body style="margin:0; padding:0; background:#f7f3ef; font-family: 'Segoe UI', Arial, sans-serif; color:#1e1f25;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f7f3ef; padding:24px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:18px; overflow:hidden; border:1px solid #e5e2dc;">
                <tr>
                    <td style="padding:28px 32px; background:linear-gradient(120deg,#eef4ff 0%,#e3ebfb 100%);">
                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:1.2px; color:#666b78;">{{ setting('app.name', 'BLU') }}</div>
                        <h1 style="margin:12px 0 0; font-size:22px;">Anda Baru Saja Menandatangani Surat</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px 32px;">
                        <p style="margin:0 0 16px; line-height:1.6;">
                            Yth. {{ $kps['name'] }},
                        </p>
                        <p style="margin:0 0 16px; color:#666b78; line-height:1.6;">
                            Kami memberitahukan bahwa sebuah <strong>{{ $letter->typeLabel() }}</strong> baru saja
                            diterbitkan dan dicetak untuk pertama kalinya dengan mencantumkan tanda tangan elektronik
                            Anda sebagai {{ $kps['jabatan'] }}.
                        </p>

                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f7f8fc; border-radius:12px; padding:4px 0; margin:0 0 16px;">
                            <tr>
                                <td style="padding:14px 18px; font-size:14px; line-height:1.7;">
                                    <div><strong>Jenis Surat</strong> : {{ $letter->typeLabel() }}</div>
                                    <div><strong>Nomor</strong> : {{ $letter->number }}</div>
                                    <div><strong>Kegiatan</strong> : {{ $activity->name }}</div>
                                    @if($activity->title)
                                        <div><strong>Judul</strong> : {{ $activity->title }}</div>
                                    @endif
                                    @if($activity->speaker)
                                        <div><strong>Penyaji</strong> : {{ $activity->speaker }}</div>
                                    @endif
                                    <div><strong>Tanggal Kegiatan</strong> : {{ date_indo_str($activity->start_date) }}</div>
                                    <div><strong>Waktu Cetak</strong> : {{ optional($letter->first_printed_at)->format('d-m-Y H:i') }}</div>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 12px; color:#666b78; line-height:1.6;">
                            Silakan periksa isi surat melalui tautan berikut. Tautan ini sama dengan yang tersimpan
                            pada kode QR di surat tersebut.
                        </p>
                        <p style="margin:0 0 20px;">
                            <a href="{{ $link }}" style="display:inline-block; background:#0e153f; color:#ffffff; text-decoration:none; padding:12px 22px; border-radius:10px; font-size:14px;">
                                Lihat Surat
                            </a>
                        </p>
                        <p style="margin:0 0 16px; font-size:12px; color:#8a8f9c; line-height:1.6; word-break:break-all;">
                            {{ $link }}
                        </p>
                        <p style="margin:0; color:#666b78; line-height:1.6;">
                            Apabila Anda merasa tidak pernah menyetujui penerbitan surat ini, mohon segera hubungi
                            bagian akademik.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
