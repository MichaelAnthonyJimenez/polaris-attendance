<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use App\Services\Email\TransactionalEmailService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AnnouncementsController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isAdmin = ($user?->role ?? 'driver') === 'admin';

        $now = now();

        $announcementsQuery = Announcement::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            })
            ->latest('published_at');

        if (! $isAdmin) {
            $announcementsQuery->where(function ($q) use ($user) {
                $q->where('send_to_all', true)
                    ->orWhereHas('recipients', function ($rq) use ($user) {
                        $rq->where('users.id', $user->id);
                    });
            });
        }

        $announcements = $announcementsQuery->paginate(10)->withQueryString();

        return view('announcements.index', [
            'announcements' => $announcements,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function show(Request $request, Announcement $announcement): View
    {
        $user = Auth::user();
        $isAdmin = ($user?->role ?? 'driver') === 'admin';

        // Only allow drivers to view announcements that target them.
        if (! $isAdmin) {
            if (! $announcement->isCurrentlyPublished()) {
                abort(404);
            }

            if (! $announcement->send_to_all
                && ! $announcement->recipients()->where('users.id', $user->id)->exists()
            ) {
                abort(403);
            }
        }

        return view('announcements.show', [
            'announcement' => $announcement,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function create(): View
    {
        $user = Auth::user();
        if (! $user || ($user->role ?? 'driver') !== 'admin') {
            abort(403);
        }

        $drivers = User::query()
            ->where('role', 'driver')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('announcements.create', [
            'drivers' => $drivers,
        ]);
    }

    public function store(Request $request, TransactionalEmailService $transactionalEmail): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || ($user->role ?? 'driver') !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'delivery_mode' => ['required', 'in:app,email,both'],
            'send_to_all' => ['required', 'boolean'],
            'selected_user_ids' => ['nullable', 'array'],
            'selected_user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $sendToAll = (bool) $validated['send_to_all'];
        $deliveryMode = (string) $validated['delivery_mode'];

        $wantApp = $deliveryMode === 'app' || $deliveryMode === 'both';
        $wantEmail = $deliveryMode === 'email' || $deliveryMode === 'both';

        $driversQuery = User::query()
            ->where('role', 'driver')
            ->where('active', true);

        $selectedDriverIds = array_values(array_filter((array) ($validated['selected_user_ids'] ?? [])));

        if (! $sendToAll) {
            if (count($selectedDriverIds) === 0) {
                return back()->withErrors(['selected_user_ids' => 'Select at least one driver or enable "Send to all".'])->withInput();
            }

            $driversQuery->whereIn('id', $selectedDriverIds);
        }

        $driverRecipients = $driversQuery->get();

        $adminRecipients = User::query()
            ->where('role', 'admin')
            ->where('active', true)
            ->get();

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'send_to_all' => $sendToAll,
            'created_by' => $user->id,
            'published_at' => now(),
            'expires_at' => empty($validated['expires_at']) ? null : Carbon::parse((string) $validated['expires_at']),
        ]);

        // Persist targeted recipients so the driver can see the announcement later.
        if (! $sendToAll) {
            $announcement->recipients()->sync($driverRecipients->pluck('id')->all());
        }

        $driverInAppEnabled = (bool) (Setting::get('driver_announcement_in_app', true) ?? true);
        $driverEmailEnabled = (bool) (Setting::get('driver_announcement_email', true) ?? true);

        // In-app notifications: only when admin selected "App" or "Both".
        if ($wantApp) {
            // Admins get announcements in-app; drivers only when they enabled announcements in Settings.
            $inAppDrivers = $driverInAppEnabled ? $driverRecipients : collect();
            $inAppNotifiables = $adminRecipients
                ->merge($inAppDrivers)
                ->unique('id')
                ->values();

            if ($inAppNotifiables->isNotEmpty()) {
                Notification::send($inAppNotifiables, new AnnouncementNotification($announcement));
            }
        }

        // Email notifications: only when admin selected "Email" or "Both".
        if ($wantEmail && $driverEmailEnabled && $driverRecipients->isNotEmpty()) {
            foreach ($driverRecipients as $driverRecipient) {
                /** @var User $driverRecipient */
                if (! $driverRecipient->email) {
                    continue;
                }

                try {
                    $mailable = new AnnouncementMail($driverRecipient, $announcement);
                    $transactionalEmail->sendTo(
                        $driverRecipient->email,
                        $mailable->envelope()->subject,
                        $mailable->render(),
                        null,
                        $driverRecipient->name
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        AuditLogger::log(
            'created',
            'Announcement',
            $announcement->id,
            null,
            ['title' => $announcement->title, 'send_to_all' => $sendToAll ? '1' : '0'],
            "Announcement created: {$announcement->title}"
        );

        return redirect()
            ->route('announcements.index')
            ->with('status', 'Announcement published.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || ($user->role ?? 'driver') !== 'admin') {
            abort(403);
        }

        $announcement->delete();

        AuditLogger::log(
            'deleted',
            'Announcement',
            $announcement->id,
            null,
            ['title' => $announcement->title],
            "Announcement deleted: {$announcement->title}"
        );

        return redirect()
            ->route('announcements.index')
            ->with('status', 'Announcement deleted.');
    }
}

