<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\MeasurementSession;
use App\Models\SensorReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensorMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('E-CASSAVA');
    }

    public function test_dashboard_renders_successfully(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create([
            'device_id' => 'ECASSAVA-001',
            'name' => 'E-CASSAVA Sensor 01',
            'status' => 'online',
        ]);

        SensorReading::factory()->create([
            'device_id' => $device->id,
            'mq2' => 1200,
            'mq3' => 800,
            'mq135' => 1000,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('ECASSAVA-001');
        $response->assertSee('E-CASSAVA');
    }

    public function test_sensor_data_api_store_creates_reading_and_device(): void
    {
        Device::factory()->create(['device_id' => 'ECASSAVA-001']);

        $payload = [
            'device_id' => 'ECASSAVA-001',
            'mq2' => 1450,
            'mq3' => 920,
            'mq135' => 1100,
            'mq2_voltage' => 1.168,
            'mq3_voltage' => 0.741,
            'mq135_voltage' => 0.886,
        ];

        $response = $this->postJson('/api/sensor-data', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Sensor data saved successfully',
            'data' => [
                'device_id' => 'ECASSAVA-001',
                'mq2' => 1450,
                'mq3' => 920,
                'mq135' => 1100,
            ],
        ]);

        $this->assertDatabaseHas('devices', [
            'device_id' => 'ECASSAVA-001',
            'status' => 'online',
        ]);

        $this->assertDatabaseHas('sensor_readings', [
            'mq2' => 1450,
            'mq3' => 920,
            'mq135' => 1100,
        ]);
    }

    public function test_dashboard_history_is_newest_first_and_paginated(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['device_id' => 'ECASSAVA-001']);

        SensorReading::factory()->count(15)->create(['device_id' => $device->id]);
        $newest = SensorReading::where('device_id', $device->id)->latest('recorded_at')->first();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        // Filter ?device tetap nempel di link pagination.
        $response->assertSee('device=ECASSAVA-001', false);
        // Info "Menampilkan 1–12 dari 15" memakai view pagination cassava.
        $response->assertSee('Menampilkan');
        // Data terbaru muncul di halaman pertama.
        $response->assertSee((string) $newest->mq2);
    }

    public function test_sensor_data_rejects_unregistered_device(): void
    {
        $response = $this->postJson('/api/sensor-data', [
            'device_id' => 'ECASSAVA-999',
            'mq2' => 1000,
            'mq3' => 800,
            'mq135' => 900,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseMissing('devices', ['device_id' => 'ECASSAVA-999']);
        $this->assertDatabaseCount('sensor_readings', 0);
    }

    public function test_sensor_data_validation_rejects_out_of_range_adc(): void
    {
        $payload = [
            'device_id' => 'ECASSAVA-001',
            'mq2' => 5000, // max is 4095
            'mq3' => 920,
            'mq135' => -5,   // min is 0
        ];

        $response = $this->postJson('/api/sensor-data', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['mq2', 'mq135']);
    }

    public function test_latest_sensor_data_api_returns_most_recent_reading(): void
    {
        $device = Device::factory()->create(['device_id' => 'ECASSAVA-001']);

        SensorReading::factory()->create([
            'device_id' => $device->id,
            'mq2' => 500,
            'recorded_at' => now()->subMinutes(5),
        ]);

        SensorReading::factory()->create([
            'device_id' => $device->id,
            'mq2' => 1500,
            'recorded_at' => now(),
        ]);

        $response = $this->getJson('/api/sensor/latest');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'device_id' => 'ECASSAVA-001',
                'mq2' => 1500,
            ],
        ]);
    }

    public function test_sensor_history_api_is_paginated(): void
    {
        $device = Device::factory()->create(['device_id' => 'ECASSAVA-001']);

        SensorReading::factory()->count(15)->create([
            'device_id' => $device->id,
        ]);

        $response = $this->getJson('/api/sensor/history?per_page=5');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
        $response->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
    }

    public function test_can_start_and_end_measurement_session(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['device_id' => 'ECASSAVA-001']);

        // Start session
        $response = $this->actingAs($user)->post('/sessions', [
            'device_id' => 'ECASSAVA-001',
            'name' => 'Uji Singkong Segar Hari 1',
            'cassava_condition' => 'fresh',
            'notes' => 'Singkong baru dipanen',
        ]);

        $response->assertRedirect(route('sessions.index'));

        $session = MeasurementSession::where('name', 'Uji Singkong Segar Hari 1')->first();
        $this->assertNotNull($session);
        $this->assertTrue($session->isActive());

        // End session
        $endResponse = $this->actingAs($user)->patch("/sessions/{$session->id}/end");
        $endResponse->assertRedirect(route('sessions.index'));

        $session->refresh();
        $this->assertFalse($session->isActive());
    }

    public function test_csv_export_streams_training_data(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['device_id' => 'ECASSAVA-001']);
        $session = MeasurementSession::create([
            'device_id' => $device->id,
            'name' => 'Test Batch',
            'cassava_condition' => 'fresh',
            'started_at' => now()->subHour(),
            'ended_at' => now(),
        ]);

        SensorReading::factory()->count(5)->create([
            'device_id' => $device->id,
            'measurement_session_id' => $session->id,
        ]);

        $response = $this->actingAs($user)->get('/sessions/export-csv');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }
}
