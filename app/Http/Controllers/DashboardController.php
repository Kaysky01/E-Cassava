<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\MeasurementSession;
use App\Models\Prediction;
use App\Models\SensorReading;
use App\Services\AlertService;
use App\Services\InsightService;
use App\Services\MlPredictionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        MlPredictionService $mlService,
        InsightService $insights,
        AlertService $alerts,
    ): View {
        $userId = auth()->id();
        $windowSize = (int) config('ecassava.ml_window_size', 30);

        $devices = Device::ownedBy($userId)
            ->with(['latestPrediction', 'latestReading'])
            ->withCount('sensorReadings')
            ->orderBy('created_at')
            ->get();

        $devices->each(function (Device $device) use ($alerts): void {
            $device->updateOnlineStatus();
            $alerts->checkOffline($device);
        });

        // ── Pemilih alat aktif ──
        $selectedId = (string) $request->query('device', 'all');
        $selected = $selectedId !== 'all'
            ? $devices->firstWhere('device_id', $selectedId)
            : null;

        if ($selectedId !== 'all' && is_null($selected)) {
            $selectedId = 'all';
        }

        $scopedDevices = $selected ? collect([$selected]) : $devices;
        $scopedIds = $scopedDevices->pluck('id');

        // ── Ringkasan ──
        $onlineCount = $devices->where('is_online', true)->count();
        $safeCount = $devices->filter(fn (Device $d) => ($d->latestPrediction?->predicted_label->value ?? 'fresh') === 'fresh')->count();
        $totalSessions = MeasurementSession::whereIn('device_id', $devices->pluck('id'))->count();
        $latestPrediction = Prediction::whereIn('device_id', $devices->pluck('id'))
            ->with(['device', 'measurementSession'])
            ->latest('predicted_at')
            ->first();

        // ── Kartu AI utama: perangkat terpilih atau prediksi terbaru ──
        $focusDevice = $selected ?? $latestPrediction?->device ?? $devices->first();
        $focusPrediction = $selected
            ? $selected->latestPrediction
            : $latestPrediction;
        $focusCount = $focusDevice
            ? SensorReading::where('device_id', $focusDevice->id)->count()
            : 0;

        // ── Grafik live ──
        $range = in_array($request->query('range'), ['1h', '24h', '7d'], true)
            ? $request->query('range')
            : '1h';

        $since = match ($range) {
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            default => now()->subHour(),
        };

        $chartDevice = $focusDevice;
        $chartReadings = $chartDevice
            ? SensorReading::where('device_id', $chartDevice->id)
                ->where('recorded_at', '>=', $since)
                ->orderBy('recorded_at')
                ->limit(300)
                ->get(['mq2', 'mq3', 'mq135', 'temperature', 'recorded_at'])
            : collect();

        // Fallback: 50 titik terakhir bila range kosong.
        if ($chartReadings->isEmpty() && $chartDevice) {
            $chartReadings = SensorReading::where('device_id', $chartDevice->id)
                ->latest('recorded_at')
                ->limit(50)
                ->get(['mq2', 'mq3', 'mq135', 'temperature', 'recorded_at'])
                ->reverse()
                ->values();
        }

        $chartLabelFormat = $range === '7d' ? 'd M H:i' : 'H:i';

        $chartData = $chartReadings->map(fn (SensorReading $r): array => [
            'label' => $r->recorded_at?->translatedFormat($chartLabelFormat),
            'mq2' => $r->mq2,
            'mq3' => $r->mq3,
            'mq135' => $r->mq135,
            'temperature' => $r->temperature !== null ? (float) $r->temperature : null,
        ]);

        $lastChart = $chartData->last();
        $chartNowText = $lastChart
            ? sprintf(
                'M2:%s M3:%s M5:%s%s',
                $lastChart['mq2'],
                $lastChart['mq3'],
                $lastChart['mq135'],
                $lastChart['temperature'] !== null ? sprintf(' • %s°C', number_format((float) $lastChart['temperature'], 1)) : ''
            )
            : '';

        // ── Timeline prediksi ──
        $timeline = Prediction::whereIn('device_id', $scopedIds)
            ->orderBy('predicted_at')
            ->limit(60)
            ->get(['predicted_label', 'predicted_at', 'device_id'])
            ->map(fn (Prediction $p): array => [
                'label' => $p->predicted_at?->format('H:i'),
                'value' => $p->predicted_label->value === 'fresh' ? 0 : ($p->predicted_label->value === 'stale' ? 1 : 2),
                'name' => $p->predicted_label->value,
            ]);

        // ── Insight per gudang (maks 6) ──
        $allInsights = [];
        foreach ($scopedDevices->take(4) as $device) {
            foreach ($insights->forDevice($device) as $item) {
                $item['device'] = $device->name;
                $allInsights[] = $item;
            }
        }
        $allInsights = array_slice($allInsights, 0, 6);

        // ── Riwayat (terbaru dulu, filter ?device & ?range nempel saat pindah halaman) ──
        $historyReadings = SensorReading::whereIn('device_id', $scopedIds)
            ->with('device')
            ->latest('recorded_at')
            ->paginate(12)
            ->withQueryString();

        $predictionHistory = Prediction::whereIn('device_id', $scopedIds)
            ->with(['device', 'measurementSession'])
            ->latest('predicted_at')
            ->paginate(8, ['*'], 'prediction_page')
            ->withQueryString();

        $isMlOnline = $mlService->isHealthy();

        return view('dashboard.index', compact(
            'devices',
            'selectedId',
            'selected',
            'scopedDevices',
            'onlineCount',
            'safeCount',
            'totalSessions',
            'latestPrediction',
            'focusDevice',
            'focusPrediction',
            'focusCount',
            'windowSize',
            'range',
            'chartDevice',
            'chartData',
            'chartNowText',
            'timeline',
            'allInsights',
            'historyReadings',
            'predictionHistory',
            'isMlOnline',
        ));
    }
}
