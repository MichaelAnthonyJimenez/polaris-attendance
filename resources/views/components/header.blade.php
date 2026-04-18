<!-- Header -->
<header class="sticky top-0 z-10 bg-slate-900/80 backdrop-blur-md border-b border-white/10">
    <div class="shell py-4">
        <div class="flex items-center gap-3">
            <!-- Search (admin only) - desktop/tablet (mobile search is in Bottom Nav → More) -->
            @if(auth()->user()?->role === 'admin')
                <form action="{{ route('search') }}" method="GET" class="relative hidden md:block flex-1 min-w-0 max-w-[22rem] sm:max-w-none sm:w-72" id="header-search-wrap">
                    <label for="header-search-input-desktop" class="sr-only">Search</label>
                    <input
                        type="text"
                        id="header-search-input-desktop"
                        name="q"
                        autocomplete="off"
                        placeholder="Search users, attendance…"
                        class="w-full px-3 py-2 pl-9 text-sm bg-white/5 border border-white/10 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50"
                    />
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                </form>
            @endif

            <div class="flex items-center gap-2 sm:gap-3 shrink-0 ml-auto">
                @php
                    $unreadNotifications = auth()->user()?->unreadNotifications;
                    $unreadCount = $unreadNotifications?->count() ?? 0;
                @endphp

                <!-- Notifications (desktop only) -->
                <div class="relative group hidden md:block">
                    <button type="button" class="relative p-2 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        @if($unreadCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full bg-red-500 text-[10px] font-semibold leading-none">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </button>
                    <div class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-slate-800 backdrop-blur-md border border-white/10 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible group-focus-within:opacity-100 group-focus-within:visible transition-all duration-200">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-white/5">
                            <p class="text-sm font-medium text-slate-100">Notifications</p>
                            @if($unreadCount > 0)
                                <form method="POST" action="{{ route('notifications.markAllRead') }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-400 hover:text-blue-300">Mark all as read</button>
                                </form>
                            @endif
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            @if($unreadCount === 0)
                                <div class="px-4 py-6 text-sm text-slate-400 text-center">You have no new notifications.</div>
                            @else
                                <ul class="divide-y divide-white/5">
                                    @foreach($unreadNotifications->take(10) as $notification)
                                        <li class="px-4 py-3 text-sm text-slate-100">
                                            <div class="flex items-start gap-2">
                                                <span class="mt-1 w-1.5 h-1.5 rounded-full bg-blue-400 flex-shrink-0"></span>
                                                <div>
                                                    <p class="text-sm">{{ $notification->data['message'] ?? class_basename($notification->type) }}</p>
                                                    <p class="text-[11px] text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>

                @if(auth()->user()?->role === 'driver')
                <button type="button" class="relative p-2 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg transition md:hidden" aria-label="Notifications">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    @if($unreadCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full bg-red-500 text-[10px] font-semibold leading-none">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </button>
                @endif

                <!-- User dropdown (mobile only) -->
                <div id="headerUserWrapper" class="relative flex md:hidden">
                    <button
                        type="button"
                        id="headerUserToggle"
                        class="flex items-center gap-2 p-1.5 pr-2 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg transition md:hidden"
                        aria-expanded="false"
                        aria-controls="headerUserMenu"
                    >
                        <span class="w-8 h-8 rounded-full overflow-hidden bg-blue-500/20 border border-blue-500/40 flex items-center justify-center flex-shrink-0">
                            @if(auth()->user()?->profile_photo_url)
                                <img src="{{ auth()->user()?->profile_photo_url }}" alt="Profile" class="w-full h-full object-cover">
                            @else
                                <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            @endif
                        </span>
                        <span class="hidden sm:block text-sm font-medium max-w-[10rem] truncate">{{ auth()->user()?->name ?? 'User' }}</span>
                        <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div
                        id="headerUserMenu"
                        class="hidden absolute right-0 mt-2 w-56 max-w-[calc(100vw-2rem)] rounded-lg bg-slate-800 border border-white/10 overflow-hidden shadow-xl md:hidden"
                        role="menu"
                        aria-labelledby="headerUserToggle"
                    >
                        <a href="{{ route('profile.show') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-200 hover:bg-slate-700 hover:text-white transition border-b border-white/10" role="menuitem">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>My Profile</span>
                        </a>
                        <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-200 hover:bg-slate-700 hover:text-white transition border-b border-white/10" role="menuitem">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Settings</span>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="block confirm-logout">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-slate-200 hover:bg-slate-700 hover:text-white transition text-left" role="menuitem">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Time (click to show time + calendar) -->
                <div class="relative group/time hidden md:block">
                    <button type="button" id="header-time-btn" class="flex items-center gap-1.5 px-2.5 py-2 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg transition text-sm tabular-nums">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span id="header-time-display">--:--</span>
                    </button>
                    <div id="header-time-popover" class="absolute right-0 mt-2 w-64 bg-slate-800 backdrop-blur-md border border-white/10 rounded-lg shadow-xl opacity-0 invisible transition-all duration-200 pointer-events-none group-hover/time:opacity-100 group-hover/time:visible group-hover/time:pointer-events-auto">
                        <div class="p-4 border-b border-white/5">
                            <p class="text-2xl font-semibold text-white tabular-nums" id="header-time-popover-time">--:--:--</p>
                            <p class="text-sm text-slate-400 mt-0.5" id="header-time-popover-date">--</p>
                        </div>
                        <div class="p-4">
                            <div id="header-mini-calendar" class="grid grid-cols-7 gap-1 text-center text-xs">
                                <!-- Filled by JS -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dark / Light mode toggle -->
                <button type="button" id="headerThemeToggle" class="p-2 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg transition" onclick="toggleThemeAdmin()" title="Toggle dark/light mode">
                    <svg id="headerMoonIcon" class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path>
                    </svg>
                    <svg id="headerSunIcon" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>

<script>
(function() {
    function pad(n) { return n < 10 ? '0' + n : n; }
    function formatTime(d) {
        return pad(d.getHours()) + ':' + pad(d.getMinutes());
    }
    function formatTimeLong(d) {
        return pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
    }
    function formatDate(d) {
        const opts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        return d.toLocaleDateString(undefined, opts);
    }
    function renderMiniCalendar(container) {
        if (!container) return;
        var d = new Date();
        var year = d.getFullYear();
        var month = d.getMonth();
        var first = new Date(year, month, 1);
        var last = new Date(year, month + 1, 0);
        var startDay = first.getDay();
        var daysInMonth = last.getDate();
        var dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        var html = '';
        dayNames.forEach(function(day) {
            html += '<span class="text-slate-400 font-medium">' + day + '</span>';
        });
        var empty = startDay;
        while (empty--) html += '<span></span>';
        for (var i = 1; i <= daysInMonth; i++) {
            var isToday = (i === d.getDate() && new Date().getMonth() === month && new Date().getFullYear() === year);
            html += '<span class="' + (isToday ? 'bg-blue-500/30 text-white rounded font-medium' : 'text-slate-300') + '">' + i + '</span>';
        }
        container.innerHTML = html;
    }
    function updateHeaderTime() {
        var d = new Date();
        var timeEl = document.getElementById('header-time-display');
        var popoverTime = document.getElementById('header-time-popover-time');
        var popoverDate = document.getElementById('header-time-popover-date');
        if (timeEl) timeEl.textContent = formatTime(d);
        if (popoverTime) popoverTime.textContent = formatTimeLong(d);
        if (popoverDate) popoverDate.textContent = formatDate(d);
        renderMiniCalendar(document.getElementById('header-mini-calendar'));
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            updateHeaderTime();
            setInterval(updateHeaderTime, 1000);
        });
    } else {
        updateHeaderTime();
        setInterval(updateHeaderTime, 1000);
    }
})();

