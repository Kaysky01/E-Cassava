<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'device_id' => $this->device_id,
            'name' => $this->name,
            'status' => $this->status,
            'is_online' => $this->is_online,
            'last_seen_at' => $this->last_seen_at?->toIso8601String(),
            'last_seen_human' => $this->last_seen_at?->diffForHumans(),
        ];
    }
}
