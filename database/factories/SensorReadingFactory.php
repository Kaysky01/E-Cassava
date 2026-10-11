<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SensorReading>
 */
class SensorReadingFactory extends Factory
{
    protected $model = SensorReading::class;

    public function definition(): array
    {
        $mq2 = $this->faker->numberBetween(200, 3000);
        $mq3 = $this->faker->numberBetween(200, 2500);
        $mq135 = $this->faker->numberBetween(300, 3500);

        return [
            'device_id' => Device::factory(),
            'mq2' => $mq2,
            'mq3' => $mq3,
            'mq135' => $mq135,
            'mq2_voltage' => round($mq2 / 4095 * 3.3, 4),
            'mq3_voltage' => round($mq3 / 4095 * 3.3, 4),
            'mq135_voltage' => round($mq135 / 4095 * 3.3, 4),
            'temperature' => $this->faker->randomFloat(2, 25, 90),
            'recorded_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ];
    }
}
