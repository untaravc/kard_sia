<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengingat Penyelesaian Tugas Stase</title>
</head>
<body style="margin:0; padding:0; background:#f7f3ef; font-family: 'Segoe UI', Arial, sans-serif; color:#1e1f25;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f7f3ef; padding:24px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:18px; overflow:hidden; border:1px solid #e5e2dc;">
                    <tr>
                        <td style="padding:28px 32px; background:linear-gradient(120deg,#fef6e9 0%,#f7eadb 100%);">
                            <div style="font-size:12px; text-transform:uppercase; letter-spacing:1.2px; color:#666b78;">Kardio Admin</div>
                            <h1 style="margin:12px 0 0; font-size:24px;">Pengingat Penyelesaian Tugas Stase</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;">
                            <p style="margin:0 0 16px; line-height:1.6;">
                                Kepada {{ $student->name }},
                            </p>
                            <p style="margin:0 0 16px; color:#666b78; line-height:1.6;">
                                Berdasarkan pemantauan kami, penyelesaian tugas Anda pada stase
                                <strong>{{ $stase->alias ?? $stase->name }}</strong> masih belum mencapai target yang
                                ditetapkan. Menyelesaikan setiap tugas tepat waktu akan membantu pembimbing menilai
                                capaian pembelajaran Anda secara menyeluruh, jadi mohon segera ditindaklanjuti.
                            </p>

                            @if(isset($doneTasks) && isset($totalTasks))
                                <div style="padding:16px; background:#fdecec; border-radius:12px; text-align:center; margin:0 0 16px;">
                                    <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.6px; color:#8a5252;">Tugas Terselesaikan</div>
                                    <div style="margin-top:6px; font-size:20px; font-weight:700; color:#b3413a;">
                                        {{ $doneTasks }} / {{ $totalTasks }} tugas
                                    </div>
                                </div>
                            @endif

                            <p style="margin:0 0 16px; color:#666b78; line-height:1.6;">
                                Silakan login ke akun Anda dan lengkapi tugas yang tersisa pada stase ini sesegera
                                mungkin. Jika Anda mengalami kendala, silakan hubungi dosen pembimbing atau bagian
                                akademik.
                            </p>
                            <p style="margin:24px 0 0; font-size:12px; color:#999;">
                                Email ini dikirim otomatis oleh sistem Kardio berdasarkan pemantauan capaian tugas stase Anda.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
