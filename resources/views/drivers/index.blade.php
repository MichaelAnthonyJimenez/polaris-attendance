@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-white">Drivers</h1>
        <a href="{{ route('drivers.create') }}" class="btn-primary">Add Driver</a>
    </div>

    <div class="glass p-6">
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Badge Number</th>
                        <th>Email</th>
                        <th>Vehicle</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($drivers as $driver)
                        <tr>
                            <td class="font-medium">{{ $driver->name }}</td>
                            <td>{{ $driver->badge_number }}</td>
                            <td>{{ $driver->email ?? '—' }}</td>
                            <td>{{ $driver->vehicle_number ?? '—' }}</td>
                            <td>{{ $driver->phone ?? '—' }}</td>
                            <td>
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $driver->active ? 'bg-emerald-500/20 text-emerald-200 border border-emerald-500/40' : 'bg-slate-500/20 text-slate-300 border border-slate-500/40' }}">
                                    {{ $driver->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('drivers.edit', $driver) }}" class="text-blue-400 hover:text-blue-300 text-sm">Edit</a>
                                    <form action="{{ route('drivers.destroy', $driver) }}" method="POST" class="inline" onsubmit="return confirm('Delete this driver?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">No drivers registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($drivers->hasPages())
        <div class="glass p-4">
            {{ $drivers->links() }}
        </div>
    @endif
</div>
@endsection

