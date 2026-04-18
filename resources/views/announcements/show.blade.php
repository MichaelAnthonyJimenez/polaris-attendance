@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-white">Announcement</h1>
            <p class="text-slate-400 mt-1 break-words">{{ $announcement->title }}</p>
        </div>

        <a href="{{ route('announcements.index') }}" class="btn-secondary w-full sm:w-auto text-center">Back to Board</a>
    </div>

    <div class="glass p-6 sm:p-8">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs text-slate-400">
                    Published {{ $announcement->published_at?->diffForHumans() ?? '' }}
                    @if($announcement->expires_at)
                        · Expires {{ $announcement->expires_at->format('M j, Y') }}
                    @endif
                </p>
            </div>

            @if($isAdmin)
                <form
                    action="{{ route('announcements.destroy', $announcement) }}"
                    method="POST"
                    class="inline"
                    data-confirm-modal="true"
                    data-confirm-title="Delete announcement"
                    data-confirm-message="Are you sure you want to delete this announcement? This cannot be undone."
                    data-confirm-confirm-text="Delete"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-rose-400 hover:text-rose-300">
                        Delete
                    </button>
                </form>
            @endif
        </div>

        <div class="mt-5 text-sm text-slate-200 whitespace-pre-wrap">
            {{ $announcement->body }}
        </div>
    </div>
</div>
@endsection

