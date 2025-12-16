<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->latest();

        // Filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        return view('audit-logs.index', [
            'logs' => $logs,
            'actions' => AuditLog::distinct()->pluck('action')->sort(),
            'modelTypes' => AuditLog::distinct()->whereNotNull('model_type')->pluck('model_type')->sort(),
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load('user');
        
        return view('audit-logs.show', [
            'log' => $auditLog,
        ]);
    }
}
