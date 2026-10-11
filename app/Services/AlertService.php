<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Device;
use App\Models\Prediction;

class AlertService
{
    /**
     * Buat alert otomatis saat label berubah ke stale/rotten.
     */
    public function handlePrediction(Device $device, Prediction $prediction, ?string $previousLabel): void
    {
        if (is_null($device->user_id)) {
            return;
        }

        $label = $prediction->predicted_label->value;

        if (! in_array($label, ['stale', 'rotten'], true)) {
            return;
        }

        // Tahan alert bila AI belum yakin (confidence di bawah ambang).
        if ($prediction->is_uncertain) {
            return;
        }

        // Hanya saat transisi (menghindari spam tiap window).
        if ($previousLabel === $label) {
            return;
        }

        $pct = number_format(($prediction->confidence ?? 0) * 100, 1);

        if ($label === 'stale') {
            $type = 'stale';
            $titleId = __('alerts.type_stale', locale: 'id');
            $titleEn = __('alerts.type_stale', locale: 'en');
            $bodyId = __('alerts.msg_stale', ['device' => $device->name, 'pct' => $pct], 'id');
            $bodyEn = __('alerts.msg_stale', ['device' => $device->name, 'pct' => $pct], 'en');
        } else {
            $type = 'rotten';
            $titleId = __('alerts.type_rotten', locale: 'id');
            $titleEn = __('alerts.type_rotten', locale: 'en');
            $bodyId = __('alerts.msg_rotten', ['device' => $device->name, 'pct' => $pct], 'id');
            $bodyEn = __('alerts.msg_rotten', ['device' => $device->name, 'pct' => $pct], 'en');
        }

        Alert::create([
            'user_id' => $device->user_id,
            'device_id' => $device->id,
            'type' => $type,
            'title_id' => $titleId,
            'title_en' => $titleEn,
            'body_id' => $bodyId,
            'body_en' => $bodyEn,
        ]);
    }

    /**
     * Buat alert saat alat offline lebih dari batas (maks 1 per hari per alat).
     */
    public function checkOffline(Device $device): void
    {
        if (is_null($device->user_id) || is_null($device->last_seen_at)) {
            return;
        }

        $threshold = (int) config('ecassava.device_online_threshold_minutes', 2);
        $offlineMinutes = (int) $device->last_seen_at->diffInMinutes(now());

        if ($offlineMinutes < $threshold * 5) {
            return;
        }

        $exists = Alert::where('device_id', $device->id)
            ->where('type', 'offline')
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($exists) {
            return;
        }

        Alert::create([
            'user_id' => $device->user_id,
            'device_id' => $device->id,
            'type' => 'offline',
            'title_id' => __('alerts.type_offline', locale: 'id'),
            'title_en' => __('alerts.type_offline', locale: 'en'),
            'body_id' => __('alerts.msg_offline', ['device' => $device->name, 'min' => $offlineMinutes], 'id'),
            'body_en' => __('alerts.msg_offline', ['device' => $device->name, 'min' => $offlineMinutes], 'en'),
        ]);
    }
}
