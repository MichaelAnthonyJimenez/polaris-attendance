@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-white">Audit Logs</h1>
    </div>

    <!-- Filters -->
    <div class="glass p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Filters</h2>
        <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="form-label">Action</label>
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst($action) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Model Type</label>
                <select name="model_type" class="form-select">
                    <option value="">All Types</option>
                    @foreach($modelTypes as $type)
                        <option value="{{ $type }}" {{ request('model_type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary w-full">Apply Filters</button>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="glass p-6">
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Model</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                            <td>{{ $log->user->name ?? 'System' }}</td>
                            <td>
                                <span class="px-2 py-1 rounded text-xs bg-blue-500/20 text-blue-200">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td>
                                @if($log->model_type)
                                    <span class="text-slate-300">{{ $log->model_type }}</span>
                                    @if($log->model_id)
                                        <span class="text-slate-500">#{{ $log->model_id }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="max-w-xs truncate">{{ $log->description ?? '—' }}</td>
                            <td class="text-slate-400 text-xs">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400">No audit logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection

