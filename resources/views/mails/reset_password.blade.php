<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
</head>
<body style="margin:0; padding:0; background:#f7f3ef; font-family: 'Segoe UI', Arial, sans-serif; color:#1e1f25;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f7f3ef; padding:24px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:18px; overflow:hidden; border:1px solid #e5e2dc;">
                    <tr>
                        <td style="padding:28px 32px; background:linear-gradient(120deg,#fef6e9 0%,#f7eadb 100%);">
                            <div style="font-size:12px; text-transform:uppercase; letter-spacing:1.2px; color:#666b78;">Kardio Admin</div>
                            <h1 style="margin:12px 0 0; font-size:24px;">Reset your password</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;">
                            <p style="margin:0 0 16px; color:#666b78; line-height:1.6;">
                                We received a request to reset your password. Use the token below to continue the reset process.
                            </p>
                            <div style="padding:16px; background:#e3f1ef; border-radius:12px; text-align:center; font-size:12px; font-weight:600;">
                                {{ $token }}
                            </div>
                            <p style="margin:20px 0 0; color:#666b78; line-height:1.6;">
                                If you did not request a reset, you can ignore this email.
                            </p>
                            <p style="margin:24px 0 0; font-size:12px; color:#999;">
                                This token will expire after it is used.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
