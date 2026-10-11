<?php

namespace App\Models;

use Database\Factories\SensorReadingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    /** @use HasFactory<SensorReadingFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'measurement_session_id',
        'mq2',
        'mq3',
        'mq135',
        'mq2_voltage',
        'mq3_voltage',
        'mq135_voltage',
        'temperature',
        'recorded_at',
    ];

    protected $casts = [
        'mq2' => 'integer',
        'mq3' => 'integer',
        'mq135' => 'integer',
        'mq2_voltage' => 'float',
        'mq3_voltage' => 'float',
        'mq135_voltage' => 'float',
        'temperature' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function measurementSession(): BelongsTo
    {
        return $this->belongsTo(MeasurementSession::class);
    }
}
