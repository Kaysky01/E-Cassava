<?php

namespace App\Http\Resources;

use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Prediction
 */
class PredictionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'device_id' => $this->device?->device_id,
            'device_name' => $this->device?->name,
            'measurement_session_id' => $this->measurement_session_id,
            'session_name' => $this->measurementSession?->name,
            'predicted_label' => $this->predicted_label?->value,
            'label_text' => $this->predicted_label?->label(),
            'label_color' => $this->predicted_label?->color(),
            'confidence' => $this->confidence,
            'confidence_percent' => $this->confidence ? round($this->confidence * 100, 1) : null,
            'uncertain' => $this->is_uncertain,
            'probabilities' => $this->probabilities,
            'model_name' => $this->model_name,
            'window_size' => $this->window_size,
            'predicted_at' => $this->predicted_at?->toIso8601String(),
            'predicted_at_human' => $this->predicted_at?->diffForHumans(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
