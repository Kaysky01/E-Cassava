<?php

namespace Database\Seeders;

use App\CassavaCondition;
use App\Models\Device;
use App\Models\MeasurementSession;
use App\Models\Prediction;
use App\Models\SensorReading;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@ecassava.id'],
            [
                'name' => 'Petani Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $warehouses = [
            ['device_id' => 'ECASSAVA-001', 'name' => 'Gudang Panen Wetan', 'location' => 'Gudang A — Desa Sumber', 'base' => 1100],
            ['device_id' => 'ECASSAVA-002', 'name' => 'Gudang Panen Kulon', 'location' => 'Gudang B — Desa Sumber', 'base' => 1900],
        ];

        foreach ($warehouses as $index => $wh) {
            $device = Device::firstOrCreate(
                ['device_id' => $wh['device_id']],
                [
                    'user_id' => $user->id,
                    'name' => $wh['name'],
                    'location' => $wh['location'],
                    'status' => 'online',
                    'last_seen_at' => now()->subSeconds(30),
                    'is_active' => true,
                ]
            );

            $device->update([
                'user_id' => $user->id,
                'location' => $wh['location'],
                'status' => 'online',
                'last_seen_at' => now()->subSeconds(30),
                'is_active' => true,
            ]);

            // Sesi berlabel untuk export CSV.
            $session = MeasurementSession::firstOrCreate(
                ['device_id' => $device->id, 'name' => 'Demo '.($index + 1)],
                [
                    'cassava_condition' => $index === 0 ? CassavaCondition::Fresh : CassavaCondition::Stale,
                    'notes' => 'Data demo sintetis untuk presentasi.',
                    'started_at' => now()->subHours(3),
                    'ended_at' => now()->subHours(2),
                ]
            );

            // 120 pembacaan sintetis per gudang (tren naik agar grafik hidup).
            if (SensorReading::where('device_id', $device->id)->count() < 100) {
                $now = now()->subMinutes(120);
                for ($i = 0; $i < 120; $i++) {
                    $drift = (int) ($i * ($index === 0 ? 2 : 8));
                    SensorReading::create([
                        'device_id' => $device->id,
                        'measurement_session_id' => $i < 60 ? $session->id : null,
                        'mq2' => max(0, min(4095, $wh['base'] + $drift + random_int(-60, 60))),
                        'mq3' => max(0, min(4095, (int) ($wh['base'] * 0.75) + $drift + random_int(-50, 50))),
                        'mq135' => max(0, min(4095, (int) ($wh['base'] * 0.9) + $drift + random_int(-55, 55))),
                        'mq2_voltage' => 1.0,
                        'mq3_voltage' => 0.8,
                        'mq135_voltage' => 0.9,
                        'recorded_at' => $now->copy()->addMinute($i),
                    ]);
                }
            }

            // Prediksi sintetis terakhir per gudang.
            if (! Prediction::where('device_id', $device->id)->exists()) {
                $label = $index === 0 ? 'fresh' : 'stale';
                Prediction::create([
                    'device_id' => $device->id,
                    'measurement_session_id' => $session->id,
                    'predicted_label' => $label,
                    'confidence' => $index === 0 ? 0.92 : 0.78,
                    'probabilities' => $index === 0
                        ? ['fresh' => 0.92, 'stale' => 0.05, 'rotten' => 0.03]
                        : ['fresh' => 0.12, 'stale' => 0.78, 'rotten' => 0.10],
                    'model_name' => 'SVM',
                    'window_size' => 30,
                    'predicted_at' => now()->subMinutes(2),
                ]);
            }
        }
    }
}
