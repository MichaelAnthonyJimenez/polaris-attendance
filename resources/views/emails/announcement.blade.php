<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Announcement</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b;">
    <p>Hi {{ $user->name }},</p>

    <p>
        <strong>{{ $announcement->title }}</strong>
    </p>

    <div style="margin-top: 10px; padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc;">
        {!! nl2br(e($announcement->body)) !!}
    </div>

    @if($announcement->expires_at)
        <p style="font-size: 0.875rem; color: #64748b;">
            This announcement expires on {{ $announcement->expires_at->format('M j, Y') }}.
        </p>
    @endif

    <p style="font-size: 0.875rem; color: #64748b; margin-top: 14px;">
        This message was sent by Polaris Attendance.
    </p>
</body>
</html>

