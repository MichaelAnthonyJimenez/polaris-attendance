<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $role = $user->role ?? 'driver';
        
        // Ensure all settings are seeded (firstOrCreate will only create missing ones)
        // Check if we have a reasonable number of settings, if not, seed them
        if (Setting::count() < 100) {
            Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);
        }
        
        // Filter settings based on user role
        $query = Setting::orderBy('group')->orderBy('key');
        
        if ($role === 'admin') {
            // Admin can see: general, admin_*, and all driver settings
            $query->where(function($q) {
                $q->where('group', 'general')
                  ->orWhere('group', 'like', 'admin_%')
                  ->orWhere('group', 'like', 'driver_%');
            });
        } else {
            // Drivers can only see: limited general settings and essential driver_* settings
            // Define which general settings drivers can see (only personal preferences)
            $allowedGeneralSettings = [
                'timezone',
                'default_language',
                'date_format',
                'time_format',
                'default_theme',
                'enable_dark_mode',
                'enable_notifications',
                'notification_sound',
            ];
            
            // Define which driver setting groups drivers can see
            $allowedDriverGroups = [
                'driver_preferences',
                'driver_notifications',
                'driver_attendance',
                'driver_reminders',
            ];
            
            $query->where(function($q) use ($allowedGeneralSettings, $allowedDriverGroups) {
                // Only specific general settings
                $q->where(function($subQ) use ($allowedGeneralSettings) {
                    $subQ->where('group', 'general')
                         ->whereIn('key', $allowedGeneralSettings);
                })
                // Or specific driver setting groups only
                ->orWhere(function($subQ) use ($allowedDriverGroups) {
                    $subQ->whereIn('group', $allowedDriverGroups);
                });
            });
        }
        
        $settings = $query->get()->groupBy('group');

        return view('settings.index', [
            'settings' => $settings,
            'userRole' => $role,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $role = $user->role ?? 'driver';
        
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable'],
        ]);

        $changedSettings = [];
        foreach ($validated['settings'] as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            
            if (!$setting) {
                continue;
            }
            
            // Check if user has permission to update this setting
            if ($role === 'driver') {
                // Drivers cannot update admin settings
                if (str_starts_with($setting->group, 'admin_')) {
                    continue;
                }
                
                // Drivers can only update specific general settings
                if ($setting->group === 'general') {
                    $allowedGeneralSettings = [
                        'timezone',
                        'default_language',
                        'date_format',
                        'time_format',
                        'default_theme',
                        'enable_dark_mode',
                        'enable_notifications',
                        'notification_sound',
                    ];
                    if (!in_array($setting->key, $allowedGeneralSettings)) {
                        continue;
                    }
                }
                
                // Drivers can only update specific driver groups
                $allowedDriverGroups = [
                    'driver_preferences',
                    'driver_notifications',
                    'driver_attendance',
                    'driver_reminders',
                ];
                if (!in_array($setting->group, $allowedDriverGroups)) {
                    continue;
                }
            }
            
            if ($setting->value != $value) {
                $oldValue = $setting->value;
                $setting->value = is_array($value) ? json_encode($value) : $value;
                $setting->save();
                $changedSettings[$key] = ['old' => $oldValue, 'new' => $value];
            }
        }

        if (!empty($changedSettings)) {
            AuditLogger::log('updated', 'Setting', null, $changedSettings, null, 'Settings updated');
        }

        return redirect()->route('settings.index')->with('status', 'Settings updated successfully.');
    }
}
