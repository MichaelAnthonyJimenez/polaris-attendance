@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white">Notifications</h1>
            <p class="text-slate-400 mt-1">View your recent system notifications</p>
        </div>
        @if(auth()->user()?->unreadNotifications()->count() > 0)
            <form method="POST" action="{{ route('notifications.markAllRead') }}">
                @csrf
                <button type="submit" class="btn-secondary">Mark all as read</button>
            </form>
        @endif
    </div>

    <div class="glass p-4 sm:p-6">
        @if($notifications->isEmpty())
            <div class="text-center py-10 text-slate-400">No notifications found.</div>
        @else
            <div class="space-y-3">
                @foreach($notifications as $notification)
                    @php
                        $isUnread = $notification->read_at === null;
                        $message = $notification->data['message'] ?? class_basename($notification->type);
                    @endphp
                    <div class="rounded-xl border {{ $isUnread ? 'border-blue-500/40 bg-blue-500/10' : 'border-white/10 bg-white/5' }} p-4">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 w-2 h-2 rounded-full {{ $isUnread ? 'bg-blue-400' : 'bg-slate-500' }}"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm sm:text-base text-white break-words">{{ $message }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($notifications->hasPages())
                <div class="mt-4 overflow-x-auto pb-1">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
