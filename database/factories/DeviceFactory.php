<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'device_id' => 'ECASSAVA-'.str_pad($this->faker->numberBetween(1, 99), 3, '0', STR_PAD_LEFT),
            'name' => 'E-CASSAVA Sensor '.$this->faker->numberBetween(1, 99),
            'location' => null,
            'status' => $this->faker->randomElement(['online', 'offline']),
            'last_seen_at' => $this->faker->optional()->dateTimeBetween('-5 minutes', 'now'),
            'api_key' => null,
            'api_key_hint' => null,
            'is_active' => true,
        ];
    }

    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'online',
            'last_seen_at' => now()->subSeconds(rand(1, 30)),
        ]);
    }

    public function offline(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'offline',
            'last_seen_at' => now()->subMinutes(rand(10, 60)),
        ]);
    }
}
