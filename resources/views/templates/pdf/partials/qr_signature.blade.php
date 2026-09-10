{{-- Signature block. The QR replaces the scanned signature image: it encodes
     the letter's public URL, so anyone holding the paper can pull up the
     original document. --}}
<table class="sign-block">
    <tr>
        <td class="sign-spacer"></td>
        <td class="sign-cell">
            @if($show_sign_date ?? true)
                <div>Yogyakarta, {{ $data['date'] }}</div>
            @endif
            <div>{{ $kps['jabatan'] }}</div>
            <div class="sign-qr">
                <img src="{{ $qr }}" alt="QR verifikasi surat">
            </div>
            <div><b>{{ $kps['name'] }}</b></div>
            <div><u>NIP {{ $kps['nip'] }}</u></div>
        </td>
    </tr>
</table>
