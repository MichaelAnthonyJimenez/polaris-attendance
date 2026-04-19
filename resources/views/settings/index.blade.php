@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-white">Settings</h1>
            <p class="text-slate-400 mt-1">
                @if($userRole === 'admin')
                    Manage system settings and preferences
                @else
                    Manage your personal preferences and notifications
                @endif
            </p>
        </div>
        @if(count($settings) > 0)
        <button id="sidebarToggle" class="lg:hidden btn-secondary flex items-center justify-center gap-2 w-full sm:w-auto">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            <span>Menu</span>
        </button>
        @endif
    </div>

    <div class="grid grid-cols-1 {{ count($settings) > 0 ? 'lg:grid-cols-4' : '' }} gap-6">
        @if(count($settings) > 0)
        <!-- Sidebar Navigation -->
        <div id="settingsSidebar" class="lg:col-span-1 hidden lg:block transition-all duration-300">
            <div class="glass p-4 sticky top-24">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Settings Categories</h3>
                    <div class="flex items-center gap-2">
                        <button id="sidebarToggleDesktop" class="hidden lg:inline-flex items-center justify-center p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/50" title="Hide sidebar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button id="sidebarCloseBtn" class="lg:hidden inline-flex items-center justify-center p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <nav class="space-y-1">
                    @if($userRole === 'driver')
                        <a href="#group-driver-live-location"
                           class="settings-nav-link block px-3 py-2 rounded-lg text-sm text-slate-300 hover:bg-white/5 hover:text-white transition-colors"
                           data-group="group-driver-live-location">
                            Location
                        </a>
                    @endif
                    @foreach($settings as $group => $groupSettings)
                        @php
                            $groupLabels = [
                                'admin_attendance' => 'Attendance',
                                'admin_backup' => 'Backup',
                                'admin_compliance' => 'Compliance',
                                'admin_driver_management' => 'Management',
                                'admin_email' => 'Email',
                                'admin_export' => 'Export',
                                'admin_face_recognition' => 'Face Recognition',
                                'admin_location' => 'Location',
                                'admin_notifications' => 'Notifications',
                                'admin_performance' => 'Performance',
                                'admin_reports' => 'Reports',
                                'admin_security' => 'Security',
                                'admin_system' => 'System',
                                'driver_accessibility' => 'Accessibility',
                                'driver_attendance' => 'Attendance',
                                'driver_camera' => 'Camera',
                                'driver_dashboard' => 'Dashboard',
                                'driver_data_usage' => 'Data usage',
                                'driver_notifications' => 'Notifications',
                                'driver_privacy' => 'Privacy',
                                'driver_profile' => 'Profile',
                                'driver_reminders' => 'Reminders',
                                'driver_security' => 'Security',
                            ];
                            $groupTitle = $groupLabels[$group] ?? null;
                            if ($groupTitle === null) {
                                $groupTitle = str_replace('_', ' ', $group);
                                $groupTitle = str_replace('admin ', '', $groupTitle);
                                $groupTitle = str_replace('driver ', '', $groupTitle);
                                $groupTitle = ucwords($groupTitle);
                            }
                            $groupSlug = 'group-' . str_replace([' ', '_'], '-', strtolower($group));
                        @endphp
                        <a href="#{{ $groupSlug }}" 
                           class="settings-nav-link block px-3 py-2 rounded-lg text-sm text-slate-300 hover:bg-white/5 hover:text-white transition-colors"
                           data-group="{{ $groupSlug }}">
                            {{ $groupTitle }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
        @endif

        <!-- Settings Content -->
        <div id="settingsContent" class="{{ count($settings) > 0 ? 'lg:col-span-3' : '' }} transition-all duration-300">
            @if(count($settings) === 0)
                <div class="glass p-8 text-center">
                    <svg class="w-16 h-16 text-slate-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-white mb-2">No Settings Found</h3>
                    <p class="text-slate-400 mb-6">Settings need to be initialized. Please run the seeder or refresh the page.</p>
                    <a href="{{ route('settings.index') }}" class="btn-primary inline-block">Refresh Page</a>
                </div>
            @else
            @if($userRole === 'driver')
                <div id="group-driver-live-location" class="glass p-6 scroll-mt-24 mb-6 sm:mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 rounded-lg bg-blue-500/20">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-white">Location</h2>
                            <p class="text-sm text-slate-400">Allow access for check-in/check-out and live location tracking.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('settings.location-sharing') }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="location_sharing_enabled" value="0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox"
                                       name="location_sharing_enabled"
                                       value="1"
                                       {{ !empty($driverLocationSharingEnabled) ? 'checked' : '' }}
                                       onchange="this.form.submit()"
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                                <span class="ml-3 text-sm text-slate-300">
                                    {{ !empty($driverLocationSharingEnabled) ? 'On (tracking active)' : 'Off (tracking disabled)' }}
                                </span>
                            </label>
                        </div>
                        <p class="text-xs text-slate-400">This updates automatically when toggled.</p>
                    </form>
                </div>
            @endif
            <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                @foreach($settings as $group => $groupSettings)
                    @php
                        $groupLabels = [
                            'admin_attendance' => 'Attendance',
                            'admin_backup' => 'Backup',
                            'admin_compliance' => 'Compliance',
                            'admin_driver_management' => 'Management',
                            'admin_email' => 'Email',
                            'admin_export' => 'Export',
                            'admin_face_recognition' => 'Face Recognition',
                            'admin_location' => 'Location',
                            'admin_notifications' => 'Notifications',
                            'admin_performance' => 'Performance',
                            'admin_reports' => 'Reports',
                            'admin_security' => 'Security',
                            'admin_system' => 'System',
                            'driver_accessibility' => 'Accessibility',
                            'driver_attendance' => 'Attendance',
                            'driver_camera' => 'Camera',
                            'driver_dashboard' => 'Dashboard',
                            'driver_data_usage' => 'Data usage',
                            'driver_notifications' => 'Notifications',
                            'driver_privacy' => 'Privacy',
                            'driver_profile' => 'Profile',
                            'driver_reminders' => 'Reminders',
                            'driver_security' => 'Security',
                        ];
                        $groupTitle = $groupLabels[$group] ?? null;
                        if ($groupTitle === null) {
                            $groupTitle = str_replace('_', ' ', $group);
                            $groupTitle = str_replace('admin ', '', $groupTitle);
                            $groupTitle = str_replace('driver ', '', $groupTitle);
                            $groupTitle = ucwords($groupTitle);
                        }
                        $groupSlug = 'group-' . str_replace([' ', '_'], '-', strtolower($group));
                        
                        // Icons for different groups
                        $groupIcons = [
                        'general' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                        'system' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                        'attendance' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                        'face recognition' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                        'notifications' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
                        'security' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                        'display' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
                        'preferences' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4',
                        'backup' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
                        'reports' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                        'api' => 'M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                        'location' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
                        'driver management' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                        'export' => 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                        'performance' => 'M13 10V3L4 14h7v7l9-11h-7z',
                        'compliance' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                        'email' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                        'privacy' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                        'accessibility' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
                        'dashboard' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                        'data usage' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                        'profile' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                        'reminders' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                        ];
                        $iconPath = $groupIcons[strtolower($groupTitle)] ?? 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z';
                    @endphp
                    <div id="{{ $groupSlug }}" class="glass p-6 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 rounded-lg bg-blue-500/20">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-white">{{ $groupTitle }}</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach($groupSettings as $setting)
                        <div class="space-y-2">
                            @php
                                $customLabels = [
                                    'driver_approval_required' => 'Approval Required',
                                    'driver_browser_notify_checkin' => 'Notify Checkin',
                                    'driver_browser_notify_checkout' => 'Notify Checkout',
                                    'driver_announcement_in_app' => 'Announcements in Notification Bell',
                                    'driver_announcement_email' => 'Announcements via Email',
                                ];
                                $label = $customLabels[$setting->key] ?? null;
                                if ($label === null) {
                                    $label = str_replace('_', ' ', $setting->key);
                                    $label = str_replace('driver ', '', $label);
                                    $label = str_replace('admin ', '', $label);
                                    $label = ucwords($label);
                                }
                            @endphp
                            <label class="form-label">{{ $label }}</label>
                            @if($setting->type === 'boolean')
                                <div class="flex items-center gap-3">
                                    <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               name="settings[{{ $setting->key }}]" 
                                               value="1" 
                                               {{ $setting->value == '1' ? 'checked' : '' }}
                                               class="sr-only peer toggle-checkbox"
                                               data-status-id="status-{{ $setting->key }}">
                                        <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                                        <span class="ml-3 text-sm text-slate-300">
                                            <span id="status-{{ $setting->key }}">{{ $setting->value == '1' ? 'Enabled' : 'Disabled' }}</span>
                                        </span>
                                    </label>
                                </div>
                            @elseif($setting->type === 'json')
                                <textarea name="settings[{{ $setting->key }}]" 
                                          class="form-input" 
                                          rows="3"
                                          placeholder="Enter JSON data">{{ $setting->value }}</textarea>
                            @elseif($setting->key === 'theme' || $setting->key === 'driver_theme')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="dark" {{ $setting->value == 'dark' ? 'selected' : '' }}>Dark</option>
                                    <option value="light" {{ $setting->value == 'light' ? 'selected' : '' }}>Light</option>
                                    <option value="auto" {{ $setting->value == 'auto' ? 'selected' : '' }}>Auto</option>
                                </select>
                            @elseif($setting->key === 'driver_language')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="en" {{ $setting->value == 'en' ? 'selected' : '' }}>English</option>
                                    <option value="es" {{ $setting->value == 'es' ? 'selected' : '' }}>Spanish</option>
                                    <option value="fr" {{ $setting->value == 'fr' ? 'selected' : '' }}>French</option>
                                    <option value="de" {{ $setting->value == 'de' ? 'selected' : '' }}>German</option>
                                </select>
                            @elseif($setting->key === 'timezone' || $setting->key === 'driver_timezone')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="UTC" {{ $setting->value == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="America/New_York" {{ $setting->value == 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                    <option value="America/Chicago" {{ $setting->value == 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                    <option value="America/Denver" {{ $setting->value == 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                    <option value="America/Los_Angeles" {{ $setting->value == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                    <option value="Europe/London" {{ $setting->value == 'Europe/London' ? 'selected' : '' }}>London</option>
                                    <option value="Asia/Manila" {{ $setting->value == 'Asia/Manila' ? 'selected' : '' }}>Manila</option>
                                    <option value="Asia/Singapore" {{ $setting->value == 'Asia/Singapore' ? 'selected' : '' }}>Singapore</option>
                                    <option value="Asia/Tokyo" {{ $setting->value == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                                    <option value="Australia/Sydney" {{ $setting->value == 'Australia/Sydney' ? 'selected' : '' }}>Sydney</option>
                                </select>
                            @elseif($setting->key === 'backup_frequency')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="daily" {{ $setting->value == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ $setting->value == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ $setting->value == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            @elseif($setting->key === 'backup_location')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="local" {{ $setting->value == 'local' ? 'selected' : '' }}>Local Storage</option>
                                    <option value="cloud" {{ $setting->value == 'cloud' ? 'selected' : '' }}>Cloud Storage</option>
                                    <option value="both" {{ $setting->value == 'both' ? 'selected' : '' }}>Both</option>
                                </select>
                            @elseif($setting->key === 'report_format')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="pdf" {{ $setting->value == 'pdf' ? 'selected' : '' }}>PDF</option>
                                    <option value="excel" {{ $setting->value == 'excel' ? 'selected' : '' }}>Excel</option>
                                    <option value="csv" {{ $setting->value == 'csv' ? 'selected' : '' }}>CSV</option>
                                </select>
                            @elseif($setting->key === 'smtp_encryption')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="tls" {{ $setting->value == 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ $setting->value == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="none" {{ $setting->value == 'none' ? 'selected' : '' }}>None</option>
                                </select>
                            @elseif($setting->key === 'driver_font_size')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="small" {{ $setting->value == 'small' ? 'selected' : '' }}>Small</option>
                                    <option value="medium" {{ $setting->value == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="large" {{ $setting->value == 'large' ? 'selected' : '' }}>Large</option>
                                    <option value="xlarge" {{ $setting->value == 'xlarge' ? 'selected' : '' }}>Extra Large</option>
                                </select>
                            @elseif($setting->key === 'driver_dashboard_layout')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="default" {{ $setting->value == 'default' ? 'selected' : '' }}>Default</option>
                                    <option value="compact" {{ $setting->value == 'compact' ? 'selected' : '' }}>Compact</option>
                                    <option value="spacious" {{ $setting->value == 'spacious' ? 'selected' : '' }}>Spacious</option>
                                    <option value="minimal" {{ $setting->value == 'minimal' ? 'selected' : '' }}>Minimal</option>
                                </select>
                            @elseif($setting->key === 'default_language')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="en" {{ $setting->value == 'en' ? 'selected' : '' }}>English</option>
                                    <option value="es" {{ $setting->value == 'es' ? 'selected' : '' }}>Spanish</option>
                                    <option value="fr" {{ $setting->value == 'fr' ? 'selected' : '' }}>French</option>
                                    <option value="de" {{ $setting->value == 'de' ? 'selected' : '' }}>German</option>
                                    <option value="it" {{ $setting->value == 'it' ? 'selected' : '' }}>Italian</option>
                                    <option value="pt" {{ $setting->value == 'pt' ? 'selected' : '' }}>Portuguese</option>
                                    <option value="zh" {{ $setting->value == 'zh' ? 'selected' : '' }}>Chinese</option>
                                    <option value="ja" {{ $setting->value == 'ja' ? 'selected' : '' }}>Japanese</option>
                                </select>
                            @elseif($setting->key === 'currency')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="USD" {{ $setting->value == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ $setting->value == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    <option value="GBP" {{ $setting->value == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                    <option value="JPY" {{ $setting->value == 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen</option>
                                    <option value="AUD" {{ $setting->value == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                                    <option value="CAD" {{ $setting->value == 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                    <option value="CHF" {{ $setting->value == 'CHF' ? 'selected' : '' }}>CHF - Swiss Franc</option>
                                    <option value="CNY" {{ $setting->value == 'CNY' ? 'selected' : '' }}>CNY - Chinese Yuan</option>
                                </select>
                            @elseif($setting->key === 'country')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="US" {{ $setting->value == 'US' ? 'selected' : '' }}>United States</option>
                                    <option value="GB" {{ $setting->value == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="CA" {{ $setting->value == 'CA' ? 'selected' : '' }}>Canada</option>
                                    <option value="AU" {{ $setting->value == 'AU' ? 'selected' : '' }}>Australia</option>
                                    <option value="DE" {{ $setting->value == 'DE' ? 'selected' : '' }}>Germany</option>
                                    <option value="FR" {{ $setting->value == 'FR' ? 'selected' : '' }}>France</option>
                                    <option value="IT" {{ $setting->value == 'IT' ? 'selected' : '' }}>Italy</option>
                                    <option value="ES" {{ $setting->value == 'ES' ? 'selected' : '' }}>Spain</option>
                                    <option value="JP" {{ $setting->value == 'JP' ? 'selected' : '' }}>Japan</option>
                                    <option value="CN" {{ $setting->value == 'CN' ? 'selected' : '' }}>China</option>
                                    <option value="IN" {{ $setting->value == 'IN' ? 'selected' : '' }}>India</option>
                                    <option value="BR" {{ $setting->value == 'BR' ? 'selected' : '' }}>Brazil</option>
                                    <option value="MX" {{ $setting->value == 'MX' ? 'selected' : '' }}>Mexico</option>
                                    <option value="PH" {{ $setting->value == 'PH' ? 'selected' : '' }}>Philippines</option>
                                </select>
                            @elseif($setting->key === 'default_theme')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="dark" {{ $setting->value == 'dark' ? 'selected' : '' }}>Dark</option>
                                    <option value="light" {{ $setting->value == 'light' ? 'selected' : '' }}>Light</option>
                                    <option value="auto" {{ $setting->value == 'auto' ? 'selected' : '' }}>Auto</option>
                                </select>
                            @elseif($setting->key === 'backup_frequency' && $setting->group === 'general')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="daily" {{ $setting->value == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ $setting->value == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ $setting->value == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            @elseif($setting->key === 'date_format' && $setting->group === 'general')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="Y-m-d" {{ $setting->value == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                    <option value="m/d/Y" {{ $setting->value == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                    <option value="d/m/Y" {{ $setting->value == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                    <option value="M d, Y" {{ $setting->value == 'M d, Y' ? 'selected' : '' }}>Jan 01, 2024</option>
                                    <option value="d M Y" {{ $setting->value == 'd M Y' ? 'selected' : '' }}>01 Jan 2024</option>
                                </select>
                            @elseif($setting->key === 'time_format' && $setting->group === 'general')
                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                    <option value="H:i" {{ $setting->value == 'H:i' ? 'selected' : '' }}>24-hour (HH:MM)</option>
                                    <option value="h:i A" {{ $setting->value == 'h:i A' ? 'selected' : '' }}>12-hour (HH:MM AM/PM)</option>
                                    <option value="h:i a" {{ $setting->value == 'h:i a' ? 'selected' : '' }}>12-hour (HH:MM am/pm)</option>
                                </select>
                            @elseif(in_array($setting->key, ['driver_checkin_reminder_time', 'driver_checkout_reminder_time'], true))
                                <input type="time" 
                                       name="settings[{{ $setting->key }}]" 
                                       value="{{ strlen((string) $setting->value) >= 5 ? substr($setting->value, 0, 5) : $setting->value }}" 
                                       class="form-input"
                                       step="60">
                            @else
                                <input type="{{ $setting->type === 'integer' ? 'number' : 'text' }}" 
                                       name="settings[{{ $setting->key }}]" 
                                       value="{{ $setting->value }}" 
                                       class="form-input"
                                       placeholder="{{ $setting->description }}">
                            @endif
                            @if($setting->description)
                                <p class="text-xs text-slate-400 mt-1">{{ $setting->description }}</p>
                            @endif
                        </div>
                    @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end gap-3">
                    <a href="{{ route('dashboard') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Settings</button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle functionality
        const sidebar = document.getElementById('settingsSidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
        const settingsContent = document.getElementById('settingsContent');
        const gridContainer = sidebar?.parentElement;
        
        // Check localStorage for sidebar state
        const sidebarCollapsed = localStorage.getItem('settingsSidebarCollapsed') === 'true';
        
        // Initialize sidebar state
        function initSidebar() {
            if (sidebarCollapsed && window.innerWidth >= 1024) {
                collapseSidebar();
            }
        }
        
        function collapseSidebar() {
            if (!sidebar || !gridContainer) return;
            
            sidebar.classList.add('hidden');
            if (settingsContent) {
                settingsContent.classList.remove('lg:col-span-3');
                settingsContent.classList.add('lg:col-span-4');
            }
            gridContainer.classList.remove('lg:grid-cols-4');
            gridContainer.classList.add('lg:grid-cols-1');
            localStorage.setItem('settingsSidebarCollapsed', 'true');
            updateToggleIcon();
        }
        
        function expandSidebar() {
            if (!sidebar || !gridContainer) return;
            
            sidebar.classList.remove('hidden');
            if (settingsContent) {
                settingsContent.classList.remove('lg:col-span-4');
                settingsContent.classList.add('lg:col-span-3');
            }
            gridContainer.classList.remove('lg:grid-cols-1');
            gridContainer.classList.add('lg:grid-cols-4');
            localStorage.setItem('settingsSidebarCollapsed', 'false');
            updateToggleIcon();
        }
        
        function toggleSidebar() {
            if (sidebar?.classList.contains('hidden')) {
                expandSidebar();
            } else {
                collapseSidebar();
            }
        }
        
        // Mobile toggle button
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                if (sidebar) {
                    sidebar.classList.toggle('hidden');
                    // On mobile, show as overlay
                    if (window.innerWidth < 1024) {
                        sidebar.classList.add('fixed', 'inset-0', 'z-50', 'bg-slate-900/95', 'backdrop-blur-md');
                        sidebar.classList.remove('lg:col-span-1');
                    }
                }
            });
        }
        
        // Close button (mobile)
        if (sidebarCloseBtn) {
            sidebarCloseBtn.addEventListener('click', function() {
                if (sidebar && window.innerWidth < 1024) {
                    sidebar.classList.add('hidden');
                    sidebar.classList.remove('fixed', 'inset-0', 'z-50', 'bg-slate-900/95', 'backdrop-blur-md');
                }
            });
        }
        
        // Update toggle button icon on sidebar state change
        function updateToggleIcon() {
            const sidebarToggleDesktop = document.getElementById('sidebarToggleDesktop');
            if (sidebarToggleDesktop && window.innerWidth >= 1024) {
                const icon = sidebarToggleDesktop.querySelector('svg path');
                if (icon) {
                    if (sidebar?.classList.contains('hidden')) {
                        // Show right chevron (>>) when collapsed - click to expand
                        icon.setAttribute('d', 'M13 5l7 7-7 7M5 5l7 7-7 7');
                        sidebarToggleDesktop.title = 'Show sidebar';
                    } else {
                        // Show left chevron (<<) when expanded - click to collapse
                        icon.setAttribute('d', 'M11 19l-7-7 7-7m8 14l-7-7 7-7');
                        sidebarToggleDesktop.title = 'Hide sidebar';
                    }
                }
            }
        }
        
        // Desktop toggle button
        const sidebarToggleDesktop = document.getElementById('sidebarToggleDesktop');
        if (sidebarToggleDesktop) {
            sidebarToggleDesktop.addEventListener('click', function() {
                if (window.innerWidth >= 1024) {
                    toggleSidebar();
                }
            });
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebarToggleDesktop = document.getElementById('sidebarToggleDesktop');
            if (window.innerWidth >= 1024) {
                // Desktop: restore saved state
                initSidebar();
                if (sidebar) {
                    sidebar.classList.remove('fixed', 'inset-0', 'z-50', 'bg-slate-900/95', 'backdrop-blur-md');
                }
                // Ensure toggle button is visible on desktop
                if (sidebarToggleDesktop) {
                    sidebarToggleDesktop.style.display = 'inline-flex';
                }
                updateToggleIcon();
            } else {
                // Mobile: always hide sidebar by default
                if (sidebar) {
                    sidebar.classList.add('hidden');
                }
                // Hide desktop toggle button on mobile
                if (sidebarToggleDesktop) {
                    sidebarToggleDesktop.style.display = 'none';
                }
            }
        });
        
        // Initialize on load
        initSidebar();
        
        // Ensure toggle button is visible on desktop after a short delay (to ensure DOM is ready)
        setTimeout(function() {
            if (window.innerWidth >= 1024) {
                const sidebarToggleDesktop = document.getElementById('sidebarToggleDesktop');
                if (sidebarToggleDesktop) {
                    // Force show on desktop
                    sidebarToggleDesktop.style.display = 'inline-flex';
                }
            }
            updateToggleIcon();
        }, 100);
        
        // Update toggle status text when changed
        document.querySelectorAll('.toggle-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const statusId = this.getAttribute('data-status-id');
                const statusElement = document.getElementById(statusId);
                if (statusElement) {
                    statusElement.textContent = this.checked ? 'Enabled' : 'Disabled';
                }
            });
        });

        // Smooth scroll for navigation links
        document.querySelectorAll('.settings-nav-link').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    // Update active state
                    document.querySelectorAll('.settings-nav-link').forEach(l => l.classList.remove('bg-blue-500/20', 'text-blue-300'));
                    this.classList.add('bg-blue-500/20', 'text-blue-300');
                    
                    // Close sidebar on mobile after clicking
                    if (window.innerWidth < 1024 && sidebar) {
                        sidebar.classList.add('hidden');
                        sidebar.classList.remove('fixed', 'inset-0', 'z-50', 'bg-slate-900/95', 'backdrop-blur-md');
                    }
                }
            });
        });

        // Highlight active section on scroll
        const observerOptions = {
            root: null,
            rootMargin: '-20% 0px -60% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const groupId = entry.target.id;
                    document.querySelectorAll('.settings-nav-link').forEach(link => {
                        link.classList.remove('bg-blue-500/20', 'text-blue-300');
                        if (link.getAttribute('href') === '#' + groupId) {
                            link.classList.add('bg-blue-500/20', 'text-blue-300');
                        }
                    });
                }
            });
        }, observerOptions);

        document.querySelectorAll('[id^="group-"]').forEach(section => {
            observer.observe(section);
        });
    });
</script>
@endpush

