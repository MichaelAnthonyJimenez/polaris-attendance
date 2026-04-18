<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\DriverFace;
use App\Models\User;
use App\Services\FaceRecognitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function __construct(private FaceRecognitionService $faceService)
    {
    }

    public function index(Request $request): View
    {
        $search = trim($request->get('search', ''));
        $sort = $request->get('sort');

        $query = User::query()->where('role', 'driver');

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('badge_number', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('vehicle_number', 'like', $like);
            });
        }

        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'badge_asc':
                $query->orderBy('badge_number', 'asc');
                break;
            case 'badge_desc':
                $query->orderBy('badge_number', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return view('drivers.index', [
            'drivers' => $query->paginate(15)->withQueryString(),
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function create(): View
    {
        return view('drivers.create');
    }

    public function edit(User $driver): View
    {
        abort_unless($driver->role === 'driver', 404);

        return view('drivers.edit', ['driver' => $driver]);
    }

    public function store(Request $request): RedirectResponse
    {
        $badgeRules = ['nullable', 'string', 'max:50'];
        if (trim((string) $request->input('badge_number', '')) !== '') {
            $badgeRules[] = 'unique:users,badge_number';
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'badge_number' => $badgeRules,
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
            'active' => ['sometimes', 'boolean'],
            'face_image' => ['nullable', 'image', 'max:5120'],
        ]);

        $badgeNumber = trim((string) ($data['badge_number'] ?? '')) !== ''
            ? $data['badge_number']
            : User::nextDriverBadgeNumber();

        $driver = User::create([
            'name' => $data['name'],
            'badge_number' => $badgeNumber,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'profile_photo_path' => $request->hasFile('profile_photo')
                ? $request->file('profile_photo')->store('driver-profiles', 'public')
                : null,
            'active' => $request->boolean('active', true),
            'role' => 'driver',
            'password' => Hash::make(Str::random(16)),
        ]);

        if ($request->hasFile('face_image')) {
            $storedPath = $request->file('face_image')->store('faces', 'public');
            $template = $this->faceService->enrollFaceForDriver($driver->id, Storage::disk('public')->path($storedPath));

            DriverFace::create([
                'driver_id' => $driver->id,
                'image_path' => $storedPath,
                'face_template' => $template,
                'created_by' => $request->user()?->id,
            ]);
        }

        AuditLogger::log('created', 'Driver', $driver->id, null, $driver->toArray(), "Driver {$driver->name} created");

        return redirect()->route('drivers.index')->with('status', 'Driver created');
    }

    public function update(Request $request, User $driver): RedirectResponse
    {
        abort_unless($driver->role === 'driver', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'badge_number' => ['required', 'string', 'max:50', 'unique:users,badge_number,'.$driver->id],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$driver->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
            'active' => ['sometimes', 'boolean'],
            'face_image' => ['nullable', 'image', 'max:5120'],
        ]);

        $old = $driver->toArray();

        $profilePhotoPath = $driver->profile_photo_path;
        if ($request->hasFile('profile_photo')) {
            if ($profilePhotoPath) {
                Storage::disk('public')->delete($profilePhotoPath);
            }
            $profilePhotoPath = $request->file('profile_photo')->store('driver-profiles', 'public');
        }

        $driver->update([
            'name' => $data['name'],
            'badge_number' => $data['badge_number'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'profile_photo_path' => $profilePhotoPath,
            'active' => $request->boolean('active', true),
            'role' => 'driver',
        ]);

        if ($request->hasFile('face_image')) {
            $storedPath = $request->file('face_image')->store('faces', 'public');
            $template = $this->faceService->enrollFaceForDriver($driver->id, Storage::disk('public')->path($storedPath));

            DriverFace::create([
                'driver_id' => $driver->id,
                'image_path' => $storedPath,
                'face_template' => $template,
                'created_by' => $request->user()?->id,
            ]);
        }

        AuditLogger::log('updated', 'Driver', $driver->id, $old, $driver->toArray(), "Driver {$driver->name} updated");

        return redirect()->route('drivers.index')->with('status', 'Driver updated');
    }

    public function destroy(User $driver): RedirectResponse
    {
        abort_unless($driver->role === 'driver', 404);

        $old = $driver->toArray();
        $driver->delete();

        AuditLogger::log('deleted', 'Driver', $driver->id, $old, null, "Driver {$old['name']} deleted");

        return redirect()->route('drivers.index')->with('status', 'Driver deleted');
    }
}

