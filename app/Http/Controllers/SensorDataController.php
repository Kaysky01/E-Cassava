<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSensorDataRequest;
use App\Http\Resources\SensorReadingResource;
use App\Jobs\ClassifySensorWindowJob;
use App\Models\Device;
use App\Models\MeasurementSession;
use App\Models\SensorReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SensorDataController extends Controller
{
    /**
     * Receive sensor data from ESP32 and store it.
     *
     * POST /api/sensor-data
     *
     * Kontrak respons tidak berubah (201 + success/message/data/session_id).
     * Perangkat yang sudah punya API key wajib mengirim key yang cocok
     * via header X-API-KEY atau field api_key — selain itu 401.
     */
    public function store(StoreSensorDataRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $device = Device::where('device_id', $validated['device_id'])->first();

        if (! $device) {
            // Perangkat wajib didaftarkan dulu via menu Alat.
            // Auto-create tanpa pemilik dihapus: device orphan tidak muncul
            // di dashboard milik siapapun (filter ownedBy user_id).
            return response()->json([
                'success' => false,
                'message' => 'Device tidak terdaftar. Daftarkan dulu via menu Alat.',
            ], 404);
        }

        $providedKey = $request->header('X-API-KEY') ?? $validated['api_key'] ?? null;

        if (! $device->verifyApiKey($providedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API key salah atau hilang.',
            ], 401);
        }

        if (! $device->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat dinonaktifkan.',
            ], 403);
        }

        // Update last_seen_at and status
        $device->last_seen_at = now();
        $device->status = 'online';
        $device->save();

        // Cek apakah ada sesi pengujian aktif untuk device ini
        $activeSession = MeasurementSession::whereNull('ended_at')
            ->where('device_id', $device->id)
            ->latest('started_at')
            ->first();

        // Create sensor reading, linked to active session if any
        $reading = SensorReading::create([
            'device_id' => $device->id,
            'measurement_session_id' => $activeSession?->id,
            'mq2' => $validated['mq2'],
            'mq3' => $validated['mq3'],
            'mq135' => $validated['mq135'],
            'mq2_voltage' => $validated['mq2_voltage'] ?? null,
            'mq3_voltage' => $validated['mq3_voltage'] ?? null,
            'mq135_voltage' => $validated['mq135_voltage'] ?? null,
            'temperature' => $validated['temperature'] ?? null,
            'recorded_at' => now(),
        ]);

        $reading->load('device');

        // Dispatch background classification without blocking ESP32 response
        ClassifySensorWindowJob::dispatchAfterResponse($device->id, $activeSession?->id);

        return response()->json([
            'success' => true,
            'message' => 'Sensor data saved successfully',
            'data' => new SensorReadingResource($reading),
            'session_id' => $activeSession?->id,
        ], 201);
    }

    /**
     * Get the latest sensor reading for each device (or specific device).
     *
     * GET /api/sensor/latest
     */
    public function latest(Request $request): JsonResponse
    {
        $query = SensorReading::with('device')
            ->latest('recorded_at');

        if ($request->filled('device_id')) {
            $device = Device::where('device_id', $request->device_id)->first();

            if (! $device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found',
                ], 404);
            }

            if (! $device->verifyApiKey($request->header('X-API-KEY') ?? $request->query('api_key'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'API key salah atau hilang.',
                ], 401);
            }

            $query->where('device_id', $device->id);
        } else {
            // Tanpa device_id: hanya perangkat lawas terbuka (tanpa key & tanpa pemilik)
            // agar data milik user ber-key tidak bocor lewat endpoint publik.
            $query->whereHas('device', fn ($q) => $q->whereNull('api_key'));
        }

        $reading = $query->first();

        if (! $reading) {
            return response()->json([
                'success' => false,
                'message' => 'No sensor data available',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new SensorReadingResource($reading),
        ]);
    }

    /**
     * Get paginated history of sensor readings.
     *
     * GET /api/sensor/history
     */
    public function history(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 20), 100);

        $query = SensorReading::with('device')
            ->latest('recorded_at');

        if ($request->filled('device_id')) {
            $device = Device::where('device_id', $request->device_id)->first();

            if ($device) {
                if (! $device->verifyApiKey($request->header('X-API-KEY') ?? $request->query('api_key'))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'API key salah atau hilang.',
                    ], 401);
                }

                $query->where('device_id', $device->id);
            }
        } else {
            $query->whereHas('device', fn ($q) => $q->whereNull('api_key'));
        }

        $readings = $query->paginate($perPage);

        return SensorReadingResource::collection($readings);
    }
}
