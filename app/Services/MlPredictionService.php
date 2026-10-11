<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MlPredictionService
{
    /**
     * Mengirim window pembacaan sensor (array N data) ke Python FastAPI ML service.
     * Fitur akan diekstrak dan diklasifikasi oleh model SVM di sisi Python.
     *
     * @param  array<int, array{mq2: int|float, mq3: int|float, mq135: int|float}>  $readings
     * @return array{success: bool, label: string|null, condition: string|null, confidence: float|null, probabilities: array<string, float>|null, model_name: string, model_version: string|null, features: array<string, mixed>|null, uncertain: bool, margin: float|null, warnings: array<int, string>, message: string}
     */
    public function predictWindow(array $readings): array
    {
        $apiUrl = rtrim((string) config('ecassava.ml_api_url', 'http://localhost:8001'), '/').'/predict';
        $timeout = (int) config('ecassava.ml_timeout_seconds', 5);
        $defaultModelName = (string) config('ecassava.ml_model_name', 'SVM');

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->post($apiUrl, [
                    'window' => $readings,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $label = $data['label'] ?? $data['condition'] ?? null;
                $confidence = isset($data['confidence']) ? (float) $data['confidence'] : null;

                Log::info('[ML-SVM] Prediksi window berhasil', [
                    'label' => $label,
                    'confidence' => $confidence,
                    'readings_count' => count($readings),
                    'model' => $data['model_name'] ?? $defaultModelName,
                    'uncertain' => $data['uncertain'] ?? null,
                    'warnings' => $data['warnings'] ?? [],
                ]);

                return [
                    'success' => true,
                    'label' => $label,
                    'condition' => $label,
                    'confidence' => $confidence,
                    'probabilities' => $data['probabilities'] ?? null,
                    'model_name' => $data['model_name'] ?? $defaultModelName,
                    'model_version' => $data['model_version'] ?? null,
                    'features' => $data['features'] ?? null,
                    'uncertain' => (bool) ($data['uncertain'] ?? false),
                    'margin' => isset($data['margin']) ? (float) $data['margin'] : null,
                    'warnings' => $data['warnings'] ?? [],
                    'message' => $data['message'] ?? 'Prediksi SVM berhasil',
                ];
            }

            Log::warning('[ML-SVM] ML Service merespons error status', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'label' => null,
                'condition' => null,
                'confidence' => null,
                'probabilities' => null,
                'model_name' => $defaultModelName,
                'model_version' => null,
                'features' => null,
                'uncertain' => true,
                'margin' => null,
                'warnings' => [],
                'message' => 'ML Service mengembalikan HTTP '.$response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('[ML-SVM] Gagal menghubungi Python ML Service (SVM)', [
                'error' => $e->getMessage(),
                'url' => $apiUrl,
                'timeout' => $timeout,
            ]);

            return [
                'success' => false,
                'label' => null,
                'condition' => null,
                'confidence' => null,
                'probabilities' => null,
                'model_name' => $defaultModelName,
                'model_version' => null,
                'features' => null,
                'uncertain' => true,
                'margin' => null,
                'warnings' => [],
                'message' => 'Python ML Service offline atau tidak terjangkau.',
            ];
        }
    }

    /**
     * Prediksi berdasarkan fitur yang sudah diekstrak (backward compatibility).
     *
     * @param  array{mq2_mean: float, mq3_mean: float, mq135_mean: float, mq2_std: float, mq3_std: float, mq135_std: float, mq2_min: int, mq3_min: int, mq135_min: int, mq2_max: int, mq3_max: int, mq135_max: int}  $features
     * @return array{success: bool, label: string|null, condition: string|null, confidence: float|null, probabilities: array<string, float>|null, model_name: string, model_version: string|null, features: array<string, mixed>|null, uncertain: bool, margin: float|null, warnings: array<int, string>, message: string}
     */
    public function predict(array $features): array
    {
        $apiUrl = rtrim((string) config('ecassava.ml_api_url', 'http://localhost:8001'), '/').'/predict';
        $timeout = (int) config('ecassava.ml_timeout_seconds', 5);
        $defaultModelName = (string) config('ecassava.ml_model_name', 'SVM');

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->post($apiUrl, $features);

            if ($response->successful()) {
                $data = $response->json();
                $label = $data['label'] ?? $data['condition'] ?? null;

                return [
                    'success' => true,
                    'label' => $label,
                    'condition' => $label,
                    'confidence' => isset($data['confidence']) ? (float) $data['confidence'] : null,
                    'probabilities' => $data['probabilities'] ?? null,
                    'model_name' => $data['model_name'] ?? $defaultModelName,
                    'model_version' => $data['model_version'] ?? null,
                    'features' => $data['features'] ?? $features,
                    'uncertain' => (bool) ($data['uncertain'] ?? false),
                    'margin' => isset($data['margin']) ? (float) $data['margin'] : null,
                    'warnings' => $data['warnings'] ?? [],
                    'message' => $data['message'] ?? 'Prediksi berhasil',
                ];
            }

            return [
                'success' => false,
                'label' => null,
                'condition' => null,
                'confidence' => null,
                'probabilities' => null,
                'model_name' => $defaultModelName,
                'model_version' => null,
                'features' => null,
                'uncertain' => true,
                'margin' => null,
                'warnings' => [],
                'message' => 'ML API error: '.$response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('[ML-SVM] Gagal menghubungi Python ML API', [
                'error' => $e->getMessage(),
                'url' => $apiUrl,
            ]);

            return [
                'success' => false,
                'label' => null,
                'condition' => null,
                'confidence' => null,
                'probabilities' => null,
                'model_name' => $defaultModelName,
                'model_version' => null,
                'features' => null,
                'uncertain' => true,
                'margin' => null,
                'warnings' => [],
                'message' => 'ML service tidak tersedia. Pastikan Python API berjalan.',
            ];
        }
    }

    /**
     * Cek apakah Python ML service aktif DAN model termuat.
     *
     * /health selalu HTTP 200 walau model belum ada, jadi wajib
     * memeriksa flag model_loaded — kalau tidak, dashboard bilang
     * "online" padahal semua /predict 503 (kasus kemarin).
     * Hasil di-cache 15 detik agar polling dashboard tiap 4 detik
     * tidak menghantam ML service terus-menerus.
     */
    public function isHealthy(): bool
    {
        return Cache::remember('ml-service-health', 15, function (): bool {
            try {
                $response = Http::timeout(3)
                    ->get(rtrim((string) config('ecassava.ml_api_url', 'http://localhost:8001'), '/').'/health');

                return $response->successful()
                    && $response->json('model_loaded', false) === true;
            } catch (\Throwable) {
                return false;
            }
        });
    }
}
