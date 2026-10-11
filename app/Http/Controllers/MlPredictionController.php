<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\MeasurementSession;
use App\Models\Prediction;
use App\Models\SensorReading;
use App\Services\MlPredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MlPredictionController extends Controller
{
    public function __construct(private readonly MlPredictionService $mlService) {}

    /**
     * Halaman prediksi real-time (hasil otomatis dari database + analisis manual).
     */
    public function index(): View
    {
        $userId = auth()->id();

        $devices = Device::ownedBy($userId)->orderBy('name')->get();
        $isServiceOnline = $this->mlService->isHealthy();
        $windowSize = (int) config('ecassava.ml_window_size', 30);

        $latestPrediction = Prediction::whereHas('device', fn ($q) => $q->where('user_id', $userId))
            ->with(['device', 'measurementSession'])
            ->latest('predicted_at')
            ->first();
        $predictionHistory = Prediction::whereHas('device', fn ($q) => $q->where('user_id', $userId))
            ->with(['device', 'measurementSession'])
            ->latest('predicted_at')
            ->paginate(15);

        return view('ml.predict', compact(
            'devices',
            'isServiceOnline',
            'windowSize',
            'latestPrediction',
            'predictionHistory',
        ));
    }

    /**
     * Prediksi kondisi singkong dari data sensor terbaru device.
     *
     * POST /ml/predict
     */
    public function predict(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string'],
        ]);

        $device = Device::ownedBy(auth()->id())
            ->where('device_id', $validated['device_id'])
            ->first();

        if (! $device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan.',
            ], 404);
        }

        // Ambil N pembacaan terakhir sebagai window untuk feature extraction
        // (N configurable via ML_WINDOW_SIZE, default 30)
        $windowSize = (int) config('ecassava.ml_window_size', 30);
        $readings = SensorReading::where('device_id', $device->id)
            ->latest('recorded_at')
            ->limit($windowSize)
            ->get(['mq2', 'mq3', 'mq135']);

        if ($readings->count() < 5) {
            return response()->json([
                'success' => false,
                'message' => 'Data sensor belum cukup. Minimal 5 pembacaan diperlukan untuk prediksi.',
            ], 422);
        }

        // Hitung fitur dari window pembacaan
        $mq2s = $readings->pluck('mq2')->map(fn ($v) => (float) $v)->toArray();
        $mq3s = $readings->pluck('mq3')->map(fn ($v) => (float) $v)->toArray();
        $mq135s = $readings->pluck('mq135')->map(fn ($v) => (float) $v)->toArray();

        $features = [
            'mq2_mean' => round(array_sum($mq2s) / count($mq2s), 4),
            'mq3_mean' => round(array_sum($mq3s) / count($mq3s), 4),
            'mq135_mean' => round(array_sum($mq135s) / count($mq135s), 4),
            'mq2_std' => round($this->stdDev($mq2s), 4),
            'mq3_std' => round($this->stdDev($mq3s), 4),
            'mq135_std' => round($this->stdDev($mq135s), 4),
            'mq2_min' => (int) min($mq2s),
            'mq3_min' => (int) min($mq3s),
            'mq135_min' => (int) min($mq135s),
            'mq2_max' => (int) max($mq2s),
            'mq3_max' => (int) max($mq3s),
            'mq135_max' => (int) max($mq135s),
        ];

        $result = $this->mlService->predict($features);

        return response()->json(array_merge($result, [
            'features' => $features,
            'reading_count' => count($mq2s),
        ]));
    }

    /**
     * Prediksi berdasarkan data sesi yang sudah lengkap.
     *
     * POST /ml/predict-session/{session}
     */
    public function predictSession(MeasurementSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $features = $session->computeFeatures();

        if ($features['reading_count'] < 5) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi ini belum memiliki cukup data.',
            ], 422);
        }

        $result = $this->mlService->predict($features);

        return response()->json(array_merge($result, [
            'features' => $features,
            'session_id' => $session->id,
            'session_name' => $session->name,
            'actual_label' => $session->cassava_condition?->value,
            'actual_label_id' => $session->cassava_condition?->label(),
        ]));
    }

    /** Cek status Python ML service. */
    public function healthCheck(): JsonResponse
    {
        $isOnline = $this->mlService->isHealthy();

        return response()->json([
            'ml_service_online' => $isOnline,
            'ml_api_url' => config('ecassava.ml_api_url'),
            'message' => $isOnline
                ? 'Python ML service aktif dan siap'
                : 'Python ML service tidak dapat dijangkau',
        ]);
    }

    /** @param float[] $values */
    private function stdDev(array $values): float
    {
        $count = count($values);
        if ($count < 2) {
            return 0.0;
        }

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn ($v) => ($v - $mean) ** 2, $values)) / ($count - 1);

        return sqrt($variance);
    }
}
