@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <h1 class="text-2xl sm:text-3xl font-bold text-white">Drivers</h1>
        <a href="{{ route('drivers.create') }}" class="btn-primary w-full sm:w-auto">Add Driver</a>
    </div>

    <div class="glass p-6">
        <form action="{{ route('drivers.index') }}" method="GET" class="mb-6 flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label for="sort-drivers" class="text-sm text-slate-300">Sort by</label>
                <select
                    name="sort"
                    id="sort-drivers"
                    class="px-3 py-2 rounded-lg bg-slate-800/50 border border-slate-600 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    onchange="this.form.submit()"
                >
                    <option value="" {{ empty($sort) ? 'selected' : '' }}>Default</option>
                    <option value="latest" {{ ($sort ?? '') === 'latest' ? 'selected' : '' }}>Newest first</option>
                    <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    <option value="name_asc" {{ ($sort ?? '') === 'name_asc' ? 'selected' : '' }}>Name A–Z</option>
                    <option value="name_desc" {{ ($sort ?? '') === 'name_desc' ? 'selected' : '' }}>Name Z–A</option>
                    <option value="badge_asc" {{ ($sort ?? '') === 'badge_asc' ? 'selected' : '' }}>Badge ↑</option>
                    <option value="badge_desc" {{ ($sort ?? '') === 'badge_desc' ? 'selected' : '' }}>Badge ↓</option>
                </select>
            </div>

            <label for="search-drivers" class="sr-only">Search drivers</label>
            <input
                type="text"
                name="search"
                id="search-drivers"
                value="{{ old('search', $search ?? '') }}"
                placeholder="Search by name, badge, email, phone or vehicle..."
                class="flex-1 min-w-0 sm:min-w-[200px] px-4 py-2 rounded-lg bg-slate-800/50 border border-slate-600 text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
            >

            <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium transition">
                Search
            </button>

            @if(!empty($search ?? '') || !empty($sort ?? ''))
                <a href="{{ route('drivers.index') }}" class="px-4 py-2 rounded-lg bg-slate-600 hover:bg-slate-500 text-white font-medium transition">
                    Clear
                </a>
            @endif
        </form>

        <!-- Mobile cards -->
        <div class="space-y-3 md:hidden">
            @forelse ($drivers as $driver)
                <div class="rounded-2xl border border-white/10 bg-white/5 p-3 sm:p-4">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="text-sm text-slate-200 font-medium truncate">
                                {{ $driver->name }}
                            </div>
                            <div class="text-xs text-slate-400">
                                Badge: {{ $driver->badge_number }}
                            </div>
                        </div>
                        <span class="shrink-0 px-2 py-1 rounded text-xs {{ $driver->active ? 'bg-emerald-500/20 text-emerald-200' : 'bg-slate-500/20 text-slate-300' }}">
                            {{ $driver->active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="mt-3 grid grid-cols-1 gap-2 text-sm">
                        <div class="text-slate-300">
                            <span class="text-slate-500">Email:</span>
                            <span class="text-slate-200">{{ $driver->email ?? '—' }}</span>
                        </div>
                        <div class="text-slate-300">
                            <span class="text-slate-500">Vehicle:</span>
                            <span class="text-slate-200">{{ $driver->vehicle_number ?? '—' }}</span>
                        </div>
                        <div class="text-slate-300">
                            <span class="text-slate-500">Phone:</span>
                            <span class="text-slate-200">{{ $driver->phone ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('drivers.edit', $driver) }}" class="text-emerald-400 hover:text-emerald-300 text-sm">View</a>
                        <a href="{{ route('drivers.edit', $driver) }}" class="text-blue-400 hover:text-blue-300 text-sm">Edit</a>
                        <form
                            action="{{ route('drivers.destroy', $driver) }}"
                            method="POST"
                            class="inline"
                            data-confirm-modal="true"
                            data-confirm-title="Delete account"
                            data-confirm-message="Are you sure you want to delete this driver account? This action cannot be undone."
                            data-confirm-confirm-text="Delete"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400">No drivers registered yet.</div>
            @endforelse
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block overflow-x-auto">
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
                                    <a href="{{ route('drivers.edit', $driver) }}" class="text-emerald-400 hover:text-emerald-300 text-sm">View</a>
                                    <a href="{{ route('drivers.edit', $driver) }}" class="text-blue-400 hover:text-blue-300 text-sm">Edit</a>
                                    <form
                                        action="{{ route('drivers.destroy', $driver) }}"
                                        method="POST"
                                        class="inline"
                                        data-confirm-modal="true"
                                        data-confirm-title="Delete account"
                                        data-confirm-message="Are you sure you want to delete this driver account? This action cannot be undone."
                                        data-confirm-confirm-text="Delete"
                                    >
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

