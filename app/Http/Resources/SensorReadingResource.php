<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SensorReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'device_id' => $this->device->device_id,
            'device_name' => $this->device->name,
            'mq2' => $this->mq2,
            'mq3' => $this->mq3,
            'mq135' => $this->mq135,
            'mq2_voltage' => $this->mq2_voltage,
            'mq3_voltage' => $this->mq3_voltage,
            'mq135_voltage' => $this->mq135_voltage,
            'temperature' => $this->temperature,
            'recorded_at' => $this->recorded_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
