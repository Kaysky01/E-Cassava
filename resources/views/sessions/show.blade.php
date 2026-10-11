@extends('layouts.app')
@section('title', $session->name)

@section('content')
@php
    $cond = $session->cassava_condition;
    $badge = match($cond?->value) {
        'fresh' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'stale' => 'bg-amber-100 text-amber-800 border-amber-200',
        'rotten' => 'bg-red-100 text-red-800 border-red-200',
        default => 'bg-gray-100 text-gray-700 border-gray-200',
    };
@endphp

<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center gap-2">
        <a href="{{ route('sessions.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3 py-1.5 text-[13px] font-bold text-cassava-700 ring-1 ring-gray-200 transition hover:bg-cassava-50">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
            {{ __('sessions.back') }}
        </a>
    </div>
    <div class="card card-hover p-6 sm:p-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="eyebrow text-cassava-600">{{ __('sessions.detail') }}</p>
            <h2 class="mt-1 text-xl font-extrabold tracking-tight text-gray-900 sm:text-2xl">{{ $session->name }}</h2>
            <p class="mt-1 font-mono text-xs text-gray-500">{{ $session->device->device_id }} &mdash; {{ $session->started_at->translatedFormat('d F Y H:i') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            @if ($cond)
            <span class="inline-flex items-center gap-1.5 rounded-full border px-4 py-2 text-sm font-bold {{ $badge }}">
                {{ $cond->label() }}
            </span>
            @endif
            @if ($session->isActive())
            <form method="POST" action="{{ route('sessions.end', $session) }}">
                @csrf @method('PATCH')
                <button class="inline-flex items-center gap-2 rounded-2xl bg-red-500 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-red-500/30 transition hover:bg-red-600 active:scale-[0.98]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                    {{ __('sessions.end') }}
                </button>
            </form>
            @else
            <button onclick="predictSession()" class="btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/></svg>
                {{ __('sessions.ml_button') }}
            </button>
            @endif
        </div>
    </div>

    <!-- Feature Cards -->
    <div>
        <div class="mb-3 flex items-center justify-between">
            <h3 class="eyebrow text-gray-500">{{ __('sessions.features', ['n' => $features['reading_count']]) }}</h3>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach (['mq2' => ['MQ-2', 'from-orange-400 to-red-500'], 'mq3' => ['MQ-3', 'from-sky-400 to-blue-600'], 'mq135' => ['MQ-135', 'from-violet-400 to-purple-600']] as $key => [$label, $grad])
            <div class="card card-hover relative overflow-hidden p-4">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $grad }}"></div>
                <p class="eyebrow mb-3 text-gray-400">{{ $label }}</p>
                <div class="space-y-1.5 text-xs text-gray-600">
                    <div class="flex justify-between rounded-lg bg-gray-50 px-2.5 py-1.5"><span>{{ __('sessions.avg') }}</span><span class="font-mono font-bold text-gray-900">{{ $features[$key . '_mean'] }}</span></div>
                    <div class="flex justify-between px-2.5"><span>{{ __('sessions.std') }}</span><span class="font-mono">{{ $features[$key . '_std'] }}</span></div>
                    <div class="flex justify-between px-2.5"><span>{{ __('sessions.min') }}</span><span class="font-mono">{{ $features[$key . '_min'] }}</span></div>
                    <div class="flex justify-between px-2.5"><span>{{ __('sessions.max') }}</span><span class="font-mono">{{ $features[$key . '_max'] }}</span></div>
                </div>
            </div>
            @endforeach
            <div class="relative overflow-hidden rounded-3xl border border-cassava-200 bg-gradient-to-br from-cassava-950 to-cassava-800 p-4 text-white shadow-[var(--shadow-card)]">
                <p class="eyebrow mb-3 text-lime-300">{{ __('sessions.session_box') }}</p>
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between rounded-lg bg-white/10 px-2.5 py-1.5"><span class="text-cassava-200">{{ __('sessions.readings') }}</span><span class="font-mono font-bold">{{ $features['reading_count'] }}</span></div>
                    <div class="flex justify-between px-2.5"><span class="text-cassava-200">Status</span><span class="font-bold {{ $session->isActive() ? 'text-emerald-300' : 'text-gray-300' }}">{{ $session->isActive() ? __('sessions.status_active') : __('sessions.status_done') }}</span></div>
                    <div class="flex justify-between px-2.5"><span class="text-cassava-200">{{ __('sessions.duration') }}</span>
                        <span class="font-mono text-xs">
                            {{ $session->started_at->diff($session->ended_at ?? now())->format('%H:%I:%S') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ML Prediction Result Placeholder -->
    <div id="ml-result" class="card hidden p-6">
        <h3 class="mb-3 flex items-center gap-2 font-bold text-gray-900">
            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-cassava-950 text-lime-300">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/></svg>
            </span>
            {{ __('sessions.ml_result') }}
        </h3>
        <div id="ml-result-content"></div>
    </div>

    <!-- Recent Readings Table -->
    <div class="card overflow-hidden">
        <div class="border-b border-gray-100 px-6 py-4">
            <h3 class="font-bold text-gray-900">{{ __('sessions.last_100') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table-shell min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th>Waktu</th>
                        <th class="!text-right !text-orange-500">MQ-2</th>
                        <th class="!text-right !text-sky-500">MQ-3</th>
                        <th class="!text-right !text-violet-500">MQ-135</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($session->sensorReadings as $reading)
                    <tr class="hover:bg-cassava-50/50">
                        <td class="px-5 py-2 text-xs font-mono tabular-nums text-gray-500">{{ $reading->recorded_at?->format('d/m H:i:s') }}</td>
                        <td class="px-5 py-2 text-right font-mono font-bold tabular-nums text-orange-600">{{ $reading->mq2 }}</td>
                        <td class="px-5 py-2 text-right font-mono font-bold tabular-nums text-sky-600">{{ $reading->mq3 }}</td>
                        <td class="px-5 py-2 text-right font-mono font-bold tabular-nums text-violet-600">{{ $reading->mq135 }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-gray-400">{{ __('sessions.no_data_session') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function predictSession() {
    const btn = document.querySelector('[onclick="predictSession()"]');
    if (btn) { btn.textContent = 'Memproses...'; btn.disabled = true; }

    try {
        const response = await fetch('{{ route('ml.predict-session', $session) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        const data = await response.json();

        const resultDiv = document.getElementById('ml-result');
        const contentDiv = document.getElementById('ml-result-content');
        resultDiv.classList.remove('hidden');

        if (data.success) {
            const condLabels = { fresh: 'Segar', stale: 'Mulai Busuk', rotten: 'Busuk' };
            const condColors = { fresh: 'text-emerald-700', stale: 'text-amber-700', rotten: 'text-red-700' };
            const condBg = { fresh: 'bg-emerald-100 ring-emerald-200', stale: 'bg-amber-100 ring-amber-200', rotten: 'bg-red-100 ring-red-200' };
            const conf = (data.confidence * 100).toFixed(1);
            contentDiv.innerHTML = `
                <div class="flex flex-wrap items-center gap-4 rounded-2xl ${condBg[data.condition] || 'bg-gray-100'} p-5 ring-1 ring-inset">
                    <p class="text-3xl font-extrabold tracking-tight ${condColors[data.condition] || 'text-gray-800'}">
                        ${condLabels[data.condition] || data.condition}
                    </p>
                    <span class="rounded-full bg-white/80 px-3 py-1 text-sm font-semibold text-gray-700">Kepercayaan: <strong>${conf}%</strong></span>
                </div>
                ${data.actual_label ? `<p class="mt-3 text-sm text-gray-500">Label asli: <strong>${data.actual_label_id}</strong></p>` : ''}
                <p class="mt-2 text-xs text-gray-400">Model: ${data.model_version || 'unknown'}</p>
            `;
            resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            contentDiv.innerHTML = `<p class="rounded-2xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-200">${data.message}</p>`;
        }
    } catch (err) {
        console.error(err);
    } finally {
        if (btn) { btn.innerHTML = 'Prediksi ML'; btn.disabled = false; }
    }
}
</script>
@endpush
