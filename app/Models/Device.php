<?php

namespace App\Models;

use Database\Factories\DeviceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends Model
{
    /** @use HasFactory<DeviceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'name',
        'location',
        'status',
        'last_seen_at',
        'api_key',
        'api_key_hint',
        'is_active',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope ke perangkat milik user tertentu.
     *
     * @param  Builder<Device>  $query
     * @return Builder<Device>
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Device is considered online if active and last_seen_at is within the threshold.
     */
    public function getIsOnlineAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (is_null($this->last_seen_at)) {
            return false;
        }

        $threshold = (int) config('ecassava.device_online_threshold_minutes', 2);

        return $this->last_seen_at->diffInMinutes(now()) <= $threshold;
    }

    /**
     * Compute and update status based on last_seen_at.
     */
    public function updateOnlineStatus(): void
    {
        $this->status = $this->isOnline ? 'online' : 'offline';
        $this->save();
    }

    /**
     * Verifikasi API key teks-jelas (dibandingkan dengan hash yang tersimpan).
     * Perangkat lawas tanpa key tetap diizinkan (kompatibilitas mundur).
     */
    public function verifyApiKey(?string $plainKey): bool
    {
        if (is_null($this->api_key) || $this->api_key === '') {
            return true;
        }

        if (is_null($plainKey) || $plainKey === '') {
            return false;
        }

        return hash_equals($this->api_key, hash('sha256', $plainKey));
    }

    public function sensorReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }

    public function latestReading(): HasOne
    {
        return $this->hasOne(SensorReading::class)->latestOfMany('recorded_at');
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    public function latestPrediction(): HasOne
    {
        return $this->hasOne(Prediction::class)->latestOfMany('predicted_at');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function measurementSessions(): HasMany
    {
        return $this->hasMany(MeasurementSession::class);
    }
}
