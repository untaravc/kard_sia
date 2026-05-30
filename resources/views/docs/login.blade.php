@php($appUrl = rtrim(config('app.url'), '/'))
<p>
    Login dapat dilakukan pada halaman
    <a href="{{ $appUrl }}/blu/login" target="_blank" rel="noopener">{{ $appUrl }}/blu/login</a>
</p>

<p>Pada halaman tersebut tersedia beberapa metode masuk:</p>
<ol>
    <li>
        <strong>Email &amp; Password</strong> — masukkan email dan kata sandi, lalu klik
        <em>Sign in</em>. Jika lupa kata sandi, gunakan tautan
        <em>Forgot your password?</em> untuk mengatur ulang.
    </li>
    <li>
        <strong>Email Link</strong> — masukkan email, lalu klik <em>Send login link</em>.
        Tautan masuk akan dikirim ke email Anda. Buka tautan tersebut untuk langsung masuk.
    </li>
    <li>
        <strong>Phone</strong> — masukkan nomor telepon, lalu klik <em>Send login code</em>.
        Kode masuk akan dikirim untuk verifikasi.
    </li>
    <li>
        <strong>Single Sign On (SSO)</strong> — klik <em>Continue with SSO</em> untuk masuk
        menggunakan akun Google Anda.
    </li>
</ol>

<p>
    Setelah berhasil masuk, Anda akan diarahkan ke halaman dashboard
    (<a href="{{ $appUrl }}/blu/dashboard" target="_blank" rel="noopener">{{ $appUrl }}/blu/dashboard</a>).
</p>
