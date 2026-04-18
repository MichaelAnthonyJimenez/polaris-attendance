@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white">Announcements</h1>
        </div>
    </div>

    <div class="glass p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl font-semibold text-white">Announcement Board</h2>
            </div>
        </div>

        @if($announcements->isEmpty())
            <div class="text-center py-10 text-slate-400">No announcements found.</div>
        @else
            <div class="space-y-4">
                @foreach($announcements as $announcement)
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-lg font-semibold text-white break-words">{{ $announcement->title }}</h3>
                                <p class="text-xs text-slate-400 mt-1">
                                    Published {{ $announcement->published_at?->diffForHumans() ?? '' }}
                                    @if($announcement->expires_at)
                                        · Expires {{ $announcement->expires_at->format('M j, Y') }}
                                    @endif
                                </p>
                            </div>
                            @if($announcement->send_to_all)
                                <span class="shrink-0 px-2.5 py-1 rounded text-xs bg-blue-500/20 text-blue-200 border border-blue-500/30">
                                    All drivers
                                </span>
                            @endif
                        </div>
                        <div class="mt-3 text-sm text-slate-200 whitespace-pre-wrap">
                            {{ $announcement->body }}
                        </div>

                        <div class="mt-4 flex items-center justify-end gap-3">
                            <a href="{{ route('announcements.show', $announcement) }}" class="text-blue-400 hover:text-blue-300 text-sm">
                                View
                            </a>

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
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($announcements->hasPages())
                <div class="mt-6">
                    {{ $announcements->links() }}
                </div>
            @endif
        @endif

        @if($isAdmin)
            <div class="mt-6 flex justify-end">
                <a href="{{ route('announcements.create') }}" class="btn-primary">
                    Make an Announcement
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

