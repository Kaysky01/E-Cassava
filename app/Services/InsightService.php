<?php

namespace App\Services;

use App\Models\Device;
use App\Models\SensorReading;

class InsightService
{
    /**
     * Rekomendasi rule-based per alat (tanpa LLM), bahasa sederhana untuk petani.
     *
     * @return array<int, array{level: string, title: string, desc: string}>
     */
    public function forDevice(Device $device): array
    {
        $insights = [];

        $recent = SensorReading::where('device_id', $device->id)
            ->where('recorded_at', '>=', now()->subMinutes(10))
            ->orderBy('recorded_at')
            ->get(['mq2', 'mq3', 'mq135', 'recorded_at']);

        $previous = SensorReading::where('device_id', $device->id)
            ->whereBetween('recorded_at', [now()->subMinutes(20), now()->subMinutes(10)])
            ->get(['mq2', 'mq3', 'mq135']);

        if ($recent->count() >= 3 && $previous->count() >= 3) {
            $recentMq3 = (float) $recent->avg('mq3');
            $prevMq3 = (float) $previous->avg('mq3');

            if ($prevMq3 > 0) {
                $rise = (($recentMq3 - $prevMq3) / $prevMq3) * 100;

                if ($rise >= 20) {
                    $insights[] = [
                        'level' => $rise >= 40 ? 'high' : 'medium',
                        'title' => __('insights.mq3_rising_title', ['pct' => number_format($rise, 0)]),
                        'desc' => __('insights.mq3_rising_desc'),
                    ];
                }
            }

            $recentMq135 = (float) $recent->avg('mq135');
            $prevMq135 = (float) $previous->avg('mq135');

            if ($recentMq135 > 1500 && $recentMq135 > $prevMq135) {
                $insights[] = [
                    'level' => 'medium',
                    'title' => __('insights.mq135_high_title'),
                    'desc' => __('insights.mq135_high_desc'),
                ];
            }

            $spread = $recent->max('mq2') - $recent->min('mq2');

            if ($spread > 800) {
                $insights[] = [
                    'level' => 'medium',
                    'title' => __('insights.mq2_spike_title'),
                    'desc' => __('insights.mq2_spike_desc'),
                ];
            }
        }

        $latestPrediction = $device->latestPrediction;

        if ($latestPrediction) {
            $label = $latestPrediction->predicted_label->value;

            if ($label === 'stale') {
                $insights[] = [
                    'level' => 'medium',
                    'title' => __('insights.stale_title'),
                    'desc' => __('insights.stale_desc'),
                ];
            } elseif ($label === 'rotten') {
                $insights[] = [
                    'level' => 'high',
                    'title' => __('insights.rotten_title'),
                    'desc' => __('insights.rotten_desc'),
                ];
            }
        }

        if (! $device->is_online && ! is_null($device->last_seen_at)) {
            $insights[] = [
                'level' => 'medium',
                'title' => __('insights.offline_title'),
                'desc' => __('insights.offline_desc'),
            ];
        }

        if (empty($insights)) {
            $insights[] = [
                'level' => 'low',
                'title' => __('insights.normal_title'),
                'desc' => __('insights.normal_desc'),
            ];
        }

        // Urutkan: high dulu.
        usort($insights, fn ($a, $b) => $this->rank($b['level']) <=> $this->rank($a['level']));

        return $insights;
    }

    private function rank(string $level): int
    {
        return match ($level) {
            'high' => 3,
            'medium' => 2,
            default => 1,
        };
    }
}
