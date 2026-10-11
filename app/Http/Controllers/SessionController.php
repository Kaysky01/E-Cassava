<?php

namespace App\Http\Controllers;

use App\CassavaCondition;
use App\Http\Requests\EndSessionRequest;
use App\Http\Requests\StartSessionRequest;
use App\Models\Device;
use App\Models\MeasurementSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SessionController extends Controller
{
    /**
     * Tampilkan halaman manajemen sesi pengujian.
     */
    public function index(): View
    {
        $userId = auth()->id();

        $devices = Device::ownedBy($userId)->orderBy('name')->get();
        $sessions = MeasurementSession::whereHas('device', fn ($q) => $q->where('user_id', $userId))
            ->with('device')
            ->latest('started_at')
            ->paginate(15);

        $activeSession = MeasurementSession::whereNull('ended_at')
            ->whereHas('device', fn ($q) => $q->where('user_id', $userId))
            ->with('device')
            ->latest('started_at')
            ->first();

        $conditions = CassavaCondition::options();

        return view('sessions.index', compact(
            'devices',
            'sessions',
            'activeSession',
            'conditions',
        ));
    }

    /**
     * Mulai sesi pengujian baru.
     *
     * POST /sessions
     */
    public function store(StartSessionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $device = Device::ownedBy(auth()->id())
            ->where('device_id', $validated['device_id'])
            ->firstOrFail();

        // Akhiri sesi aktif yang mungkin masih berjalan sebelum membuat sesi baru
        MeasurementSession::whereNull('ended_at')
            ->where('device_id', $device->id)
            ->update(['ended_at' => now()]);

        MeasurementSession::create([
            'device_id' => $device->id,
            'name' => $validated['name'],
            'cassava_condition' => $validated['cassava_condition'],
            'notes' => $validated['notes'] ?? null,
            'started_at' => now(),
        ]);

        return redirect()->route('sessions.index')
            ->with('success', 'Sesi pengujian dimulai! ESP32 sekarang merekam data berlabel.');
    }

    /**
     * Akhiri sesi pengujian yang sedang aktif.
     *
     * PATCH /sessions/{session}/end
     */
    public function end(EndSessionRequest $request, MeasurementSession $session): RedirectResponse
    {
        $this->authorize('update', $session);

        if (! $session->isActive()) {
            return redirect()->route('sessions.index')
                ->with('error', 'Sesi ini sudah diakhiri sebelumnya.');
        }

        $session->update([
            'ended_at' => now(),
            'notes' => $request->validated('notes') ?? $session->notes,
        ]);

        $readingCount = $session->sensorReadings()->count();

        return redirect()->route('sessions.index')
            ->with('success', "Sesi diakhiri. {$readingCount} data pembacaan berhasil direkam.");
    }

    /**
     * Tampilkan detail sesi dan fitur-fitur yang diekstrak.
     */
    public function show(MeasurementSession $session): View
    {
        $this->authorize('view', $session);

        $session->load(['device', 'sensorReadings' => fn ($q) => $q->latest('recorded_at')->limit(100)]);

        $features = $session->computeFeatures();

        return view('sessions.show', compact('session', 'features'));
    }

    /**
     * Export semua sesi berlabel sebagai CSV untuk training ML.
     *
     * GET /sessions/export-csv
     */
    public function exportCsv(): StreamedResponse
    {
        $userId = auth()->id();

        $sessions = MeasurementSession::whereHas('device', fn ($q) => $q->where('user_id', $userId))
            ->with('device')
            ->whereNotNull('ended_at')
            ->whereNotNull('cassava_condition')
            ->get();

        $filename = 'ecassava_training_data_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($sessions): void {
            $out = fopen('php://output', 'w');

            // Header baris CSV
            fputcsv($out, [
                'session_id', 'session_name', 'device_id', 'condition',
                'mq2_mean', 'mq3_mean', 'mq135_mean',
                'mq2_std',  'mq3_std',  'mq135_std',
                'mq2_min',  'mq3_min',  'mq135_min',
                'mq2_max',  'mq3_max',  'mq135_max',
                'reading_count', 'started_at', 'ended_at',
            ]);

            foreach ($sessions as $session) {
                $features = $session->computeFeatures();

                fputcsv($out, [
                    $session->id,
                    $session->name,
                    $session->device->device_id,
                    $session->cassava_condition->value,
                    $features['mq2_mean'],   $features['mq3_mean'],   $features['mq135_mean'],
                    $features['mq2_std'],    $features['mq3_std'],    $features['mq135_std'],
                    $features['mq2_min'],    $features['mq3_min'],    $features['mq135_min'],
                    $features['mq2_max'],    $features['mq3_max'],    $features['mq135_max'],
                    $features['reading_count'],
                    $session->started_at?->toDateTimeString(),
                    $session->ended_at?->toDateTimeString(),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
