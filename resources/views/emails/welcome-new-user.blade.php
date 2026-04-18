<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name', 'Polaris Attendance') }}</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent;">
        Welcome to {{ config('app.name', 'Polaris Attendance') }}. Your driver account is ready.
    </div>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f1f5f9; margin:0; padding:0; width:100%;">
        <tr>
            <td align="center" style="padding:28px 16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" width="600" style="width:600px; max-width:600px;">
                    <tr>
                        <td style="padding:0 0 14px 0;">
                            <div style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:#0f172a; font-size:18px; font-weight:800; letter-spacing:0.2px;">
                                Polaris Attendance
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:26px;">
                            <div style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:#0f172a; font-size:22px; font-weight:800; line-height:1.25; margin:0 0 10px 0;">
                                Welcome, {{ $user->name }}!
                            </div>

                            <div style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:#334155; font-size:15px; line-height:1.7;">
                                <p style="margin:0 0 14px 0;">
                                    Your <strong>driver account</strong> has been created successfully. You can now start using Polaris Attendance to keep your work records accurate and up to date.
                                </p>

                                <div style="margin:14px 0 12px 0; padding:14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">
                                    <div style="font-weight:800; color:#0f172a; margin:0 0 8px 0;">What you can do inside the app</div>
                                    <ul style="margin:0; padding:0 0 0 18px;">
                                        <li style="margin:6px 0;">Track your attendance (time in / time out) reliably.</li>
                                        <li style="margin:6px 0;">View your assigned details and keep your driver profile updated.</li>
                                        <li style="margin:6px 0;">Stay compliant with cooperative policies and verification requirements.</li>
                                        <li style="margin:6px 0;">Access summaries and records when needed.</li>
                                    </ul>
                                </div>

                                <p style="margin:12px 0 0 0; color:#64748b; font-size:13px;">
                                    If you didn’t create this account, you can ignore this email.
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

