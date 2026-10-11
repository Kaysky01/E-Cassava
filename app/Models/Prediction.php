<?php

namespace App\Models;

use App\CassavaCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'measurement_session_id',
        'predicted_label',
        'confidence',
        'probabilities',
        'model_name',
        'window_size',
        'predicted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'predicted_label' => CassavaCondition::class,
            'confidence' => 'float',
            'probabilities' => 'array',
            'window_size' => 'integer',
            'predicted_at' => 'datetime',
        ];
    }

    /**
     * Label di bawah ambang kepercayaan dianggap belum yakin:
     * tampil sebagai "Belum yakin" dan tidak memicu alert.
     */
    public function getIsUncertainAttribute(): bool
    {
        return ($this->confidence ?? 0) < (float) config('ecassava.ml_confidence_threshold', 0.6);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function measurementSession(): BelongsTo
    {
        return $this->belongsTo(MeasurementSession::class);
    }
}
