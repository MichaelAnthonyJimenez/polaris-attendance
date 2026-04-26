<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        [$dateFrom, $dateTo, $driverIds, $period, $driverScope] = $this->resolveFilters($request);
        $attendances = $this->attendanceQuery($dateFrom, $dateTo, $driverIds)
            ->orderBy('captured_at', 'desc')
            ->get();

        // Statistics
        $stats = [
            'total_check_ins' => Attendance::whereBetween('captured_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('type', 'check_in')
                ->when($driverIds !== [], fn($q) => $q->whereIn('driver_id', $driverIds))
                ->count(),
            'total_check_outs' => Attendance::whereBetween('captured_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('type', 'check_out')
                ->when($driverIds !== [], fn($q) => $q->whereIn('driver_id', $driverIds))
                ->count(),
            'avg_face_confidence' => Attendance::whereBetween('captured_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->when($driverIds !== [], fn($q) => $q->whereIn('driver_id', $driverIds))
                ->whereNotNull('face_confidence')
                ->avg('face_confidence'),
            'avg_liveness_score' => Attendance::whereBetween('captured_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->when($driverIds !== [], fn($q) => $q->whereIn('driver_id', $driverIds))
                ->whereNotNull('liveness_score')
                ->avg('liveness_score'),
        ];

        // Daily attendance chart data
        $dailyData = Attendance::select(
                DB::raw('DATE(captured_at) as date'),
                DB::raw('COUNT(CASE WHEN type = "check_in" THEN 1 END) as check_ins'),
                DB::raw('COUNT(CASE WHEN type = "check_out" THEN 1 END) as check_outs')
            )
            ->whereBetween('captured_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->when($driverIds !== [], fn($q) => $q->whereIn('driver_id', $driverIds))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('reports.index', [
            'attendances' => $attendances,
            'stats' => $stats,
            'dailyData' => $dailyData,
            'drivers' => User::where('role', 'driver')->where('active', true)->orderBy('name')->get(),
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'driver_ids' => $driverIds,
                'period' => $period,
                'driver_scope' => $driverScope,
            ],
        ]);
    }

    public function export(Request $request): Response
    {
        [$dateFrom, $dateTo, $driverIds, $period, $driverScope] = $this->resolveFilters($request);
        $format = strtolower((string) $request->input('export_as', 'csv'));
        $driverName = $this->resolveDriverName($driverIds);
        $generatedAt = now()->format('Y-m-d H:i:s');

        $rows = $this->attendanceQuery($dateFrom, $dateTo, $driverIds)
            ->orderBy('captured_at', 'desc')
            ->get()
            ->map(function (Attendance $attendance): array {
                $hours = '';
                if ($attendance->type === 'check_out' && $attendance->total_hours !== null) {
                    $hours = number_format((float) $attendance->total_hours, 2);
                }

                return [
                    'Driver' => $attendance->driver->name ?? 'Unknown',
                    'Type' => str_replace('_', ' ', (string) $attendance->type),
                    'Date & Time' => $attendance->captured_at?->format('Y-m-d H:i:s') ?? '',
                    'Total Hours' => $hours,
                    'Face Match' => $attendance->face_confidence !== null ? $attendance->face_confidence . '%' : '',
                    'Liveness' => $attendance->liveness_score !== null ? number_format((float) $attendance->liveness_score, 2) : '',
                    'Device' => $attendance->device_id ?? '',
                    'Location' => $this->locationLabel($attendance),
                ];
            });

        $headers = ['Driver', 'Type', 'Date & Time', 'Total Hours', 'Face Match', 'Liveness', 'Device', 'Location'];
        $filenameBase = "attendance-report-{$dateFrom}-to-{$dateTo}";
        $summary = [
            'Total Records' => $rows->count(),
            'Total Check-ins' => $rows->where('Type', 'check in')->count(),
            'Total Check-outs' => $rows->where('Type', 'check out')->count(),
        ];
        $meta = [
            'Report Title' => 'Attendance Report',
            'Date From' => $dateFrom,
            'Date To' => $dateTo,
            'Period' => ucfirst($period),
            'Driver Filter' => $driverName,
            'Driver Mode' => $driverScope === 'multi' ? 'Multi-select' : 'All drivers',
            'Generated At' => $generatedAt,
        ];

        return match ($format) {
            'excel' => response("\xEF\xBB\xBF" . $this->buildDelimitedContent($meta, $summary, $headers, $rows, "\t"), 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filenameBase . '.xls"',
            ]),
            'word' => response($this->buildHtmlDocument($meta, $summary, $headers, $rows), 200, [
                'Content-Type' => 'application/msword; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filenameBase . '.doc"',
            ]),
            'pdf' => response($this->buildSimplePdf($meta, $summary, $headers, $rows), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filenameBase . '.pdf"',
            ]),
            default => response($this->buildDelimitedContent($meta, $summary, $headers, $rows, ','), 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filenameBase . '.csv"',
            ]),
        };
    }

    private function resolveFilters(Request $request): array
    {
        $period = (string) $request->input('period', 'monthly');
        $today = now();
        [$defaultFrom, $defaultTo] = match ($period) {
            'hourly' => [$today->copy()->format('Y-m-d'), $today->copy()->format('Y-m-d')],
            'daily' => [$today->copy()->format('Y-m-d'), $today->copy()->format('Y-m-d')],
            'weekly' => [$today->copy()->startOfWeek()->format('Y-m-d'), $today->copy()->endOfWeek()->format('Y-m-d')],
            default => [$today->copy()->startOfMonth()->format('Y-m-d'), $today->copy()->format('Y-m-d')],
        };

        $driverScope = (string) $request->input('driver_scope', 'all');
        if (! in_array($driverScope, ['all', 'multi'], true)) {
            $driverScope = 'all';
        }

        $driverIds = array_values(array_filter(array_map('intval', (array) $request->input('driver_ids', []))));
        if ($driverIds === [] && $request->filled('driver_id')) {
            $driverIds = [(int) $request->input('driver_id')];
        }
        if ($driverScope === 'all') {
            $driverIds = [];
        }

        return [
            (string) $request->input('date_from', $defaultFrom),
            (string) $request->input('date_to', $defaultTo),
            $driverIds,
            $period,
            $driverScope,
        ];
    }

    private function attendanceQuery(string $dateFrom, string $dateTo, array $driverIds)
    {
        return Attendance::with('driver')
            ->whereBetween('captured_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->when($driverIds !== [], fn($query) => $query->whereIn('driver_id', $driverIds));
    }

    private function resolveDriverName(array $driverIds): string
    {
        if ($driverIds === []) {
            return 'All Drivers';
        }

        $names = User::query()->whereIn('id', $driverIds)->pluck('name')->all();
        if ($names === []) {
            return 'Unknown Driver';
        }

        return implode(', ', $names);
    }

    private function locationLabel(Attendance $attendance): string
    {
        $meta = is_array($attendance->meta ?? null) ? $attendance->meta : [];
        $lat = data_get($meta, 'latitude');
        $lng = data_get($meta, 'longitude');
        if (is_numeric($lat) && is_numeric($lng)) {
            return number_format((float) $lat, 6) . ', ' . number_format((float) $lng, 6);
        }

        return '';
    }

    private function buildDelimitedContent(array $meta, array $summary, array $headers, Collection $rows, string $delimiter): string
    {
        $handle = fopen('php://temp', 'r+');

        foreach ($meta as $label => $value) {
            fputcsv($handle, [$label, $value], $delimiter);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['Summary'], $delimiter);
        foreach ($summary as $label => $value) {
            fputcsv($handle, [$label, $value], $delimiter);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['Attendance Records'], $delimiter);
        fputcsv($handle, $headers, $delimiter);
        foreach ($rows as $row) {
            fputcsv($handle, array_values($row), $delimiter);
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        return $content ?: '';
    }

    private function buildHtmlDocument(array $meta, array $summary, array $headers, Collection $rows): string
    {
        $metaRows = collect($meta)->map(
            fn($value, $label) => '<tr><th align="left">' . e((string) $label) . '</th><td>' . e((string) $value) . '</td></tr>'
        )->implode('');

        $summaryRows = collect($summary)->map(
            fn($value, $label) => '<tr><th align="left">' . e((string) $label) . '</th><td>' . e((string) $value) . '</td></tr>'
        )->implode('');

        $thead = '<tr>' . collect($headers)->map(fn($header) => '<th>' . e($header) . '</th>')->implode('') . '</tr>';
        $tbody = $rows->map(function (array $row): string {
            $cells = collect($row)->map(fn($value) => '<td>' . e((string) $value) . '</td>')->implode('');
            return '<tr>' . $cells . '</tr>';
        })->implode('');

        return '<h1>Attendance Report</h1>'
            . '<h3>Report Details</h3><table border="1">' . $metaRows . '</table>'
            . '<h3>Summary</h3><table border="1">' . $summaryRows . '</table>'
            . '<h3>Attendance Records</h3><table border="1"><thead>' . $thead . '</thead><tbody>' . $tbody . '</tbody></table>';
    }

    private function buildSimplePdf(array $meta, array $summary, array $headers, Collection $rows): string
    {
        $lines = [];
        $lines[] = 'Attendance Report';
        $lines[] = '';
        foreach ($meta as $label => $value) {
            $lines[] = $label . ': ' . $value;
        }
        $lines[] = '';
        $lines[] = 'Summary';
        foreach ($summary as $label => $value) {
            $lines[] = $label . ': ' . $value;
        }
        $lines[] = '';
        $lines[] = 'Attendance Records';
        $lines[] = implode(' | ', $headers);
        $lines[] = str_repeat('-', 120);
        foreach ($rows as $row) {
            $line = implode(' | ', array_map(fn($value) => (string) $value, array_values($row)));
            $chunks = mb_str_split($line, 95);
            foreach ($chunks as $chunk) {
                $lines[] = $chunk;
            }
        }

        $y = 800;
        $commands = ["BT /F1 10 Tf 12 TL 40 {$y} Td"];
        foreach ($lines as $line) {
            $safeLine = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $commands[] = '(' . $safeLine . ') Tj';
            $commands[] = 'T*';
        }
        $commands[] = 'ET';
        $content = implode("\n", $commands) . "\n";
        $contentLength = strlen($content);

        $objects = [];
        $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n";
        $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n";
        $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n";
        $objects[] = "4 0 obj << /Length {$contentLength} >> stream\n{$content}endstream endobj\n";
        $objects[] = "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";
        return $pdf;
    }
}
