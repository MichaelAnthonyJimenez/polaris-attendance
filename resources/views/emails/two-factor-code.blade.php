<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your verification code</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent;">
        Your {{ config('app.name', 'Polaris Attendance') }} verification code is {{ $code }}.
    </div>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f1f5f9; margin:0; padding:0; width:100%;">
        <tr>
            <td align="center" style="padding:28px 16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" width="600" style="width:600px; max-width:600px;">
                    <tr>
                        <td style="padding:0 0 14px 0;">
                            <div style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:#0f172a; font-size:18px; font-weight:800; letter-spacing:0.2px;">
                                {{ config('app.name', 'Polaris Attendance') }}
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:26px;">
                            <div style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:#0f172a; font-size:20px; font-weight:800; line-height:1.25; margin:0 0 10px 0;">
                                Verify your login
                            </div>

                            <div style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:#334155; font-size:15px; line-height:1.7;">
                                <p style="margin:0 0 14px 0;">Hi {{ $user->name }},</p>
                                <p style="margin:0 0 14px 0;">
                                    Use this code to finish signing in to {{ config('app.name', 'Polaris Attendance') }}:
                                </p>

                                <div style="margin:14px 0; padding:14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; text-align:center;">
                                    <div style="font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-size:28px; font-weight:900; letter-spacing:0.3em; color:#0284c7;">
                                        {{ $code }}
                                    </div>
                                </div>

                                <p style="margin:0; color:#64748b; font-size:13px;">
                                    This code expires soon. If you didn’t try to sign in, you can ignore this email.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:14px 4px 0 4px;">
                            <div style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:#64748b; font-size:12px; line-height:1.6;">
                                Polaris Multipurpose Cooperative<br>
                                © {{ date('Y') }} {{ config('app.name', 'Polaris Attendance') }}. All rights reserved.<br>
                                <span style="color:#94a3b8;">This is an automated email. Please do not reply.</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

