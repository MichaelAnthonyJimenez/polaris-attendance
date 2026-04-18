<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Polaris Attendance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head-scripts')
</head>
<body @class(['camera-page' => request()->routeIs('camera.*')])>
    <div @class(['min-h-screen flex', 'min-h-[100dvh] bg-black' => request()->routeIs('camera.*')])>
        @auth
            <!-- Sidebar -->
            <aside id="appSidebar" class="hidden lg:flex w-64 bg-slate-900/80 backdrop-blur-md border-r border-white/10 flex-col fixed h-screen z-20">
                <div class="p-6 border-b border-white/10">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <img
                                    src="{{ asset('images/571126195_1487564565872864_6483675374142568502_n-removebg-preview (1).png') }}"
                                    alt="Polaris Attendance"
                                    class="w-7 h-7 rounded-lg object-contain bg-white/5 border border-white/10"
                                >
                                <h1 class="text-sm font-semibold text-white truncate">Polaris Attendance</h1>
                            </div>
                            <p class="text-xs text-slate-400 mt-1 truncate">Taxi Driver Management</p>
                        </div>
                        <button
                            type="button"
                            id="appSidebarToggleDesktop"
                            class="hidden lg:inline-flex items-center justify-center p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                            title="Hide sidebar"
                            aria-label="Toggle sidebar"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    @if(auth()->user()?->role === 'admin')
                        <a href="{{ route('locations.index') }}" class="nav-link {{ request()->routeIs('locations.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.5-7.5 11-7.5 11S4.5 18 4.5 10.5a7.5 7.5 0 1115 0z"></path>
                            </svg>
                            <span>Locations</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span>Users</span>
                        </a>
                        <a href="{{ route('driver-verification.index') }}" class="nav-link {{ request()->routeIs('driver-verification.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span>Driver Verification</span>
                        </a>
                    @endif

                    <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Attendance</span>
                    </a>

                    <a href="{{ route('announcements.index') }}" class="nav-link {{ request()->routeIs('announcements.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1a1 1 0 01-1-1V8a4 4 0 118 0v7a1 1 0 01-1 1h-1m-7 0H9m4 4H9a2 2 0 01-2-2v-1m0-1a2 2 0 012-2h1"></path>
                        </svg>
                        <span>Announcements</span>
                    </a>

                    @if(auth()->user()?->role === 'admin')
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Reports</span>
                        </a>
                        <a href="{{ route('audit-logs.index') }}" class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Audit Logs</span>
                        </a>
                        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Settings</span>
                        </a>
                    @endif
                </nav>

                <div class="p-4 border-t border-white/10 relative" id="sidebarProfileDropdownWrap">
                    <button
                        type="button"
                        id="sidebarProfileDropdownTrigger"
                        class="flex items-center gap-3 w-full rounded-lg p-2 text-left hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                        aria-label="Profile menu"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <span class="flex items-center justify-center w-10 h-10 rounded-full overflow-hidden bg-slate-600 text-white font-medium text-sm border-2 border-white/10 shrink-0">
                            @if(Auth::user()->profile_photo_url ?? null)
                                <img src="{{ Auth::user()->profile_photo_url }}" alt="" class="w-full h-full object-cover">
                            @else
                                @php
                                    $sidebarName = Auth::user()->name ?? 'User';
                                    $sidebarInitials = mb_strtoupper(collect(explode(' ', $sidebarName))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->join('')) ?: 'U';
                                @endphp
                                {{ $sidebarInitials }}
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="text-white font-medium truncate text-sm">{{ Auth::user()->name }}</div>
                            <div class="text-slate-400 text-xs truncate">{{ Auth::user()->email }}</div>
                        </div>
                    </button>
                    <div
                        id="sidebarProfileDropdownMenu"
                        class="absolute left-4 right-4 bottom-full mb-2 rounded-xl border border-white/10 bg-slate-900/95 backdrop-blur shadow-xl py-1 hidden z-50"
                        role="menu"
                        aria-orientation="vertical"
                        data-position="dropup"
                    >
                        <a href="{{ route('profile.show') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-300 hover:bg-white/5 hover:text-white transition" role="menuitem">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Profile
                        </a>
                        <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-300 hover:bg-white/5 hover:text-white transition" role="menuitem">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Settings
                        </a>
                        <div class="border-t border-white/10 mt-1 pt-1">
                            <form action="{{ route('logout') }}" method="POST" class="confirm-logout">
                                @csrf
                                <button type="submit" class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-slate-300 hover:bg-white/5 hover:text-red-400 transition text-left" role="menuitem">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <div id="appMain" class="flex flex-1 w-full min-w-0 flex-col min-h-screen ml-0 lg:ml-64">
                <!-- Header: sticky (html/body no longer use overflow-x-hidden; clip is on .app-scroll-clip below) -->
                <nav class="sticky top-0 z-30 shrink-0 border-b border-white/5 bg-slate-900/70 backdrop-blur pt-[env(safe-area-inset-top,0px)] @if(request()->routeIs('camera.*')) hidden @endif">
                    <div class="shell flex items-center justify-between py-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="text-white font-semibold text-lg truncate">Polaris Attendance</div>
                        </div>

                        @php
                            $topbarUser = auth()->user();
                            $topbarUnreadCount = $topbarUser?->unreadNotifications()->count() ?? 0;
                            $topbarNotifications = $topbarUser
                                ? $topbarUser->notifications()->latest()->limit(10)->get()
                                : collect();
                        @endphp
                        <div class="flex items-center gap-2 sm:gap-3 text-sm text-slate-300 shrink-0">
                            @if(auth()->user()?->role === 'admin')
                                <form action="{{ route('global-search') }}" method="GET" class="relative hidden md:block w-64 lg:w-72">
                                    <label for="topbar-search-input" class="sr-only">Search</label>
                                    <input
                                        type="text"
                                        id="topbar-search-input"
                                        name="search"
                                        value="{{ request('search') }}"
                                        autocomplete="off"
                                        placeholder="Search users, attendance…"
                                        data-global-search="true"
                                        data-global-search-target="top"
                                        class="w-full h-10 px-4 pl-11 text-sm bg-white/5 border border-white/10 rounded-xl text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50"
                                    />
                                    <button type="submit" class="sr-only">Search</button>
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </span>
                                    <div
                                        id="global-search-suggestions-top"
                                        class="absolute z-50 top-full left-0 right-0 mt-2 hidden overflow-hidden rounded-xl border border-white/10 bg-slate-900/95 shadow-xl max-h-96 overflow-y-auto"
                                        role="listbox"
                                        aria-label="Search suggestions"
                                    ></div>
                                </form>
                            @endif
                                <span id="topbarClock" class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-slate-200 tabular-nums"></span>
                            <div class="relative shrink-0" id="topbarNotificationsWrap">
                                <button
                                    type="button"
                                    id="topbarNotificationsBtn"
                                    class="relative inline-flex items-center justify-center p-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/5 transition focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                    aria-label="Notifications"
                                    title="Notifications"
                                    aria-expanded="false"
                                    aria-haspopup="true"
                                    aria-controls="topbarNotificationsPanel"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    @if($topbarUnreadCount > 0)
                                        <span class="absolute -top-0.5 -right-0.5 inline-flex min-w-[1.125rem] items-center justify-center rounded-full bg-red-500 px-1 py-0.5 text-[10px] font-semibold leading-none text-white">
                                            {{ $topbarUnreadCount > 9 ? '9+' : $topbarUnreadCount }}
                                        </span>
                                    @endif
                                </button>
                                <div
                                    id="topbarNotificationsPanel"
                                    class="hidden absolute right-0 mt-2 w-[min(100vw-2rem,22rem)] max-w-[calc(100vw-2rem)] rounded-xl border border-white/10 bg-slate-900/95 backdrop-blur-md shadow-xl z-50"
                                    role="region"
                                    aria-label="Notification list"
                                >
                                    <div class="flex items-center justify-between gap-2 px-4 py-3 border-b border-white/10">
                                        <p class="text-sm font-medium text-slate-100">Notifications</p>
                                        @if($topbarUnreadCount > 0)
                                            <form method="POST" action="{{ route('notifications.markAllRead') }}" class="shrink-0">
                                                @csrf
                                                <button type="submit" class="text-xs text-blue-400 hover:text-blue-300 whitespace-nowrap">Mark all read</button>
                                            </form>
                                        @endif
                                    </div>
                                    <div class="max-h-80 overflow-y-auto">
                                        @if($topbarNotifications->isEmpty())
                                            <div class="px-4 py-8 text-sm text-slate-400 text-center">No notifications yet.</div>
                                        @else
                                            <ul class="divide-y divide-white/5">
                                                @foreach($topbarNotifications as $notification)
                                                    @php
                                                        $rowMessage = $notification->data['message'] ?? class_basename($notification->type);
                                                        $announcementId = $notification->data['announcement_id'] ?? null;
                                                        $rowHref = $announcementId
                                                            ? route('announcements.show', $announcementId)
                                                            : route('notifications.index');
                                                        $rowUnread = $notification->read_at === null;
                                                    @endphp
                                                    <li>
                                                        <a
                                                            href="{{ $rowHref }}"
                                                            class="flex items-start gap-2 px-4 py-3 text-sm text-slate-100 hover:bg-white/5 transition"
                                                        >
                                                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full shrink-0 {{ $rowUnread ? 'bg-blue-400' : 'bg-slate-600' }}" title="{{ $rowUnread ? 'Unread' : 'Read' }}"></span>
                                                            <span class="min-w-0 flex-1">
                                                                <span class="block text-slate-100 leading-snug break-words">{{ $rowMessage }}</span>
                                                                <span class="mt-1 block text-[11px] text-slate-400">{{ $notification->created_at?->diffForHumans() }}</span>
                                                            </span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <div class="border-t border-white/10 px-4 py-2.5">
                                        <a href="{{ route('notifications.index') }}" class="text-xs font-medium text-blue-400 hover:text-blue-300">View all notifications</a>
                                    </div>
                                </div>
                            </div>

                            {{-- Profile menu: sidebar is hidden below lg, so expose account actions in the top bar --}}
                            <div class="relative shrink-0 lg:hidden" id="topbarProfileWrap">
                                @php
                                    $topbarDisplayName = $topbarUser?->name ?? 'User';
                                    $topbarInitials = mb_strtoupper(collect(explode(' ', $topbarDisplayName))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->join('')) ?: 'U';
                                @endphp
                                <button
                                    type="button"
                                    id="topbarProfileBtn"
                                    class="flex items-center gap-1.5 rounded-lg p-1.5 text-slate-300 hover:bg-white/5 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                    aria-label="Account menu"
                                    aria-expanded="false"
                                    aria-haspopup="true"
                                    aria-controls="topbarProfileMenu"
                                >
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/10 bg-white/10 text-xs font-semibold text-white">
                                        @if($topbarUser?->profile_photo_url)
                                            <img src="{{ $topbarUser->profile_photo_url }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            {{ $topbarInitials }}
                                        @endif
                                    </span>
                                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div
                                    id="topbarProfileMenu"
                                    class="hidden absolute right-0 z-50 mt-2 w-56 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-white/10 bg-slate-900/95 shadow-xl backdrop-blur-md"
                                    role="menu"
                                    aria-labelledby="topbarProfileBtn"
                                >
                                    <div class="border-b border-white/10 px-4 py-3">
                                        <p class="truncate text-sm font-medium text-white">{{ $topbarDisplayName }}</p>
                                        <p class="mt-0.5 truncate text-xs text-slate-400">{{ $topbarUser?->email }}</p>
                                    </div>
                                    <a href="{{ route('profile.show') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-200 hover:bg-white/5 hover:text-white transition" role="menuitem">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        Profile
                                    </a>
                                    <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-200 hover:bg-white/5 hover:text-white transition" role="menuitem">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        Settings
                                    </a>
                                    <form action="{{ route('logout') }}" method="POST" class="confirm-logout border-t border-white/10">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-slate-200 hover:bg-white/5 hover:text-red-300 transition" role="menuitem">
                                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                <div class="app-scroll-clip">
                <main @class([
                    'app-main-shell w-full min-w-0',
                    'shell mt-8 space-y-6 grow pb-10 max-lg:pb-[calc(env(safe-area-inset-bottom,0px)+4rem)]' => ! request()->routeIs('camera.*'),
                    'mt-0 max-w-none grow p-0 pb-0' => request()->routeIs('camera.*'),
                ])>
                    @if (session('status'))
                        <div class="alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert-error">
                            <div class="font-semibold mb-2">Please fix the following errors:</div>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')

                    {{-- Extra scroll height on mobile so the last pixels clear the fixed bottom nav (document scroll) --}}
                    @unless(request()->routeIs('camera.*'))
                        <div
                            class="lg:hidden shrink-0 w-full"
                            style="min-height: calc(2.25rem + env(safe-area-inset-bottom, 0px));"
                            aria-hidden="true"
                        ></div>
                    @endunless
                </main>

                <!-- Footer (authenticated) -->
                <footer class="app-footer hidden border-t border-white/10 bg-slate-900/50 backdrop-blur-md mt-auto lg:block">
                    <div class="shell py-6">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div class="text-slate-400 text-sm">
                                <p>&copy; {{ date('Y') }} Polaris Multipurpose Cooperative. All rights reserved.</p>
                            </div>
                            <div class="flex items-center gap-6 text-sm">
                                <a href="{{ route('privacy-policy') }}" class="text-slate-400 hover:text-white transition">Privacy Policy</a>
                                <a href="{{ route('terms-of-service') }}" class="text-slate-400 hover:text-white transition">Terms of Service</a>
                                <a href="{{ route('contact') }}" class="text-slate-400 hover:text-white transition">Contact</a>
                            </div>
                        </div>
                    </div>
                </footer>
                </div>
            </div>
            @unless(request()->routeIs('camera.*'))
                <x-bottom-nav />
            @endunless
        @else
            <!-- Public pages without sidebar -->
            <div class="w-full flex flex-col min-h-screen">
                <nav class="sticky top-0 z-30 shrink-0 border-b border-white/5 bg-slate-900/70 backdrop-blur pt-[env(safe-area-inset-top,0px)]">
                    <div class="shell flex items-center justify-between py-4">
                        <div class="text-white font-semibold text-lg">Polaris Attendance</div>
                        <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('login') }}" class="btn-primary">Login</a>
                        </div>
                    </div>
                </nav>

                <div class="app-scroll-clip">
                <main class="shell mt-8 space-y-6 pb-10 flex-1">
                    @if (session('status'))
                        <div class="alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert-error">
                            <div class="font-semibold mb-2">Please fix the following errors:</div>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </main>

                <!-- Footer -->
                <footer class="border-t border-white/10 bg-slate-900/50 backdrop-blur-md mt-auto">
                    <div class="shell py-6">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div class="text-slate-400 text-sm">
                                <p>&copy; {{ date('Y') }} Polaris Multipurpose Cooperative. All rights reserved.</p>
                            </div>
                            <div class="flex items-center gap-6 text-sm">
                                <a href="{{ route('privacy-policy') }}" class="text-slate-400 hover:text-white transition">Privacy Policy</a>
                                <a href="{{ route('terms-of-service') }}" class="text-slate-400 hover:text-white transition">Terms of Service</a>
                                <a href="{{ route('contact') }}" class="text-slate-400 hover:text-white transition">Contact</a>
                            </div>
                        </div>
                    </div>
                </footer>
                </div>
            </div>
        @endauth
    </div>

    @auth
    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-slate-950 flex items-center justify-center z-50 hidden opacity-0 pointer-events-none transition-opacity duration-300" aria-hidden="true">
        <div id="logoutModalContent" class="bg-slate-900 rounded-xl border border-slate-700 p-8 max-w-md w-full mx-4 transform scale-95 transition-transform duration-300 shadow-2xl">
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-500/20 mx-auto mb-4">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2m-6-4a9 9 0 1118 0 9 9 0 01-18 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white text-center mb-2">Logout</h3>
            <p class="text-slate-300 text-center mb-6">Are you sure you want to logout? Your saved data will be preserved.</p>
            <div class="flex gap-3 justify-center">
                <button type="button" id="logoutCancel" class="px-6 py-2.5 rounded-lg bg-slate-700 border border-slate-600 text-slate-200 hover:bg-slate-600 transition">Cancel</button>
                <button type="button" id="logoutConfirm" class="px-6 py-2.5 rounded-lg bg-red-600 hover:bg-red-500 text-white font-medium transition">Logout</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Topbar live clock
            const clockEl = document.getElementById('topbarClock');
            if (clockEl) {
                const fmt = new Intl.DateTimeFormat(undefined, { hour: '2-digit', minute: '2-digit' });
                const tick = () => { clockEl.textContent = fmt.format(new Date()); };
                tick();
                setInterval(tick, 1000 * 30);
            }

            // Topbar notifications + mobile profile dropdowns
            const topbarNotifWrap = document.getElementById('topbarNotificationsWrap');
            const topbarNotifBtn = document.getElementById('topbarNotificationsBtn');
            const topbarNotifPanel = document.getElementById('topbarNotificationsPanel');
            const topbarProfileWrap = document.getElementById('topbarProfileWrap');
            const topbarProfileBtn = document.getElementById('topbarProfileBtn');
            const topbarProfileMenu = document.getElementById('topbarProfileMenu');

            function closeTopbarNotifications() {
                if (!topbarNotifPanel || !topbarNotifBtn) return;
                topbarNotifPanel.classList.add('hidden');
                topbarNotifBtn.setAttribute('aria-expanded', 'false');
            }
            function closeTopbarProfile() {
                if (!topbarProfileMenu || !topbarProfileBtn) return;
                topbarProfileMenu.classList.add('hidden');
                topbarProfileBtn.setAttribute('aria-expanded', 'false');
            }
            function openTopbarNotifications() {
                if (!topbarNotifPanel || !topbarNotifBtn) return;
                closeTopbarProfile();
                topbarNotifPanel.classList.remove('hidden');
                topbarNotifBtn.setAttribute('aria-expanded', 'true');
            }
            function openTopbarProfile() {
                if (!topbarProfileMenu || !topbarProfileBtn) return;
                closeTopbarNotifications();
                topbarProfileMenu.classList.remove('hidden');
                topbarProfileBtn.setAttribute('aria-expanded', 'true');
            }
            function closeTopbarDropdowns() {
                closeTopbarNotifications();
                closeTopbarProfile();
            }
            if (topbarNotifWrap && topbarNotifBtn && topbarNotifPanel) {
                topbarNotifBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (topbarNotifPanel.classList.contains('hidden')) {
                        openTopbarNotifications();
                    } else {
                        closeTopbarNotifications();
                    }
                });
                topbarNotifWrap.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            }
            if (topbarProfileWrap && topbarProfileBtn && topbarProfileMenu) {
                topbarProfileBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (topbarProfileMenu.classList.contains('hidden')) {
                        openTopbarProfile();
                    } else {
                        closeTopbarProfile();
                    }
                });
                topbarProfileWrap.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            }
            document.addEventListener('click', function () {
                closeTopbarDropdowns();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeTopbarDropdowns();
                }
            });

            // Sidebar collapse/expand toggle (desktop)
            const appSidebar = document.getElementById('appSidebar');
            const sidebarToggleBtn = document.getElementById('appSidebarToggleDesktop');
            const storageKey = 'appSidebarCollapsed';

            function setSidebarCollapsed(collapsed) {
                if (!appSidebar) return;
                if (collapsed) {
                    document.body.classList.add('sidebar-collapsed');
                    localStorage.setItem(storageKey, 'true');
                } else {
                    document.body.classList.remove('sidebar-collapsed');
                    localStorage.setItem(storageKey, 'false');
                }
                updateSidebarToggleIcon();
            }

            function updateSidebarToggleIcon() {
                if (!sidebarToggleBtn || !appSidebar) return;
                const iconPath = sidebarToggleBtn.querySelector('svg path');
                if (!iconPath) return;
                const collapsed = document.body.classList.contains('sidebar-collapsed');
                if (collapsed) {
                    // >> (expand)
                    iconPath.setAttribute('d', 'M13 5l7 7-7 7M5 5l7 7-7 7');
                    sidebarToggleBtn.title = 'Show sidebar';
                } else {
                    // << (collapse)
                    iconPath.setAttribute('d', 'M11 19l-7-7 7-7m8 14l-7-7 7-7');
                    sidebarToggleBtn.title = 'Hide sidebar';
                }
            }

            function updateSidebarProfileDropdownPosition() {
                const sidebarProfileMenu = document.getElementById('sidebarProfileDropdownMenu');
                if (!sidebarProfileMenu) return;

                const collapsed = document.body.classList.contains('sidebar-collapsed');

                // Reset to base "drop up" layout anchored above the profile area
                sidebarProfileMenu.classList.remove(
                    'left-full',
                    'right-auto',
                    'bottom-4',
                    'top-auto',
                    'translate-x-0',
                    'ml-0'
                );
                sidebarProfileMenu.classList.add('bottom-full', 'left-4', 'right-4');

                // When the sidebar is collapsed, show the dropdown to the side
                if (collapsed) {
                    sidebarProfileMenu.classList.remove('left-4', 'right-4', 'bottom-full');
                    sidebarProfileMenu.classList.add('left-full', 'bottom-4');
                }
            }

            if (sidebarToggleBtn && appSidebar) {
                sidebarToggleBtn.addEventListener('click', function () {
                    const collapsed = document.body.classList.contains('sidebar-collapsed');
                    setSidebarCollapsed(!collapsed);
                    updateSidebarProfileDropdownPosition();
                });
            }

            // Initialize sidebar state on desktop
            if (appSidebar && window.innerWidth >= 1024) {
                const collapsed = localStorage.getItem(storageKey) === 'true';
                if (collapsed) {
                    document.body.classList.add('sidebar-collapsed');
                } else {
                    document.body.classList.remove('sidebar-collapsed');
                }
                updateSidebarToggleIcon();
                updateSidebarProfileDropdownPosition();
            } else {
                document.body.classList.remove('sidebar-collapsed');
                updateSidebarToggleIcon();
                updateSidebarProfileDropdownPosition();
            }

            const modal = document.getElementById('logoutModal');
            const modalContent = document.getElementById('logoutModalContent');
            const cancelBtn = document.getElementById('logoutCancel');
            const confirmBtn = document.getElementById('logoutConfirm');
            let pendingForm = null;

            if (!modal || !modalContent) return;

            function showModal() {
                modal.classList.remove('hidden');
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.classList.add('pointer-events-auto');
                modal.setAttribute('aria-hidden', 'false');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }

            function hideModal() {
                modal.classList.add('opacity-0', 'pointer-events-none');
                modal.classList.remove('pointer-events-auto');
                modalContent.classList.add('scale-95');
                modalContent.classList.remove('scale-100');
                modal.setAttribute('aria-hidden', 'true');
                setTimeout(() => modal.classList.add('hidden'), 300);
                pendingForm = null;
            }

            // Ensure hidden on initial load
            hideModal();

            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!form || !form.classList.contains('confirm-logout')) return;
                e.preventDefault();
                pendingForm = form;
                showModal();
            });

            if (cancelBtn) {
                cancelBtn.addEventListener('click', hideModal);
            }

            if (confirmBtn) {
                confirmBtn.addEventListener('click', function () {
                    if (pendingForm) {
                        pendingForm.submit();
                    }
                    hideModal();
                });
            }

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    hideModal();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.classList.contains('pointer-events-none')) {
                    hideModal();
                }
            });

            // Sidebar profile dropdown
            const sidebarProfileWrap = document.getElementById('sidebarProfileDropdownWrap');
            const sidebarProfileTrigger = document.getElementById('sidebarProfileDropdownTrigger');
            const sidebarProfileMenu = document.getElementById('sidebarProfileDropdownMenu');
            if (sidebarProfileWrap && sidebarProfileTrigger && sidebarProfileMenu) {
                sidebarProfileTrigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const open = !sidebarProfileMenu.classList.contains('hidden');
                    sidebarProfileMenu.classList.toggle('hidden', open);
                    sidebarProfileTrigger.setAttribute('aria-expanded', !open);
                });
                document.addEventListener('click', function () {
                    sidebarProfileMenu.classList.add('hidden');
                    sidebarProfileTrigger.setAttribute('aria-expanded', 'false');
                });
                sidebarProfileWrap.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        sidebarProfileMenu.classList.add('hidden');
                        sidebarProfileTrigger.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        });
    </script>
    @endauth

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    @stack('scripts')
</body>
</html>


