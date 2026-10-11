<?php

namespace Tests\Feature;

use App\Jobs\ClassifySensorWindowJob;
use App\Models\Device;
use App\Models\Prediction;
use App\Models\SensorReading;
use App\Services\AlertService;
use App\Services\MlPredictionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SvmPredictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_classification_job_stores_prediction_when_window_full(): void
    {
        config(['ecassava.ml_window_size' => 30]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'label' => 'fresh',
                'condition' => 'fresh',
                'confidence' => 0.92,
                'probabilities' => ['fresh' => 0.92, 'stale' => 0.05, 'rotten' => 0.03],
                'model_name' => 'SVM',
                'model_version' => 'svm_test',
                'features' => ['mq2_mean' => 1000.0],
                'message' => 'Klasifikasi SVM berhasil',
            ], 200),
        ]);

        $device = Device::factory()->create(['device_id' => 'ECASSAVA-001']);

        SensorReading::factory()->count(30)->create([
            'device_id' => $device->id,
            'measurement_session_id' => null,
        ]);

        $job = new ClassifySensorWindowJob($device->id);
        $job->handle(app(MlPredictionService::class), app(AlertService::class));

        $this->assertDatabaseHas('predictions', [
            'device_id' => $device->id,
            'predicted_label' => 'fresh',
            'model_name' => 'SVM',
        ]);

        $prediction = Prediction::first();
        $this->assertNotNull($prediction);
        $this->assertEqualsWithDelta(0.92, (float) $prediction->confidence, 0.0001);
        $this->assertSame(30, (int) $prediction->window_size);
        $this->assertSame(['fresh' => 0.92, 'stale' => 0.05, 'rotten' => 0.03], $prediction->probabilities);
    }

    public function test_sensor_data_still_saved_when_ml_service_down(): void
    {
        // ML service mengembalikan error / tidak terjangkau.
        Http::fake([
            '*' => Http::response(['detail' => 'Model belum dimuat'], 503),
        ]);

        Device::factory()->create(['device_id' => 'ECASSAVA-001']);

        $payload = [
            'device_id' => 'ECASSAVA-001',
            'mq2' => 1450,
            'mq3' => 920,
            'mq135' => 1100,
        ];

        $response = $this->postJson('/api/sensor-data', $payload);

        // Penyimpanan data sensor TIDAK boleh gagal walau ML down.
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('sensor_readings', [
            'mq2' => 1450,
            'mq3' => 920,
            'mq135' => 1100,
        ]);
    }

    public function test_job_skips_prediction_when_window_not_full(): void
    {
        config(['ecassava.ml_window_size' => 30]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'label' => 'fresh',
                'confidence' => 0.9,
                'probabilities' => ['fresh' => 0.9, 'stale' => 0.07, 'rotten' => 0.03],
                'model_name' => 'SVM',
                'message' => 'Klasifikasi SVM berhasil',
            ], 200),
        ]);

        $device = Device::factory()->create(['device_id' => 'ECASSAVA-001']);

        // Hanya 5 data (< 30) → status "collecting data", prediksi dilewati.
        SensorReading::factory()->count(5)->create([
            'device_id' => $device->id,
            'measurement_session_id' => null,
        ]);

        $job = new ClassifySensorWindowJob($device->id);
        $job->handle(app(MlPredictionService::class), app(AlertService::class));

        $this->assertDatabaseCount('predictions', 0);
        Http::assertNothingSent();
    }

    public function test_low_confidence_prediction_skips_alert(): void
    {
        config(['ecassava.ml_window_size' => 30, 'ecassava.ml_confidence_threshold' => 0.6]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'label' => 'stale',
                'confidence' => 0.4,
                'probabilities' => ['fresh' => 0.35, 'stale' => 0.4, 'rotten' => 0.25],
                'model_name' => 'SVM',
                'uncertain' => true,
                'margin' => 0.05,
                'warnings' => [],
                'message' => 'Klasifikasi SVM berhasil (belum yakin)',
            ], 200),
        ]);

        $device = Device::factory()->create(['device_id' => 'ECASSAVA-001']);
        SensorReading::factory()->count(30)->create([
            'device_id' => $device->id,
            'measurement_session_id' => null,
        ]);

        $job = new ClassifySensorWindowJob($device->id);
        $job->handle(app(MlPredictionService::class), app(AlertService::class));

        // Prediksi tetap disimpan (tebakan terbaik) tapi ditandai belum yakin.
        $this->assertDatabaseHas('predictions', [
            'device_id' => $device->id,
            'predicted_label' => 'stale',
        ]);
        $this->assertTrue(Prediction::first()->is_uncertain);

        // Alert ditahan — tidak ada false alarm dari tebakan 40%.
        $this->assertDatabaseCount('alerts', 0);
    }

    public function test_sensor_fault_window_skips_prediction(): void
    {
        config(['ecassava.ml_window_size' => 30]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'label' => 'rotten',
                'confidence' => 0.9,
                'probabilities' => ['fresh' => 0.05, 'stale' => 0.05, 'rotten' => 0.9],
                'model_name' => 'SVM',
                'uncertain' => true,
                'margin' => 0.85,
                'warnings' => ['mq3_saturated'],
                'message' => 'Klasifikasi SVM berhasil',
            ], 200),
        ]);

        $device = Device::factory()->create(['device_id' => 'ECASSAVA-001']);
        SensorReading::factory()->count(30)->create([
            'device_id' => $device->id,
            'measurement_session_id' => null,
        ]);

        $job = new ClassifySensorWindowJob($device->id);
        $job->handle(app(MlPredictionService::class), app(AlertService::class));

        // Label dari sensor jenuh tidak boleh disimpan.
        $this->assertDatabaseCount('predictions', 0);
        $this->assertDatabaseCount('alerts', 0);
    }

    public function test_ml_health_requires_loaded_model(): void
    {
        $service = app(MlPredictionService::class);

        // Panggilan 1: health 200 tapi model belum termuat → offline.
        // Panggilan 2: health 200 + model termuat → online.
        $calls = 0;
        Http::fake(function () use (&$calls) {
            $calls++;

            return $calls === 1
                ? Http::response(['status' => 'ok'], 200)
                : Http::response(['status' => 'ok', 'model_loaded' => true], 200);
        });
        $this->assertFalse($service->isHealthy());

        Cache::forget('ml-service-health');
        $this->assertTrue($service->isHealthy());
    }

    public function test_prediction_latest_api_returns_collecting_status(): void
    {
        config(['ecassava.ml_window_size' => 30]);
        Http::fake(['*' => Http::response(['status' => 'ok'], 200)]);

        $device = Device::factory()->create(['device_id' => 'ECASSAVA-001']);
        SensorReading::factory()->count(5)->create(['device_id' => $device->id]);

        $response = $this->getJson('/api/prediction/latest?device_id=ECASSAVA-001');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'window_status' => [
                'current' => 5,
                'target' => 30,
                'ready' => false,
            ],
        ]);
    }
}