function toggleThemeAdmin() {
    var html = document.documentElement;
    var isDark = html.classList.contains('dark');
    if (typeof setTheme === 'function') setTheme(isDark ? 'light' : 'dark');
}

// Header user dropdown (mobile only - click to toggle)
(function() {
    const toggle = document.getElementById('headerUserToggle');
    const menu = document.getElementById('headerUserMenu');
    const wrapper = document.getElementById('headerUserWrapper');
    if (!toggle || !menu || !wrapper) return;

    const desktopMql = window.matchMedia('(min-width: 768px)');

    const closeMenu = () => {
        menu.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
    };

    const syncVisibilityToViewport = () => {
        if (desktopMql.matches) {
            wrapper.classList.add('hidden');
            closeMenu();
        } else {
            wrapper.classList.remove('hidden');
        }
    };

    toggle.addEventListener('click', function(e) {
        if (desktopMql.matches) {
            closeMenu();
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        const isOpen = !menu.classList.contains('hidden');
        if (isOpen) closeMenu();
        else {
            menu.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
        }
    });

    document.addEventListener('click', function(e) {
        if (menu.classList.contains('hidden')) return;
        if (menu.contains(e.target) || toggle.contains(e.target)) return;
        closeMenu();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMenu();
    });

    syncVisibilityToViewport();
    if (typeof desktopMql.addEventListener === 'function') {
        desktopMql.addEventListener('change', syncVisibilityToViewport);
    } else if (typeof desktopMql.addListener === 'function') {
        desktopMql.addListener(syncVisibilityToViewport);
    }
    window.addEventListener('resize', syncVisibilityToViewport);
})();
</script>

