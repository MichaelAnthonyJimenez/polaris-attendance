<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AnnouncementsController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverVerificationController;
use App\Http\Controllers\DriverVerificationSubmissionController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::view('/terms-of-service', 'terms-of-service')->name('terms-of-service');
Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::view('/contact', 'contact')->name('contact');
Route::post('/contact', [ContactMessageController::class, 'store'])->name('contact.submit');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/register/complete', [AuthController::class, 'showRegisterComplete'])->name('register.complete');

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');

    Route::get('/two-factor-challenge', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::post('/two-factor-challenge/resend', [TwoFactorController::class, 'resend'])->name('two-factor.resend');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    // Driver verification flow (must remain accessible even if not verified yet).
    Route::view('/verification/required', 'verify-popup')->name('verification.required');
    Route::view('/verification/facial', 'facial-verification')->name('verification.facial');
    Route::view('/id-verification', 'id-verification')->name('verification.id');
    Route::post('/driver-verification', [DriverVerificationSubmissionController::class, 'store'])->name('driver-verification.store');
});

Route::middleware(['auth', 'driver.verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/announcements', [AnnouncementsController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/make', [AnnouncementsController::class, 'create'])
        ->middleware('role:admin')
        ->name('announcements.create');
    Route::post('/announcements/make', [AnnouncementsController::class, 'store'])
        ->middleware('role:admin')
        ->name('announcements.store');
    Route::get('/announcements/{announcement}', [AnnouncementsController::class, 'show'])
        ->name('announcements.show');
    Route::delete('/announcements/{announcement}', [AnnouncementsController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('announcements.destroy');

    Route::get('/camera', [CameraController::class, 'index'])->name('camera.index');

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])
        ->name('attendance.history');
    Route::get('/attendance/{attendance}', [AttendanceController::class, 'show'])
        ->middleware('role:admin')
        ->name('attendance.show');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Settings accessible to all authenticated users
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/location-sharing', [SettingsController::class, 'updateDriverLocationSharing'])->name('settings.location-sharing');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

    Route::post('/locations/live-update', [LocationController::class, 'liveUpdate'])->name('locations.live-update');
    Route::post('/locations/enable-sharing', [LocationController::class, 'enableSharing'])->name('locations.enable-sharing');

    Route::middleware('role:admin')->group(function () {
        Route::get('/search', [GlobalSearchController::class, 'redirect'])->name('global-search');
        Route::get('/search/suggest', [GlobalSearchController::class, 'suggest'])->name('global-search.suggest');

        Route::resource('users', UserController::class);
        Route::prefix('inbox')->name('inbox.')->group(function () {
            Route::get('/', [InboxController::class, 'index'])->name('index');
            Route::get('/{message}', [InboxController::class, 'show'])->name('show');
            Route::delete('/{message}', [InboxController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('driver-verification')->name('driver-verification.')->group(function () {
            Route::get('/', [DriverVerificationController::class, 'index'])->name('index');
            Route::post('/bulk-approve', [DriverVerificationController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/bulk-reject', [DriverVerificationController::class, 'bulkReject'])->name('bulk-reject');
            Route::post('/bulk-delete', [DriverVerificationController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/{verification}', [DriverVerificationController::class, 'show'])->name('show');
            Route::post('/{verification}/approve', [DriverVerificationController::class, 'approve'])->name('approve');
            Route::post('/{verification}/reject', [DriverVerificationController::class, 'reject'])->name('reject');
        });
        Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
        Route::get('/locations/live-feed', [LocationController::class, 'liveFeed'])->name('locations.live-feed');
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportsController::class, 'export'])->name('reports.export');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });
});
