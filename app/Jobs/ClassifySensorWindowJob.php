<?php

namespace App\Jobs;

use App\Models\Device;
use App\Models\Prediction;
use App\Models\SensorReading;
use App\Services\AlertService;
use App\Services\MlPredictionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ClassifySensorWindowJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $deviceId,
        public ?int $measurementSessionId = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MlPredictionService $mlService, AlertService $alerts): void
    {
        $device = Device::find($this->deviceId);

        if (! $device) {
            Log::warning('[ML-SVM] Job klasifikasi dilewati: Device tidak ditemukan', ['id' => $this->deviceId]);

            return;
        }

        $windowSize = (int) config('ecassava.ml_window_size', 30);

        // Ambil N pembacaan terakhir perangkat tersebut
        $readings = SensorReading::where('device_id', $this->deviceId)
            ->latest('recorded_at')
            ->limit($windowSize)
            ->get(['mq2', 'mq3', 'mq135']);

        // Jika pembacaan belum mencapai N, lewati prediksi (status: collecting data)
        if ($readings->count() < $windowSize) {
            Log::info('[ML-SVM] Mengumpulkan data sensor (belum mencapai window size)', [
                'device_id' => $device->device_id,
                'current' => $readings->count(),
                'target' => $windowSize,
                'status' => 'collecting data',
            ]);

            return;
        }

        // Format array pembacaan untuk dikirim ke Python ML Service
        $readingsArray = $readings->reverse()->values()->map(fn (SensorReading $r) => [
            'mq2' => (int) $r->mq2,
            'mq3' => (int) $r->mq3,
            'mq135' => (int) $r->mq135,
        ])->all();

        // Panggil ML Service
        $result = $mlService->predictWindow($readingsArray);

        if (! $result['success'] || empty($result['label'])) {
            Log::warning('[ML-SVM] Prediksi tidak berhasil disimpan ke database', [
                'device_id' => $device->device_id,
                'message' => $result['message'],
            ]);

            return;
        }

        // Sensor fault (jenuh/mati): jangan simpan label palsu.
        // Umumnya AO 5V langsung ke ADC 3.3V — pasang pembagi tegangan.
        if (! empty($result['warnings'])) {
            Log::warning('[ML-SVM] Prediksi dilewati: sensor fault terdeteksi', [
                'device_id' => $device->device_id,
                'warnings' => $result['warnings'],
            ]);

            return;
        }

        // Simpan hasil klasifikasi SVM ke tabel predictions
        $previousLabel = Prediction::where('device_id', $device->id)
            ->latest('predicted_at')
            ->value('predicted_label');

        $prediction = Prediction::create([
            'device_id' => $device->id,
            'measurement_session_id' => $this->measurementSessionId,
            'predicted_label' => $result['label'],
            'confidence' => $result['confidence'] ?? 0.0,
            'probabilities' => $result['probabilities'] ?? [],
            'model_name' => $result['model_name'] ?? config('ecassava.ml_model_name', 'SVM'),
            'window_size' => $windowSize,
            'predicted_at' => now(),
        ]);

        Log::info('[ML-SVM] Hasil klasifikasi tersimpan ke tabel predictions', [
            'prediction_id' => $prediction->id,
            'device_id' => $device->device_id,
            'label' => $prediction->predicted_label->value,
            'confidence' => $prediction->confidence,
            'uncertain' => $result['uncertain'] ?? $prediction->is_uncertain,
        ]);

        // Alert otomatis saat transisi ke stale/rotten (tidak mengubah kontrak API).
        // value() mengembalikan string mentah dari DB (fresh/stale/rotten) atau null.
        $alerts->handlePrediction($device, $prediction, $previousLabel);
    }
}
