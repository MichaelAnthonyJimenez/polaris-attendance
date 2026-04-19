<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b;">
    <p>Hi {{ $user->name }},</p>
    <p>
        Your <strong>{{ str_replace('_', ' ', $attendance->type) }}</strong>
        was recorded at {{ $attendance->captured_at?->timezone(config('app.timezone'))->format('M j, Y g:i A T') ?? '—' }}.
    </p>
    @if($attendance->type === 'check_out' && $attendance->total_hours !== null)
        <p>Total time for this shift: <strong>{{ number_format((float) $attendance->total_hours, 2) }} hours</strong>.</p>
    @endif
    <p style="font-size: 0.875rem; color: #64748b;">This message was sent by {{ config('app.name', 'Polaris Attendance') }}.</p>
</body>
</html>
