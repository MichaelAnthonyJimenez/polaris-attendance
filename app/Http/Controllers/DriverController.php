<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\Driver;
use App\Models\DriverFace;
use App\Models\User;
use App\Services\FaceRecognitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function __construct(private FaceRecognitionService $faceService)
    {
    }

    public function index(): View
    {
        return view('drivers.index', [
            'drivers' => Driver::latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('drivers.create');
    }

    public function edit(Driver $driver): View
    {
        return view('drivers.edit', ['driver' => $driver]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'badge_number' => ['required', 'string', 'max:50', 'unique:drivers,badge_number'],
            'email' => ['nullable', 'email', 'max:255', 'unique:drivers,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'active' => ['sometimes', 'boolean'],
            'face_image' => ['nullable', 'image', 'max:5120'],
        ]);

        $driver = Driver::create([
            'name' => $data['name'],
            'badge_number' => $data['badge_number'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'active' => $request->boolean('active', true),
            'user_id' => !empty($data['email']) ? User::where('email', $data['email'])->value('id') : null,
        ]);

        if ($request->hasFile('face_image')) {
            $storedPath = $request->file('face_image')->store('faces', 'public');
            $template = $this->faceService->generateTemplate(Storage::disk('public')->path($storedPath));

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

    public function update(Request $request, Driver $driver): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'badge_number' => ['required', 'string', 'max:50', 'unique:drivers,badge_number,'.$driver->id],
            'email' => ['nullable', 'email', 'max:255', 'unique:drivers,email,'.$driver->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'active' => ['sometimes', 'boolean'],
            'face_image' => ['nullable', 'image', 'max:5120'],
        ]);

        $old = $driver->toArray();

        $driver->update([
            'name' => $data['name'],
            'badge_number' => $data['badge_number'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'active' => $request->boolean('active', true),
            'user_id' => !empty($data['email']) ? User::where('email', $data['email'])->value('id') : null,
        ]);

        if ($request->hasFile('face_image')) {
            $storedPath = $request->file('face_image')->store('faces', 'public');
            $template = $this->faceService->generateTemplate(Storage::disk('public')->path($storedPath));

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

    public function destroy(Driver $driver): RedirectResponse
    {
        $old = $driver->toArray();
        $driver->delete();

        AuditLogger::log('deleted', 'Driver', $driver->id, $old, null, "Driver {$old['name']} deleted");

        return redirect()->route('drivers.index')->with('status', 'Driver deleted');
    }
}

