<?php

namespace Tests\Feature;

use App\Jobs\ClassifySensorWindowJob;
use App\Models\Alert;
use App\Models\Device;
use App\Models\MeasurementSession;
use App\Models\SensorReading;
use App\Models\User;
use App\Services\AlertService;
use App\Services\MlPredictionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EcassavaUpgradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_landing_page_and_login_redirects_to_dashboard(): void
    {
        $this->get('/')->assertStatus(200)->assertSee(__('landing.hero_title'));

        $user = User::factory()->create();

        $this->actingAs($user)->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_user_can_register_and_login(): void
    {
        $this->get('/register')->assertStatus(200)->assertSee(__('auth.register_title'));
        $this->get('/login')->assertStatus(200)->assertSee(__('auth.login_title'));

        $response = $this->post('/register', [
            'name' => 'Pak Tani',
            'email' => 'tani@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'tani@example.com']);
    }

    public function test_guest_cannot_access_app_pages(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/devices')->assertRedirect(route('login'));
        $this->get('/sessions')->assertRedirect(route('login'));
        $this->get('/ml')->assertRedirect(route('login'));
        $this->get('/alerts')->assertRedirect(route('login'));
    }

    public function test_user_cannot_access_other_users_device_session_or_prediction(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $deviceB = Device::factory()->for($userB)->create(['device_id' => 'ECASSAVA-090']);
        $sessionB = MeasurementSession::create([
            'device_id' => $deviceB->id,
            'name' => 'Milik B',
            'cassava_condition' => 'fresh',
            'started_at' => now()->subHour(),
        ]);

        // Device milik B: A ditolak (403, policy).
        $this->actingAs($userA)->get("/devices/{$deviceB->id}/edit")->assertForbidden();
        $this->actingAs($userA)->patch("/devices/{$deviceB->id}", [
            'name' => 'Coba ambil',
        ])->assertForbidden();
        $this->actingAs($userA)->delete("/devices/{$deviceB->id}")->assertForbidden();

        // Sesi milik B tidak bisa dibuka/diakhiri A.
        $this->actingAs($userA)->get("/sessions/{$sessionB->id}")->assertForbidden();
        $this->actingAs($userA)->patch("/sessions/{$sessionB->id}/end")->assertForbidden();

        // Prediksi manual untuk device B via A → 404.
        $this->actingAs($userA)->postJson('/ml/predict', ['device_id' => 'ECASSAVA-090'])
            ->assertStatus(404);

        // Dashboard A tidak menampilkan device B.
        $this->actingAs($userA)->get('/dashboard')->assertStatus(200)->assertDontSee('ECASSAVA-090');

        // Data B tetap utuh.
        $this->assertDatabaseHas('devices', ['device_id' => 'ECASSAVA-090', 'user_id' => $userB->id]);
    }

    public function test_device_creation_uses_name_as_device_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/devices', [
            'name' => 'Gudang Wetan',
            'location' => 'Desa Sumber',
        ]);

        $response->assertRedirect(route('devices.index'));
        $response->assertSessionHas('api_plain_key');

        $device = Device::where('user_id', $user->id)->first();
        $this->assertNotNull($device);
        $this->assertSame('Gudang Wetan', $device->device_id);
        $this->assertSame('Gudang Wetan', $device->name);
        $this->assertNotNull($device->api_key);
        $this->assertGreaterThanOrEqual(32, strlen((string) $response->getSession()->get('api_plain_key')));
        $this->assertTrue($device->verifyApiKey((string) $response->getSession()->get('api_plain_key')));

        // Modal snippet tampil di halaman index (key sekali tampil).
        $this->actingAs($user)->get('/devices')->assertStatus(200)->assertSee($device->device_id);
    }

    public function test_device_creation_rejects_duplicate_name(): void
    {
        $user = User::factory()->create();
        Device::factory()->for($user)->create(['device_id' => 'Gudang Wetan', 'name' => 'Gudang Wetan']);

        $response = $this->actingAs($user)->post('/devices', [
            'name' => 'Gudang Wetan',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertSame(1, Device::where('user_id', $user->id)->count());
    }

    public function test_sensor_data_requires_api_key_when_device_has_one(): void
    {
        $user = User::factory()->create();
        $plain = 'rahasia-32-karakter-minimal-abcdef123456';
        $device = Device::factory()->for($user)->create([
            'device_id' => 'ECASSAVA-077',
            'api_key' => hash('sha256', $plain),
            'api_key_hint' => substr($plain, -4),
        ]);

        $payload = ['device_id' => 'ECASSAVA-077', 'mq2' => 1000, 'mq3' => 800, 'mq135' => 900];

        // Tanpa key → 401.
        $this->postJson('/api/sensor-data', $payload)->assertStatus(401);

        // Key salah → 401.
        $this->postJson('/api/sensor-data', $payload, ['X-API-KEY' => 'salah'])
            ->assertStatus(401);

        // Key benar via header → 201 dan masuk ke alat yang benar.
        $this->postJson('/api/sensor-data', $payload, ['X-API-KEY' => $plain])
            ->assertStatus(201);
        $this->assertDatabaseHas('sensor_readings', ['device_id' => $device->id, 'mq2' => 1000]);

        // Key benar via field → 201.
        $this->postJson('/api/sensor-data', $payload + ['api_key' => $plain])
            ->assertStatus(201);
    }

    public function test_regenerate_key_invalidates_old_key(): void
    {
        $user = User::factory()->create();
        $old = 'old-key-32-karakter-minimal-abcdef123456';
        $device = Device::factory()->for($user)->create([
            'device_id' => 'ECASSAVA-078',
            'api_key' => hash('sha256', $old),
        ]);

        $response = $this->actingAs($user)->post("/devices/{$device->id}/regenerate");
        $response->assertRedirect(route('devices.index'));
        $new = (string) $response->getSession()->get('api_plain_key');
        $this->assertNotEmpty($new);
        $this->assertNotSame($old, $new);

        $payload = ['device_id' => 'ECASSAVA-078', 'mq2' => 1, 'mq3' => 2, 'mq135' => 3];
        $this->postJson('/api/sensor-data', $payload, ['X-API-KEY' => $old])->assertStatus(401);
        $this->postJson('/api/sensor-data', $payload, ['X-API-KEY' => $new])->assertStatus(201);
    }

    public function test_classification_transition_creates_alert(): void
    {
        config(['ecassava.ml_window_size' => 30]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'label' => 'rotten',
                'confidence' => 0.88,
                'probabilities' => ['fresh' => 0.05, 'stale' => 0.07, 'rotten' => 0.88],
                'model_name' => 'SVM',
                'message' => 'ok',
            ], 200),
        ]);

        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['device_id' => 'ECASSAVA-055']);

        SensorReading::factory()->count(30)->create([
            'device_id' => $device->id,
            'measurement_session_id' => null,
        ]);

        (new ClassifySensorWindowJob($device->id))->handle(
            app(MlPredictionService::class), app(AlertService::class)
        );

        $this->assertDatabaseHas('predictions', ['device_id' => $device->id, 'predicted_label' => 'rotten']);
        $this->assertDatabaseHas('alerts', ['user_id' => $user->id, 'device_id' => $device->id, 'type' => 'rotten']);

        // Alert bisa dibaca pemiliknya, tidak bisa diintip user lain.
        $alert = Alert::first();
        $this->withSession(['locale' => 'id'])->actingAs($user)->get('/alerts')->assertStatus(200)->assertSee('Busuk');

        $other = User::factory()->create();
        $this->actingAs($other)->patch("/alerts/{$alert->id}/read")->assertStatus(404);
        $this->actingAs($user)->patch("/alerts/{$alert->id}/read")->assertRedirect();
        $this->assertNotNull($alert->fresh()->read_at);
    }

    public function test_dashboard_api_overview_and_sensor_are_user_scoped(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->for($user)->create(['device_id' => 'ECASSAVA-031']);
        SensorReading::factory()->count(3)->create(['device_id' => $device->id]);

        $this->getJson('/dashboard/api/overview')->assertUnauthorized();

        $this->actingAs($user)->getJson('/dashboard/api/overview')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('devices.0.device_id', 'ECASSAVA-031')
            ->assertJsonStructure([
                'focus_device_id',
                'timeline',
                'devices' => [
                    '*' => ['prediction'],
                ],
            ]);

        $this->actingAs($user)->getJson('/dashboard/api/sensor?device_id=ECASSAVA-031&range=1h')
            ->assertOk()
            ->assertJsonPath('success', true);

        $other = User::factory()->create();
        $this->actingAs($other)->getJson('/dashboard/api/sensor?device_id=ECASSAVA-031')
            ->assertNotFound();
    }

    public function test_locale_switch_changes_language_and_persists(): void
    {
        $response = $this->get('/lang/en');
        $response->assertRedirect();
        $response->assertCookie('locale', 'en');

        $this->get('/')->assertSee('Detect cassava freshness');

        $this->get('/lang/id')->assertCookie('locale', 'id');
        $this->get('/')->assertSee('Deteksi kesegaran singkong');
    }
}
