<?php

namespace App\Http\Controllers;

use App\Http\Resources\PredictionResource;
use App\Models\Device;
use App\Models\Prediction;
use App\Models\SensorReading;
use App\Services\MlPredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PredictionController extends Controller
{
    public function __construct(private readonly MlPredictionService $mlService) {}

    /**
     * Mengambil hasil prediksi SVM terbaru (opsional berdasarkan device_id).
     *
     * GET /api/prediction/latest
     */
    public function latest(Request $request): JsonResponse
    {
        $windowTarget = (int) config('ecassava.ml_window_size', 30);
        $device = null;
        $deviceCount = 0;

        $query = Prediction::with(['device', 'measurementSession'])
            ->latest('predicted_at');

        if ($request->filled('device_id')) {
            $device = Device::where('device_id', $request->device_id)->first();

            if (! $device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device tidak ditemukan',
                ], 404);
            }

            if (! $device->verifyApiKey($request->header('X-API-KEY') ?? $request->query('api_key'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'API key salah atau hilang.',
                ], 401);
            }

            $query->where('device_id', $device->id);
            $deviceCount = SensorReading::where('device_id', $device->id)->count();
        } else {
            // Tanpa device_id: hanya perangkat lawas terbuka agar data ber-key tidak bocor.
            $query->whereHas('device', fn ($q) => $q->whereNull('api_key'));
            $device = Device::whereNull('api_key')->latest('last_seen_at')->first();
            if ($device) {
                $deviceCount = SensorReading::where('device_id', $device->id)->count();
            }
        }

        $latestPrediction = $query->first();
        $isServiceOnline = $this->mlService->isHealthy();

        $isReady = $deviceCount >= $windowTarget;
        $statusText = $isReady
            ? 'Window data siap ('.$deviceCount.'/'.$windowTarget.')'
            : 'Mengumpulkan data ('.$deviceCount.'/'.$windowTarget.')';

        return response()->json([
            'success' => true,
            'data' => $latestPrediction ? new PredictionResource($latestPrediction) : null,
            'window_status' => [
                'current' => $deviceCount,
                'target' => $windowTarget,
                'ready' => $isReady,
                'status_text' => $statusText,
            ],
            'ml_service_online' => $isServiceOnline,
            'device_id' => $device?->device_id,
        ]);
    }

    /**
     * Mengambil riwayat prediksi SVM terpaginasi.
     *
     * GET /api/prediction/history
     */
    public function history(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 20), 100);

        $query = Prediction::with(['device', 'measurementSession'])
            ->latest('predicted_at');

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

        $predictions = $query->paginate($perPage);

        return PredictionResource::collection($predictions);
    }
}
