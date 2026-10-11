<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Prediction;
use App\Models\SensorReading;
use App\Services\MlPredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function __construct(private readonly MlPredictionService $mlService) {}

    /**
     * Ringkasan real-time untuk polling dashboard (auth, milik sendiri).
     * Bentuk respons stabil; hanya menambah field bila perlu.
     *
     * GET /dashboard/api/overview?device_id=all|ECASSAVA-XXX
     */
    public function overview(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $windowTarget = (int) config('ecassava.ml_window_size', 30);

        $devices = Device::ownedBy($userId)
            ->with(['latestPrediction', 'latestReading'])
            ->withCount('sensorReadings')
            ->orderBy('created_at')
            ->get();

        $filter = (string) $request->query('device_id', 'all');

        if ($filter !== 'all') {
            $devices = $devices->where('device_id', $filter)->values();
        }

        $payload = $devices->map(function (Device $device) use ($windowTarget): array {
            $reading = $device->latestReading;
            $pred = $device->latestPrediction;

            return [
                'device_id' => $device->device_id,
                'name' => $device->name,
                'location' => $device->location,
                'is_online' => $device->is_online,
                'last_seen' => $device->last_seen_at?->toIso8601String(),
                'latest' => $reading ? [
                    'mq2' => $reading->mq2,
                    'mq3' => $reading->mq3,
                    'mq135' => $reading->mq135,
                    'temperature' => $reading->temperature !== null ? (float) $reading->temperature : null,
                    'recorded_at' => $reading->recorded_at?->toIso8601String(),
                ] : null,
                'prediction' => $pred ? [
                    'label' => $pred->predicted_label->value,
                    'confidence' => $pred->confidence,
                    'confidence_percent' => round(($pred->confidence ?? 0) * 100, 1),
                    'uncertain' => $pred->is_uncertain,
                    'probabilities' => $pred->probabilities,
                    'predicted_at' => $pred->predicted_at?->toIso8601String(),
                ] : null,
                'window' => [
                    'current' => $device->sensor_readings_count,
                    'target' => $windowTarget,
                    'ready' => $device->sensor_readings_count >= $windowTarget,
                ],
            ];
        });

        $online = $devices->where('is_online', true)->count();

        $latestPrediction = Prediction::whereHas('device', fn ($q) => $q->where('user_id', $userId))
            ->with('device')
            ->latest('predicted_at')
            ->first();

        // Samakan fokus dengan DashboardController: terpilih → prediksi terbaru → pertama.
        $focusDeviceId = $filter !== 'all'
            ? $devices->first()?->device_id
            : ($latestPrediction?->device?->device_id ?? $devices->first()?->device_id);

        $timeline = Prediction::whereHas('device', fn ($q) => $q->where('user_id', $userId))
            ->when($filter !== 'all', fn ($q) => $q->whereHas('device', fn ($d) => $d->where('device_id', $filter)))
            ->orderBy('predicted_at')
            ->limit(60)
            ->get(['predicted_label', 'predicted_at'])
            ->map(fn (Prediction $p): array => [
                'label' => $p->predicted_at?->format('H:i'),
                'value' => $p->predicted_label->value === 'fresh' ? 0 : ($p->predicted_label->value === 'stale' ? 1 : 2),
                'name' => $p->predicted_label->value,
            ]);

        return response()->json([
            'success' => true,
            'ml_service_online' => $this->mlService->isHealthy(),
            'summary' => [
                'online' => $online,
                'offline' => $devices->count() - $online,
                'total' => $devices->count(),
            ],
            'devices' => $payload,
            'focus_device_id' => $focusDeviceId,
            'timeline' => $timeline,
            'latest_prediction' => $latestPrediction?->only(['predicted_label', 'confidence', 'predicted_at']),
        ]);
    }

    /**
     * Data sensor terbaru untuk grafik live (auth, milik sendiri).
     *
     * GET /dashboard/api/sensor?device_id=...&range=1h|24h|7d
     */
    public function sensor(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $device = Device::ownedBy($userId)
            ->where('device_id', $request->query('device_id'))
            ->firstOrFail();

        $since = match ($request->query('range', '1h')) {
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            default => now()->subHour(),
        };

        $readings = SensorReading::where('device_id', $device->id)
            ->where('recorded_at', '>=', $since)
            ->orderBy('recorded_at')
            ->limit(300)
            ->get(['mq2', 'mq3', 'mq135', 'temperature', 'recorded_at']);

        $range = $request->query('range', '1h');
        $labelFormat = $range === '7d' ? 'd M H:i' : 'H:i';

        return response()->json([
            'success' => true,
            'device_id' => $device->device_id,
            'data' => $readings->map(fn (SensorReading $r): array => [
                'label' => $r->recorded_at?->translatedFormat($labelFormat),
                'mq2' => $r->mq2,
                'mq3' => $r->mq3,
                'mq135' => $r->mq135,
                'temperature' => $r->temperature !== null ? (float) $r->temperature : null,
            ]),
        ]);
    }
}
