@php
    $showBottomNav = request()->routeIs('dashboard')
        || request()->routeIs('camera.*')
        || request()->routeIs('attendance.*')
        || request()->routeIs('announcements.*')
        || request()->routeIs('driver-verification.*')
        || request()->routeIs('verification.*')
        || request()->routeIs('profile.*')
        || request()->routeIs('settings.index')
        || request()->routeIs('reports.*')
        || request()->routeIs('audit-logs.*')
        || request()->routeIs('users.*')
        || request()->routeIs('locations.*');
@endphp
@if($showBottomNav)
<div class="fixed bottom-0 left-0 right-0 z-40 w-full lg:hidden" style="padding-bottom: env(safe-area-inset-bottom, 0);">
    <nav class="flex w-full min-h-16 bg-slate-900/80 backdrop-blur-md border-t border-white/10 transition-colors duration-300" aria-label="Bottom navigation">
        <div class="flex items-center justify-around w-full min-h-16 px-1 gap-1">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center flex-1 py-2 min-w-0 text-slate-300 hover:text-white transition-colors {{ request()->routeIs('dashboard') ? 'text-blue-300' : '' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span class="text-xs mt-0.5 truncate max-w-full">Home</span>
        </a>

        @if(auth()->user()?->role === 'admin')
        <a href="{{ route('users.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 min-w-0 text-slate-300 hover:text-white transition-colors {{ request()->routeIs('users.*') ? 'text-blue-300' : '' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <span class="text-xs mt-0.5 truncate max-w-full">Users</span>
        </a>
        @endif

        <a href="{{ route('attendance.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 min-w-0 text-slate-300 hover:text-white transition-colors {{ request()->routeIs('attendance.*') ? 'text-blue-300' : '' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-xs mt-0.5 truncate max-w-full">{{ auth()->user()?->role === 'driver' ? 'History' : 'Attendance' }}</span>
        </a>

        @if(auth()->user()?->role === 'admin')
        <a href="{{ route('driver-verification.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 min-w-0 text-slate-300 hover:text-white transition-colors {{ request()->routeIs('driver-verification.*') ? 'text-blue-300' : '' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            <span class="text-xs mt-0.5 truncate max-w-full">Verify</span>
        </a>
        @elseif(auth()->user()?->role === 'driver')
        <a href="{{ route('announcements.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 min-w-0 text-slate-300 hover:text-white transition-colors {{ request()->routeIs('announcements.*') ? 'text-blue-300' : '' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1a1 1 0 01-1-1V8a4 4 0 118 0v7a1 1 0 01-1 1h-1m-7 0H9m4 4H9a2 2 0 01-2-2v-1m0-1a2 2 0 012-2h1"></path>
            </svg>
            <span class="text-xs mt-0.5 truncate max-w-full">Announcements</span>
        </a>
        @else
        <a href="{{ route('profile.show') }}" class="flex flex-col items-center justify-center flex-1 py-2 min-w-0 text-slate-300 hover:text-white transition-colors {{ request()->routeIs('profile.*') ? 'text-blue-300' : '' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="text-xs mt-0.5 truncate max-w-full">Profile</span>
        </a>
        @endif

        @if(auth()->user()?->role === 'admin')
        <button type="button" id="bottomNavMoreBtn" class="flex flex-col items-center justify-center flex-1 py-2 min-w-0 text-slate-300 hover:text-white transition-colors {{ (request()->routeIs('settings.index') || request()->routeIs('reports.*') || request()->routeIs('audit-logs.*') || request()->routeIs('locations.*')) ? 'text-blue-300' : '' }}" aria-label="More options">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            <span class="text-xs mt-0.5 truncate max-w-full">More</span>
        </button>
        @elseif(auth()->user()?->role === 'driver')
        <a href="{{ route('settings.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 min-w-0 text-slate-300 hover:text-white transition-colors {{ request()->routeIs('settings.index') ? 'text-blue-300' : '' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span class="text-xs mt-0.5 truncate max-w-full">Settings</span>
        </a>
        @else
        <a href="{{ route('settings.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 min-w-0 text-slate-300 hover:text-white transition-colors {{ request()->routeIs('settings.index') ? 'text-blue-300' : '' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span class="text-xs mt-0.5 truncate max-w-full">Settings</span>
        </a>
        @endif
        </div>
    </nav>

    @if(auth()->user()?->role === 'admin')
    <!-- More options slide-up panel (admin only) -->
    <div id="bottomNavMorePanel" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div id="bottomNavMoreBackdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm" aria-hidden="true"></div>
        <div id="bottomNavMoreSheet" class="absolute bottom-0 left-0 right-0 max-h-[70vh] overflow-y-auto bg-slate-900 border-t border-white/10 rounded-t-2xl shadow-xl transform transition-transform duration-300 translate-y-full" style="padding-bottom: env(safe-area-inset-bottom, 0);">
            <div class="p-4">
                <div class="w-12 h-1 bg-white/20 rounded-full mx-auto mb-4"></div>
                <h2 class="text-lg font-semibold text-white mb-4">More</h2>
                <!-- Mobile search (admin only) -->
                <form action="{{ route('global-search') }}" method="GET" class="relative mb-4" id="bottom-nav-search-wrap">
                    <label for="bottom-nav-search-input" class="sr-only">Search</label>
                    <input
                        type="text"
                        id="bottom-nav-search-input"
                        name="search"
                        value="{{ request('search') }}"
                        autocomplete="off"
                        placeholder="Search users, attendance…"
                        data-global-search="true"
                        data-global-search-target="bottom"
                        class="w-full px-4 py-3 pl-11 text-sm bg-white/5 border border-white/10 rounded-xl text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50"
                    />
                    <button type="submit" class="sr-only">Search</button>
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <div
                        id="global-search-suggestions-bottom"
                        class="absolute z-50 top-full left-0 right-0 mt-2 hidden overflow-hidden rounded-xl border border-white/10 bg-slate-900/95 shadow-xl max-h-96 overflow-y-auto"
                        role="listbox"
                        aria-label="Search suggestions"
                    ></div>
                </form>
                <div class="space-y-1">
                    @if(auth()->user()?->role === 'admin')
                    <a href="{{ route('locations.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-200 hover:bg-white/5 hover:text-white transition-colors {{ request()->routeIs('locations.*') ? 'bg-blue-500/20 text-blue-200 border border-blue-500/30' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>Locations</span>
                    </a>
                    <a href="{{ route('announcements.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-200 hover:bg-white/5 hover:text-white transition-colors {{ request()->routeIs('announcements.*') ? 'bg-blue-500/20 text-blue-200 border border-blue-500/30' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1a1 1 0 01-1-1V8a4 4 0 118 0v7a1 1 0 01-1 1h-1m-7 0H9m4 4H9a2 2 0 01-2-2v-1m0-1a2 2 0 012-2h1"></path></svg>
                        <span>Announcements</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-200 hover:bg-white/5 hover:text-white transition-colors {{ request()->routeIs('reports.*') ? 'bg-blue-500/20 text-blue-200 border border-blue-500/30' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Reports</span>
                    </a>
                    <a href="{{ route('audit-logs.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-200 hover:bg-white/5 hover:text-white transition-colors {{ request()->routeIs('audit-logs.*') ? 'bg-blue-500/20 text-blue-200 border border-blue-500/30' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Audit Logs</span>
                    </a>
                    @endif
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-200 hover:bg-white/5 hover:text-white transition-colors {{ request()->routeIs('settings.index') ? 'bg-blue-500/20 text-blue-200 border border-blue-500/30' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>Settings</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function() {
            const btn = document.getElementById('bottomNavMoreBtn');
            const panel = document.getElementById('bottomNavMorePanel');
            const backdrop = document.getElementById('bottomNavMoreBackdrop');
            const sheet = document.getElementById('bottomNavMoreSheet');

            if (!btn || !panel) return;

            function openMore() {
                panel.classList.remove('hidden');
                panel.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                requestAnimationFrame(() => sheet.classList.remove('translate-y-full'));
            }

            function closeMore() {
                sheet.classList.add('translate-y-full');
                setTimeout(() => {
                    panel.classList.add('hidden');
                    panel.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                }, 300);
            }

            btn.addEventListener('click', openMore);
            backdrop.addEventListener('click', closeMore);

            panel.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => closeMore());
            });
        })();
    </script>
    @endif
</div>
@endif
