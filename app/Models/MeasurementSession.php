<?php

namespace App\Models;

use App\CassavaCondition;
use Database\Factories\MeasurementSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeasurementSession extends Model
{
    /** @use HasFactory<MeasurementSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'name',
        'cassava_condition',
        'notes',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'cassava_condition' => CassavaCondition::class,
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function sensorReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    public function isActive(): bool
    {
        return is_null($this->ended_at);
    }

    /**
     * Hitung rata-rata nilai sensor dalam sesi ini.
     *
     * @return array{mq2_mean: float, mq3_mean: float, mq135_mean: float, mq2_std: float, mq3_std: float, mq135_std: float, mq2_min: int, mq3_min: int, mq135_min: int, mq2_max: int, mq3_max: int, mq135_max: int, reading_count: int}
     */
    public function computeFeatures(): array
    {
        $readings = $this->sensorReadings()->get(['mq2', 'mq3', 'mq135']);

        if ($readings->isEmpty()) {
            return [
                'mq2_mean' => 0.0, 'mq3_mean' => 0.0, 'mq135_mean' => 0.0,
                'mq2_std' => 0.0, 'mq3_std' => 0.0, 'mq135_std' => 0.0,
                'mq2_min' => 0,   'mq3_min' => 0,   'mq135_min' => 0,
                'mq2_max' => 0,   'mq3_max' => 0,   'mq135_max' => 0,
                'reading_count' => 0,
            ];
        }

        $mq2Values = $readings->pluck('mq2')->map(fn ($v) => (float) $v);
        $mq3Values = $readings->pluck('mq3')->map(fn ($v) => (float) $v);
        $mq135Values = $readings->pluck('mq135')->map(fn ($v) => (float) $v);

        return [
            'mq2_mean' => round($mq2Values->average(), 4),
            'mq3_mean' => round($mq3Values->average(), 4),
            'mq135_mean' => round($mq135Values->average(), 4),
            'mq2_std' => round($this->stdDev($mq2Values->toArray()), 4),
            'mq3_std' => round($this->stdDev($mq3Values->toArray()), 4),
            'mq135_std' => round($this->stdDev($mq135Values->toArray()), 4),
            'mq2_min' => (int) $mq2Values->min(),
            'mq3_min' => (int) $mq3Values->min(),
            'mq135_min' => (int) $mq135Values->min(),
            'mq2_max' => (int) $mq2Values->max(),
            'mq3_max' => (int) $mq3Values->max(),
            'mq135_max' => (int) $mq135Values->max(),
            'reading_count' => $readings->count(),
        ];
    }

    /** @param float[] $values */
    private function stdDev(array $values): float
    {
        $count = count($values);
        if ($count < 2) {
            return 0.0;
        }

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn ($v) => ($v - $mean) ** 2, $values)) / ($count - 1);

        return sqrt($variance);
    }
}
