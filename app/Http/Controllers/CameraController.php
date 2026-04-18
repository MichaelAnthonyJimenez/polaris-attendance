<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CameraController extends Controller
{
    public function index(): View
    {
        return view('camera.index', [
            'cameraAutoCapture' => (bool) Setting::get('auto_capture_photo', false),
            'cameraAutoSubmit' => (bool) Setting::get('auto_submit_camera', false),
            'driverLocationSharingEnabled' => (bool) (Auth::user()?->location_sharing_enabled ?? false),
            'postCaptureNotify' => [
                'showNotifications' => (bool) Setting::get('show_notifications', true),
                'browserCheckin' => (bool) Setting::get('driver_browser_notify_checkin', true),
                'browserCheckout' => (bool) Setting::get('driver_browser_notify_checkout', true),
                'sound' => (bool) Setting::get('driver_notification_sound', true)
                    && (bool) Setting::get('enable_notifications', true),
            ],
        ]);
    }
}
