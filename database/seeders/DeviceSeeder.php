<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::orderBy('id')->first();

        Device::firstOrCreate(
            ['device_id' => 'ECASSAVA-001'],
            [
                'user_id' => $user?->id,
                'name' => 'E-CASSAVA Sensor 01',
                'status' => 'offline',
                'last_seen_at' => null,
                'is_active' => true,
            ]
        );
    }
}
