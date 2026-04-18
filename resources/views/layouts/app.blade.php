<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polaris Attendance</title>
    @include('components.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head-scripts')
</head>
<body class="@auth role-{{ auth()->user()?->role ?? 'user' }} @endauth">
    <div class="min-h-screen flex">
        @auth
            @php $isCameraPage = request()->routeIs('camera.*', 'verification.facial', 'verification.id'); @endphp
            <!-- Sidebar -->
            <aside id="appSidebar" class="@if($isCameraPage) hidden @else hidden lg:flex @endif lg:flex-col w-64 bg-slate-900/80 backdrop-blur-md border-r border-white/10 fixed h-screen z-20">
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
                            <p class="text-xs text-slate-400 mt-1 truncate">Attendance Management</p>
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
                        <span>{{ auth()->user()?->role === 'driver' ? 'History' : 'Attendance' }}</span>
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
                            @if(auth()->user()?->profile_photo_url)
                                <img src="{{ auth()->user()?->profile_photo_url }}" alt="" class="w-full h-full object-cover">
                            @else
                                @php
                                    $sidebarName = auth()->user()?->name ?? 'User';
                                    $sidebarInitials = mb_strtoupper(collect(explode(' ', $sidebarName))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->join('')) ?: 'U';
                                @endphp
                                {{ $sidebarInitials }}
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="text-white font-medium truncate text-sm">{{ auth()->user()?->name ?? 'User' }}</div>
                            <div class="text-slate-400 text-xs truncate">{{ auth()->user()?->email ?? '' }}</div>
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
            <div id="appMain" class="flex-1 @if($isCameraPage) ml-0 @else ml-0 lg:ml-64 @endif flex flex-col min-h-screen">
                <!-- Header (authenticated) -->
                @unless($isCameraPage)
                <nav class="sticky top-0 z-30 backdrop-blur bg-slate-900/70 border-b border-white/5">
                    <div class="shell flex items-center justify-between py-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="text-white font-semibold text-lg truncate">Polaris Attendance</div>
                        </div>

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
                            @if(auth()->user()?->role !== 'driver')
                                <span id="topbarClock" class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-slate-200 tabular-nums"></span>
                            @endif
                            @php
                                $notificationsTableExists = \Illuminate\Support\Facades\Schema::hasTable('notifications');
                                $unreadNotifications = $notificationsTableExists ? auth()->user()?->unreadNotifications : collect();
                                $unreadCount = $unreadNotifications?->count() ?? 0;
                                $recentNotifications = $notificationsTableExists
                                    ? (auth()->user()?->notifications()->latest()->limit(10)->get() ?? collect())
                                    : collect();
                            @endphp
                            <div class="relative" id="topbarNotifWrap">
                                <button
                                    type="button"
                                    id="topbarNotifToggle"
                                    class="inline-flex items-center justify-center p-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/5 transition focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                    aria-label="Notifications"
                                    aria-expanded="false"
                                    aria-haspopup="true"
                                    title="Notifications"
                                >
                                    <span class="relative">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                        @if($unreadCount > 0)
                                            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full bg-red-500 text-[10px] font-semibold leading-none">
                                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                            </span>
                                        @endif
                                    </span>
                                </button>
                                <div
                                    id="topbarNotifMenu"
                                    class="hidden absolute right-0 top-full mt-2 w-80 max-w-[calc(100vw-2rem)] rounded-xl border border-white/10 bg-slate-900/95 backdrop-blur shadow-xl z-50"
                                    role="menu"
                                >
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                                        <p class="text-sm font-medium text-slate-100">Notifications</p>
                                        @if($unreadCount > 0)
                                            <form method="POST" action="{{ route('notifications.markAllRead') }}">
                                                @csrf
                                                <button type="submit" class="text-xs text-blue-400 hover:text-blue-300">Mark all as read</button>
                                            </form>
                                        @endif
                                    </div>
                                    <div class="max-h-80 overflow-y-auto">
                                        @if($recentNotifications->isEmpty())
                                            <div class="px-4 py-6 text-sm text-slate-400 text-center">You have no notifications.</div>
                                        @else
                                            <ul class="divide-y divide-white/10">
                                                @foreach($recentNotifications as $notification)
                                                    @php
                                                        $isUnread = $notification->read_at === null;
                                                        $message = $notification->data['message'] ?? class_basename($notification->type);
                                                    @endphp
                                                    <li class="px-4 py-3 text-sm text-slate-100 {{ $isUnread ? 'bg-blue-500/5' : '' }}">
                                                        <div class="flex items-start gap-2">
                                                            <span class="mt-1 w-1.5 h-1.5 rounded-full {{ $isUnread ? 'bg-blue-400' : 'bg-slate-500' }} flex-shrink-0"></span>
                                                            <div class="min-w-0">
                                                                <p class="text-sm break-words">{{ $message }}</p>
                                                                <p class="text-[11px] text-slate-400 mt-1">{{ $notification->created_at?->diffForHumans() }}</p>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <div class="px-4 py-2 border-t border-white/10">
                                        <a href="{{ route('notifications.index') }}" class="text-xs text-blue-400 hover:text-blue-300">View all notifications</a>
                                    </div>
                                </div>
                            </div>

                            <div class="relative lg:hidden" id="topbarProfileDropdownWrap">
                                <button
                                    type="button"
                                    id="topbarProfileDropdownTrigger"
                                    class="inline-flex items-center gap-2 pl-2 pr-2.5 py-1.5 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                    aria-label="Profile menu"
                                    aria-expanded="false"
                                    aria-haspopup="true"
                                >
                                    <span class="flex items-center justify-center w-8 h-8 rounded-full overflow-hidden bg-slate-600 text-white font-medium text-xs border border-white/10 shrink-0">
                                        @if(auth()->user()?->profile_photo_url)
                                            <img src="{{ auth()->user()?->profile_photo_url }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            @php
                                                $topbarName = auth()->user()?->name ?? 'User';
                                                $topbarInitials = mb_strtoupper(collect(explode(' ', $topbarName))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->join('')) ?: 'U';
                                            @endphp
                                            {{ $topbarInitials }}
                                        @endif
                                    </span>
                                    <span class="hidden sm:inline text-slate-200 font-medium text-sm truncate max-w-[10rem]">{{ auth()->user()?->name ?? 'User' }}</span>
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div
                                    id="topbarProfileDropdownMenu"
                                    class="absolute right-0 top-full mt-2 w-56 rounded-xl border border-white/10 bg-slate-900/95 backdrop-blur shadow-xl py-1 hidden z-50"
                                    role="menu"
                                    aria-orientation="vertical"
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
                        </div>
                    </div>
                </nav>
                @endunless

                {{-- Extra bottom padding below lg: fixed bottom nav (~4rem) + safe area so content scrolls fully into view --}}
                <main class="shell @if($isCameraPage) mt-0 p-0 max-w-none mx-0 px-0 sm:px-0 flex-1 min-h-0 @else mt-4 lg:mt-8 space-y-4 lg:space-y-6 pb-[calc(5rem+env(safe-area-inset-bottom,0px)+0.5rem)] lg:pb-10 flex-1 @endif">
                    @unless($isCameraPage)
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
                    @endunless

                    @yield('content')
                </main>

                @unless($isCameraPage)
                <!-- Footer (authenticated; hidden on mobile — bottom nav + less clutter) -->
                <footer class="app-footer hidden lg:block border-t border-white/10 bg-slate-900/50 backdrop-blur-md mt-auto">
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

                @include('components.bottom-nav')
                @endunless
            </div>
        @else
            <!-- Public pages without sidebar -->
            <div class="w-full flex flex-col min-h-screen">
                <nav class="sticky top-0 z-30 backdrop-blur bg-slate-900/70 border-b border-white/5">
                    <div class="shell flex items-center justify-between py-4">
                        <div class="text-white font-semibold text-lg">Polaris Attendance</div>
                        <div class="flex items-center gap-3 text-sm">
                            @if (!request()->routeIs('login') && !request()->routeIs('register'))
                                <a href="{{ route('login') }}" class="btn-primary">Login</a>
                            @endif
                        </div>
                    </div>
                </nav>

                <main class="shell mt-8 space-y-6 pb-16 sm:pb-20 lg:pb-10 flex-1">
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
        @endauth
    </div>

    @auth
    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-transparent flex items-center justify-center z-50 hidden opacity-0 pointer-events-none transition-opacity duration-300" aria-hidden="true">
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

    <!-- Generic Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 bg-transparent flex items-center justify-center z-50 hidden opacity-0 pointer-events-none transition-opacity duration-300" aria-hidden="true">
        <div id="confirmModalContent" class="bg-slate-900 rounded-xl border border-slate-700 p-8 max-w-md w-full mx-4 transform scale-95 transition-transform duration-300 shadow-2xl">
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-rose-500/20 mx-auto mb-4">
                <svg class="w-6 h-6 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m-6-4a9 9 0 1118 0 9 9 0 01-18 0z"></path>
                </svg>
            </div>
            <h3 id="confirmModalTitle" class="text-xl font-bold text-white text-center mb-2">Confirm</h3>
            <p id="confirmModalMessage" class="text-slate-300 text-center mb-6">Are you sure?</p>
            <div class="flex gap-3 justify-center">
                <button type="button" id="confirmModalCancel" class="px-6 py-2.5 rounded-lg bg-slate-700 border border-slate-600 text-slate-200 hover:bg-slate-600 transition">Cancel</button>
                <button type="button" id="confirmModalConfirm" class="px-6 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-medium transition">Confirm</button>
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

            // Generic confirm modal (deletes, destructive actions, etc.)
            const confirmModal = document.getElementById('confirmModal');
            const confirmModalContent = document.getElementById('confirmModalContent');
            const confirmTitle = document.getElementById('confirmModalTitle');
            const confirmMessage = document.getElementById('confirmModalMessage');
            const confirmCancel = document.getElementById('confirmModalCancel');
            const confirmConfirm = document.getElementById('confirmModalConfirm');
            let pendingConfirmForm = null;

            function showConfirmModal({ title, message, confirmText } = {}) {
                if (!confirmModal || !confirmModalContent) return;
                if (confirmTitle) confirmTitle.textContent = title || 'Confirm';
                if (confirmMessage) confirmMessage.textContent = message || 'Are you sure?';
                if (confirmConfirm) confirmConfirm.textContent = confirmText || 'Confirm';

                confirmModal.classList.remove('hidden');
                confirmModal.classList.remove('opacity-0', 'pointer-events-none');
                confirmModal.classList.add('pointer-events-auto');
                confirmModal.setAttribute('aria-hidden', 'false');
                confirmModalContent.classList.remove('scale-95');
                confirmModalContent.classList.add('scale-100');
            }

            function hideConfirmModal() {
                if (!confirmModal || !confirmModalContent) return;
                confirmModal.classList.add('opacity-0', 'pointer-events-none');
                confirmModal.classList.remove('pointer-events-auto');
                confirmModalContent.classList.add('scale-95');
                confirmModalContent.classList.remove('scale-100');
                confirmModal.setAttribute('aria-hidden', 'true');
                setTimeout(() => confirmModal.classList.add('hidden'), 300);
                pendingConfirmForm = null;
            }

            if (confirmModal && confirmModalContent) {
                hideConfirmModal();

                document.addEventListener('submit', function (e) {
                    const form = e.target;
                    if (!form || form.getAttribute('data-confirm-modal') !== 'true') return;

                    e.preventDefault();
                    pendingConfirmForm = form;

                    showConfirmModal({
                        title: form.getAttribute('data-confirm-title') || 'Confirm',
                        message: form.getAttribute('data-confirm-message') || 'Are you sure?',
                        confirmText: form.getAttribute('data-confirm-confirm-text') || 'Confirm',
                    });
                });

                if (confirmCancel) confirmCancel.addEventListener('click', hideConfirmModal);

                if (confirmConfirm) {
                    confirmConfirm.addEventListener('click', function () {
                        if (pendingConfirmForm) pendingConfirmForm.submit();
                        hideConfirmModal();
                    });
                }

                confirmModal.addEventListener('click', function (e) {
                    if (e.target === confirmModal) hideConfirmModal();
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && !confirmModal.classList.contains('pointer-events-none')) {
                        hideConfirmModal();
                    }
                });
            }

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

            // Topbar profile dropdown
            const topbarProfileWrap = document.getElementById('topbarProfileDropdownWrap');
            const topbarProfileTrigger = document.getElementById('topbarProfileDropdownTrigger');
            const topbarProfileMenu = document.getElementById('topbarProfileDropdownMenu');
            if (topbarProfileWrap && topbarProfileTrigger && topbarProfileMenu) {
                topbarProfileTrigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const open = !topbarProfileMenu.classList.contains('hidden');
                    topbarProfileMenu.classList.toggle('hidden', open);
                    topbarProfileTrigger.setAttribute('aria-expanded', !open);
                });
                document.addEventListener('click', function () {
                    topbarProfileMenu.classList.add('hidden');
                    topbarProfileTrigger.setAttribute('aria-expanded', 'false');
                });
                topbarProfileWrap.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        topbarProfileMenu.classList.add('hidden');
                        topbarProfileTrigger.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            // Topbar notifications dropdown
            const topbarNotifWrap = document.getElementById('topbarNotifWrap');
            const topbarNotifToggle = document.getElementById('topbarNotifToggle');
            const topbarNotifMenu = document.getElementById('topbarNotifMenu');
            if (topbarNotifWrap && topbarNotifToggle && topbarNotifMenu) {
                topbarNotifToggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const open = !topbarNotifMenu.classList.contains('hidden');
                    topbarNotifMenu.classList.toggle('hidden', open);
                    topbarNotifToggle.setAttribute('aria-expanded', (!open).toString());
                });
                document.addEventListener('click', function () {
                    topbarNotifMenu.classList.add('hidden');
                    topbarNotifToggle.setAttribute('aria-expanded', 'false');
                });
                topbarNotifWrap.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        topbarNotifMenu.classList.add('hidden');
                        topbarNotifToggle.setAttribute('aria-expanded', 'false');
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


