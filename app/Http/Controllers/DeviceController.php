<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Models\Device;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(): View
    {
        $devices = Device::ownedBy(auth()->id())
            ->with(['latestPrediction', 'latestReading'])
            ->withCount('sensorReadings')
            ->orderBy('created_at')
            ->get();

        $devices->each(function (Device $device): void {
            $device->updateOnlineStatus();
        });

        $plainKey = session('api_plain_key');
        $plainDeviceId = session('api_plain_device_id');

        return view('devices.index', compact('devices', 'plainKey', 'plainDeviceId'));
    }

    public function store(StoreDeviceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $deviceId = $this->generateDeviceId();
        $plainKey = Str::random(48);

        $device = Device::create([
            'user_id' => auth()->id(),
            'device_id' => $deviceId,
            'name' => $validated['name'],
            'location' => $validated['location'] ?? null,
            'status' => 'offline',
            'api_key' => hash('sha256', $plainKey),
            'api_key_hint' => substr($plainKey, -4),
            'is_active' => true,
        ]);

        return redirect()->route('devices.index')
            ->with('success', __('devices.created_ok'))
            ->with('api_plain_key', $plainKey)
            ->with('api_plain_device_id', $device->id);
    }

    public function edit(Device $device): View
    {
        $this->authorize('update', $device);

        return view('devices.edit', compact('device'));
    }

    public function update(UpdateDeviceRequest $request, Device $device): RedirectResponse
    {
        $this->authorize('update', $device);

        $device->update([
            'name' => $request->validated('name'),
            'location' => $request->validated('location'),
            'is_active' => $request->boolean('is_active', $device->is_active),
        ]);

        return redirect()->route('devices.index')
            ->with('success', __('devices.updated_ok'));
    }

    public function destroy(Device $device): RedirectResponse
    {
        $this->authorize('delete', $device);

        $device->delete();

        return redirect()->route('devices.index')
            ->with('success', __('devices.deleted_ok'));
    }

    public function regenerate(Device $device): RedirectResponse
    {
        $this->authorize('update', $device);

        $plainKey = Str::random(48);

        $device->update([
            'api_key' => hash('sha256', $plainKey),
            'api_key_hint' => substr($plainKey, -4),
        ]);

        return redirect()->route('devices.index')
            ->with('success', __('devices.regenerated_ok'))
            ->with('api_plain_key', $plainKey)
            ->with('api_plain_device_id', $device->id);
    }

    public function toggle(Device $device): RedirectResponse
    {
        $this->authorize('update', $device);

        $device->update(['is_active' => ! $device->is_active]);

        return redirect()->route('devices.index')
            ->with('success', __('devices.updated_ok'));
    }

    private function generateDeviceId(): string
    {
        do {
            $number = str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
            $candidate = 'ECASSAVA-'.$number;
        } while (Device::where('device_id', $candidate)->exists());

        return $candidate;
    }
}
