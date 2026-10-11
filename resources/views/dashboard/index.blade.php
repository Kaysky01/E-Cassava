@extends('layouts.app')
@section('title', __('dashboard.title'))

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="space-y-6">

    {{-- HEADER + SELECTOR --}}
    <div class="card card-hover relative overflow-hidden p-6 sm:p-7">
        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cassava-100 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="eyebrow text-cassava-600">AI Monitoring</p>
                <h2 class="mt-1 font-display text-xl font-extrabold tracking-tight sm:text-2xl">{{ __('dashboard.title') }}</h2>
                <p class="mt-1 max-w-xl text-[13px] text-gray-500">{{ __('dashboard.subtitle') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <label for="device-select" class="text-sm font-semibold text-gray-500">{{ __('dashboard.device_selector') }}:</label>
                <select id="device-select" onchange="location = this.value"
                    class="input-shell max-w-full !w-auto !py-2.5 !text-sm font-semibold">
                    <option value="{{ route('dashboard', ['range' => $range]) }}" @selected($selectedId === 'all')>{{ __('app.all_warehouses') }}</option>
                    @foreach ($devices as $d)
                        <option value="{{ route('dashboard', ['device' => $d->device_id, 'range' => $range]) }}" @selected($selectedId === $d->device_id)>
                            {{ $d->name }} ({{ $d->device_id }})
                        </option>
                    @endforeach
                </select>
                <a href="{{ route('devices.index') }}" class="btn-ghost !py-2.5 !text-sm">{{ __('devices.add') }}</a>
            </div>
        </div>
    </div>

    @if ($devices->isEmpty())
        <x-empty-state :title="__('dashboard.empty_devices')" :desc="__('dashboard.empty_devices_desc')">
            <a href="{{ route('devices.index') }}" class="btn-primary mt-2">{{ __('dashboard.add_first') }}</a>
        </x-empty-state>
    @else

    {{-- RINGKASAN --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        <x-stat-tile id="sum-online" :label="__('dashboard.summary_online')" :value="$onlineCount" icon="" :sub="$devices->count() . ' total'" />
        <x-stat-tile id="sum-offline" :label="__('dashboard.summary_offline')" :value="$devices->count() - $onlineCount" icon="" />
        <x-stat-tile id="sum-safe" :label="__('dashboard.summary_safe') . ' / ' . __('dashboard.summary_attention')" :value="$safeCount . ' / ' . ($devices->count() - $safeCount)" icon="" />
        <x-stat-tile :label="__('dashboard.summary_sessions')" :value="$totalSessions" icon="" />
        <x-stat-tile id="sum-temp" :label="__('dashboard.machine_temp')" :value="$focusDevice?->latestReading?->temperature !== null ? number_format((float) $focusDevice->latestReading->temperature, 1) . '°C' : __('dashboard.machine_temp_empty')" icon="" :sub="($focusDevice?->name ?? '') . ' • ' . __('dashboard.machine_temp_sub')" />
    </div>

    {{-- KARTU AI UTAMA --}}
    <section class="card relative overflow-hidden" id="ai-card">
        <div class="h-1.5 bg-gradient-to-r from-emerald-400 via-lime-400 to-cassava-500"></div>
        <div class="p-6 sm:p-7">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-cassava-950 text-2xl" aria-hidden="true"></div>
                    <div>
                        <h3 class="font-display font-bold">{{ __('dashboard.ai_title') }}{{ $focusDevice ? ' — ' . $focusDevice->name : '' }}</h3>
                        <p class="mt-0.5 text-xs text-gray-500">{{ __('dashboard.ai_subtitle', ['n' => $windowSize]) }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span id="ai-ml-status" class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $isMlOnline ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $isMlOnline ? 'animate-pulse bg-emerald-500' : 'bg-red-500' }}"></span>
                        {{ $isMlOnline ? __('dashboard.ml_online') : __('dashboard.ml_offline') }}
                    </span>
                    <span id="ai-window-status" class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-600">
                        {{ $focusCount >= $windowSize ? __('dashboard.window_ready', ['c' => $focusCount, 't' => $windowSize]) : __('dashboard.collecting', ['c' => $focusCount, 't' => $windowSize]) }}
                    </span>
                </div>
            </div>

            <div id="ai-body" class="rounded-2xl border border-gray-100 bg-gradient-to-br from-gray-50/80 to-white p-5">
                @if ($focusPrediction)
                    <p id="ai-uncertain-badge" class="{{ $focusPrediction->is_uncertain ? '' : 'hidden ' }}mb-4 items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 {{ $focusPrediction->is_uncertain ? 'inline-flex' : '' }}">{{ __('dashboard.uncertain_badge') }}</p>
                    <div class="flex flex-col items-start gap-5 lg:flex-row lg:items-center">
                        <span id="ai-badge"><x-badge-status :label="$focusPrediction->predicted_label->value" size="lg" /></span>
                        <div id="ai-gauge"><x-gauge :value="($focusPrediction->confidence ?? 0) * 100" :label="__('dashboard.confidence')" /></div>
                        <div class="text-sm text-gray-600">
                            <p id="ai-time">{{ $focusPrediction->predicted_at?->translatedFormat('d M Y H:i:s') }}</p>
                            <p class="mt-1 font-mono text-xs"><span id="ai-dev" class="rounded-md bg-gray-900 px-2 py-0.5 text-lime-300">{{ $focusPrediction->device?->device_id }}</span></p>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        @foreach (['fresh' => ['text-emerald-700', 'from-emerald-400 to-emerald-600'], 'stale' => ['text-amber-700', 'from-amber-400 to-amber-600'], 'rotten' => ['text-red-700', 'from-red-400 to-red-600']] as $key => [$txt, $bar])
                            @php $pct = round(($focusPrediction->probabilities[$key] ?? 0) * 100, 1); @endphp
                            <div class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-sm">
                                <div class="mb-2 flex justify-between text-xs">
                                    <x-badge-status :label="$key" size="sm" />
                                    <span id="ppct-{{ $key }}" class="font-mono font-bold tabular-nums">{{ $pct }}%</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                    <div id="pbar-{{ $key }}" class="h-2.5 rounded-full bg-gradient-to-r {{ $bar }} transition-all duration-700" style="width:{{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p id="ai-uncertain-note" class="{{ $focusPrediction->is_uncertain ? '' : 'hidden ' }}mt-4 text-xs leading-relaxed text-amber-700">{{ __('dashboard.uncertain_note') }}</p>
                @else
                    <p class="text-sm text-gray-500">{{ __('dashboard.no_prediction', ['n' => $windowSize]) }}</p>
                @endif
            </div>
            <p id="ai-offline-note" class="{{ $isMlOnline ? 'hidden' : 'flex' }} mt-4 items-start gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs leading-relaxed text-red-700">
                {{ __('dashboard.ml_offline_note') }}
            </p>
        </div>
    </section>

    {{-- PERBANDINGAN GUDANG --}}
    <section>
        <h3 class="font-display text-lg font-extrabold">{{ __('dashboard.compare_title') }}</h3>
        <p class="mb-3 text-xs text-gray-500">{{ __('dashboard.compare_subtitle') }}</p>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($devices as $device)
                @php $lp = $device->latestPrediction; @endphp
                <a href="{{ route('dashboard', ['device' => $device->device_id, 'range' => $range]) }}" data-device="{{ $device->device_id }}"
                    class="card card-hover block p-5 ring-2 transition {{ $selectedId === $device->device_id ? 'ring-cassava-500' : 'ring-transparent' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-display font-extrabold">{{ $device->name }}</p>
                            <p class="text-xs text-gray-500">{{ $device->location ?? '—' }} • <span class="font-mono">{{ $device->device_id }}</span></p>
                        </div>
                        <span class="cmp-dot h-3 w-3 shrink-0 rounded-full {{ $device->is_online ? 'animate-pulse bg-emerald-500' : 'bg-red-400' }}" title="{{ $device->is_online ? __('app.online') : __('app.offline') }}"></span>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        @if ($lp)
                            <span class="cmp-badge"><x-badge-status :label="$lp->predicted_label->value" size="sm" /></span>
                            <span class="cmp-conf font-mono text-sm font-bold tabular-nums">{{ number_format(($lp->confidence ?? 0) * 100, 1) }}%</span>
                        @else
                            <span class="cmp-collect text-xs text-gray-400">{{ __('dashboard.collecting', ['c' => $device->sensor_readings_count, 't' => $windowSize]) }}</span>
                        @endif
                    </div>
                    <p class="cmp-temp mt-2 font-mono text-xs tabular-nums text-gray-500">
                        @if ($device->latestReading?->temperature !== null)
                            {{ __('dashboard.machine_temp') }}: <span class="font-bold text-red-600">{{ number_format((float) $device->latestReading->temperature, 1) }}°C</span>
                        @else
                            {{ __('dashboard.machine_temp') }}: <span class="text-gray-400">{{ __('dashboard.machine_temp_empty') }}</span>
                        @endif
                    </p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- GRAFIK --}}
    <div class="grid gap-4 xl:grid-cols-3">
        <section class="card p-6 sm:p-7 xl:col-span-2">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-display font-bold">{{ __('dashboard.chart_title') }}{{ $chartDevice ? ' — ' . $chartDevice->name : '' }}</h3>
                    <p class="mt-0.5 text-xs text-gray-500">{{ __('dashboard.chart_subtitle', ['n' => $chartData->count()]) }}</p>
                    <p class="mt-1.5 flex flex-wrap items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 font-bold text-emerald-700">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>LIVE
                        </span>
                        <span id="chart-now" class="font-mono font-semibold tabular-nums text-gray-600">{{ $chartNowText ?? '' }}</span>
                    </p>
                </div>
                <div class="flex gap-1 rounded-2xl bg-gray-100 p-1 text-xs font-bold" role="group" aria-label="range">
                    @foreach (['1h' => __('dashboard.range_1h'), '24h' => __('dashboard.range_24h'), '7d' => __('dashboard.range_7d')] as $r => $lbl)
                        <a href="{{ route('dashboard', ['device' => $selectedId === 'all' ? null : $selectedId, 'range' => $r]) }}"
                            @class(['rounded-xl px-3 py-1.5 transition', 'bg-white shadow' => $range === $r, 'text-gray-500 hover:text-gray-800' => $range !== $r])>{{ $lbl }}</a>
                    @endforeach
                </div>
            </div>
            <div class="relative h-60 rounded-2xl border border-gray-100 bg-gradient-to-b from-gray-50/60 to-white p-3 sm:h-[280px]">
                <canvas id="sensor-chart"></canvas>
            </div>
            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1.5 font-semibold text-orange-700"><span class="h-2 w-2 rounded-full bg-orange-500"></span>MQ-2</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-3 py-1.5 font-semibold text-sky-700"><span class="h-2 w-2 rounded-full bg-sky-500"></span>MQ-3</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-3 py-1.5 font-semibold text-violet-700"><span class="h-2 w-2 rounded-full bg-violet-500"></span>MQ-135</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 font-semibold text-red-700"><span class="h-2 w-2 rounded-full bg-red-500"></span>{{ __('dashboard.temp_chart_label') }}</span>
            </div>
        </section>

        <section class="card p-6 sm:p-7">
            <h3 class="font-display font-bold">{{ __('dashboard.timeline_title') }}</h3>
            <p class="mb-4 mt-0.5 text-xs text-gray-500">{{ __('dashboard.timeline_subtitle') }}</p>
            <div class="relative h-60 sm:h-[280px]">
                <canvas id="timeline-chart"></canvas>
            </div>
            <div class="mt-3 space-y-1 text-xs text-gray-500">
                <p class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>0 = {{ __('app.fresh') }}</p>
                <p class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-amber-400"></span>1 = {{ __('app.stale') }}</p>
                <p class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-red-500"></span>2 = {{ __('app.rotten') }}</p>
            </div>
        </section>
    </div>

    {{-- INSIGHT --}}
    <section>
        <h3 class="font-display text-lg font-extrabold">{{ __('dashboard.insights_title') }}</h3>
        <p class="mb-3 text-xs text-gray-500">{{ __('dashboard.insights_subtitle') }}</p>
        <div class="grid gap-3 md:grid-cols-2">
            @foreach ($allInsights as $insight)
                @php
                    $ring = $insight['level'] === 'high' ? 'border-red-200' : ($insight['level'] === 'medium' ? 'border-amber-200' : 'border-emerald-200');
                    $urg = __('insights.urgency_' . $insight['level']);
                @endphp
                <div class="card flex gap-3 border p-5 {{ $ring }}">
                    <span class="mt-1.5 h-3 w-3 shrink-0 rounded-full {{ $insight['level'] === 'high' ? 'bg-red-500' : ($insight['level'] === 'medium' ? 'bg-amber-400' : 'bg-emerald-500') }}" aria-hidden="true"></span>
                    <div>
                        <p class="font-bold">{{ $insight['title'] }}
                            <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold uppercase text-gray-600">{{ $urg }}</span>
                        </p>
                        <p class="mt-1 text-sm text-gray-600">{{ $insight['desc'] }}</p>
                        @if (!empty($insight['device']))
                            <p class="mt-1 font-mono text-xs text-gray-400">{{ $insight['device'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- RIWAYAT --}}
    <div class="grid gap-4 xl:grid-cols-2">
        <section>
            <h3 class="mb-3 font-display text-lg font-extrabold">{{ __('dashboard.history_sensor') }}</h3>
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table-shell min-w-[620px] text-sm">
                        <thead><tr class="border-b border-gray-100 bg-gray-50/80">
                            <th>Waktu</th><th>Perangkat</th><th class="!text-right">MQ-2</th><th class="!text-right">MQ-3</th><th class="!text-right">MQ-135</th><th class="!text-right">{{ __('dashboard.temp_column') }}</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($historyReadings as $reading)
                                <tr class="transition-colors hover:bg-cassava-50/50">
                                    <td class="whitespace-nowrap px-5 py-3 font-mono text-xs tabular-nums text-gray-600">{{ $reading->recorded_at?->translatedFormat('d M H:i:s') }}</td>
                                    <td class="px-5 py-3"><span class="rounded-lg border border-cassava-200 bg-cassava-50 px-2.5 py-1 font-mono text-xs font-bold text-cassava-800">{{ $reading->device?->device_id }}</span></td>
                                    <td class="px-5 py-3 text-right font-mono font-bold tabular-nums text-orange-600">{{ $reading->mq2 }}</td>
                                    <td class="px-5 py-3 text-right font-mono font-bold tabular-nums text-sky-600">{{ $reading->mq3 }}</td>
                                    <td class="px-5 py-3 text-right font-mono font-bold tabular-nums text-violet-600">{{ $reading->mq135 }}</td>
                                    <td class="px-5 py-3 text-right font-mono font-bold tabular-nums text-red-600">{{ $reading->temperature !== null ? number_format((float) $reading->temperature, 1) . '°C' : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">{{ __('app.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($historyReadings->hasPages())
                    <div class="border-t border-gray-100 bg-gray-50/60 px-5 py-4">{{ $historyReadings->links() }}</div>
                @endif
            </div>
        </section>

        <section>
            <h3 class="mb-3 font-display text-lg font-extrabold">{{ __('dashboard.history_prediction') }}</h3>
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table-shell min-w-[540px] text-sm">
                        <thead><tr class="border-b border-gray-100 bg-gray-50/80">
                            <th>Waktu</th><th>Perangkat</th><th>Label</th><th class="!text-right">Conf.</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($predictionHistory as $prediction)
                                <tr class="transition-colors hover:bg-cassava-50/50">
                                    <td class="whitespace-nowrap px-5 py-3 font-mono text-xs tabular-nums text-gray-600">{{ $prediction->predicted_at?->translatedFormat('d M H:i:s') }}</td>
                                    <td class="px-5 py-3"><span class="rounded-lg border border-cassava-200 bg-cassava-50 px-2.5 py-1 font-mono text-xs font-bold text-cassava-800">{{ $prediction->device?->device_id }}</span></td>
                                    <td class="px-5 py-3"><x-badge-status :label="$prediction->predicted_label->value" size="sm" /></td>
                                    <td class="px-5 py-3 text-right font-mono font-bold tabular-nums">{{ number_format(($prediction->confidence ?? 0) * 100, 1) }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">{{ __('app.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($predictionHistory->hasPages())
                    <div class="border-t border-gray-100 bg-gray-50/60 px-5 py-4">{{ $predictionHistory->links() }}</div>
                @endif
            </div>
        </section>
    </div>

    @endif
</div>
@endsection

@push('scripts')
<script>
const chartLabels = @json($chartData->pluck('label'));
const chartMq2 = @json($chartData->pluck('mq2'));
const chartMq3 = @json($chartData->pluck('mq3'));
const chartMq135 = @json($chartData->pluck('mq135'));
const chartTemp = @json($chartData->pluck('temperature'));
const timelineLabels = @json($timeline->pluck('label'));
const timelineValues = @json($timeline->pluck('value'));
const pollDevice = @json($selectedId);
const chartDeviceId = @json($chartDevice?->device_id);
const chartRange = @json($range);
const windowTarget = @json($windowSize);
const T = {
    collecting: @json(__('dashboard.collecting', ['c' => '__C__', 't' => '__T__'])),
    windowReady: @json(__('dashboard.window_ready', ['c' => '__C__', 't' => '__T__'])),
    mlOnline: @json(__('dashboard.ml_online')),
    mlOffline: @json(__('dashboard.ml_offline')),
    online: @json(__('app.online')),
    offline: @json(__('app.offline')),
    badgeText: { fresh: @json(__('app.fresh')), stale: @json(__('app.stale')), rotten: @json(__('app.rotten')) },
    tempLabel: @json(__('dashboard.temp_chart_label')),
    tempEmpty: @json(__('dashboard.machine_temp_empty')),
    tempPrefix: @json(__('dashboard.machine_temp')),
};
const BADGE_CLS = {
    fresh: 'bg-emerald-100 text-emerald-800 ring-emerald-200',
    stale: 'bg-amber-100 text-amber-800 ring-amber-200',
    rotten: 'bg-red-100 text-red-800 ring-red-200',
};
let sensorChart = null, timelineChart = null;

function timelineColor(v) {
    return v >= 2 ? '#dc2626' : (v >= 1 ? '#f59e0b' : '#16a34a');
}

function badgeHTML(label, size) {
    const pad = size === 'lg' ? 'px-5 py-2.5 text-base' : 'px-3 py-1.5 text-xs';
    const key = BADGE_CLS[label] ? label : 'fresh';
    return `<span class="inline-flex items-center rounded-full font-bold ring-1 ring-inset ${BADGE_CLS[key]} ${pad}">${T.badgeText[key]}</span>`;
}
function setTile(id, text) {
    const el = document.getElementById(id);
    if (el) { const v = el.querySelector('.font-display'); if (v) v.textContent = text; }
}
function setGauge(pct) {
    const box = document.getElementById('ai-gauge');
    if (!box) return;
    const p = Math.max(0, Math.min(100, pct));
    const circ = 2 * Math.PI * 54;
    const circle = box.querySelectorAll('circle')[1];
    const num = box.querySelector('.font-display');
    if (circle) {
        circle.setAttribute('stroke-dashoffset', String(circ * (1 - p / 100)));
        circle.setAttribute('stroke', p >= 70 ? '#16a34a' : (p >= 40 ? '#f59e0b' : '#ef4444'));
    }
    if (num) num.textContent = (Math.round(p * 10) / 10) + '%';
}

(function initCharts() {
    const dark = document.documentElement.classList.contains('dark');
    const grid = dark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.05)';
    const tick = dark ? '#9ca3af' : '#6b7280';

    const sc = document.getElementById('sensor-chart');
    if (sc) {
        sensorChart = new Chart(sc, {
            type: 'line',
            data: { labels: chartLabels, datasets: [
                { label: 'MQ-2', data: chartMq2, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.10)', borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 4, tension: 0.35, fill: true, yAxisID: 'y' },
                { label: 'MQ-3', data: chartMq3, borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,0.10)', borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 4, tension: 0.35, fill: true, yAxisID: 'y' },
                { label: 'MQ-135', data: chartMq135, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.10)', borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 4, tension: 0.35, fill: true, yAxisID: 'y' },
                { label: T.tempLabel, data: chartTemp, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.08)', borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 4, tension: 0.35, fill: false, borderDash: [6, 4], yAxisID: 'y1', spanGaps: true },
            ]},
            options: { responsive: true, maintainAspectRatio: false, animation: { duration: 300 },
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { maxTicksLimit: 8, font: { size: 10 }, color: tick }, grid: { display: false } },
                    y: { type: 'linear', display: true, position: 'left', min: 0, max: 4095, ticks: { font: { size: 10 }, color: tick }, grid: { color: grid } },
                    y1: { type: 'linear', display: true, position: 'right', ticks: { font: { size: 10 }, color: '#ef4444' }, grid: { drawOnChartArea: false } },
                } },
        });
    }

    const tc = document.getElementById('timeline-chart');
    if (tc) {
        timelineChart = new Chart(tc, {
            type: 'line',
            data: { labels: timelineLabels, datasets: [{
                label: 'Label', data: timelineValues, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,0.12)',
                borderWidth: 2.5, pointRadius: 3.5, pointBackgroundColor: timelineValues.map(timelineColor),
                tension: 0.25, fill: true, stepped: true,
            }]},
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { maxTicksLimit: 6, font: { size: 10 }, color: tick }, grid: { display: false } },
                    y: { min: -0.2, max: 2.2, ticks: { stepSize: 1, font: { size: 10 }, color: tick }, grid: { color: grid } },
                } },
        });
    }
})();

function fmtWin(c, t, ready) {
    return (ready ? T.windowReady : T.collecting).replace('__C__', c).replace('__T__', t);
}

async function pollOverview() {
    try {
        const url = new URL("{{ route('dashboard.api.overview') }}", location.origin);
        if (pollDevice && pollDevice !== 'all') url.searchParams.set('device_id', pollDevice);
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;
        const json = await res.json();
        if (!json.success || !json.devices) return;

        // ── Status ML ──
        const ml = document.getElementById('ai-ml-status');
        if (ml) {
            const on = !!json.ml_service_online;
            ml.className = 'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold ' +
                (on ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700');
            ml.innerHTML = `<span class="h-1.5 w-1.5 rounded-full ${on ? 'animate-pulse bg-emerald-500' : 'bg-red-500'}"></span>` + (on ? T.mlOnline : T.mlOffline);
        }
        const note = document.getElementById('ai-offline-note');
        if (note) note.classList.toggle('hidden', !!json.ml_service_online);

        // ── Ringkasan ──
        if (json.summary) {
            setTile('sum-online', String(json.summary.online));
            setTile('sum-offline', String(json.summary.offline));
            const safe = json.devices.filter(d => (d.prediction?.label ?? 'fresh') === 'fresh').length;
            setTile('sum-safe', `${safe} / ${json.devices.length - safe}`);
        }

        function fmtTemp(v) {
            return (v === null || v === undefined) ? T.tempEmpty : (Math.round(v * 10) / 10) + '°C';
        }

        // ── Kartu AI fokus ──
        const focus = json.devices.find(d => d.device_id === json.focus_device_id) ?? json.devices[0];
        if (focus) {
            const w = focus.window ?? { current: 0, target: windowTarget, ready: false };
            const pill = document.getElementById('ai-window-status');
            if (pill) pill.textContent = fmtWin(w.current, w.target, w.ready);
            const dev = document.getElementById('ai-dev');
            if (dev) dev.textContent = focus.device_id;
            setTile('sum-temp', fmtTemp(focus.latest?.temperature));
            if (focus.prediction) {
                const p = focus.prediction;
                const badge = document.getElementById('ai-badge');
                if (badge) badge.innerHTML = badgeHTML(p.label, 'lg');
                setGauge(p.confidence_percent ?? 0);
                const probs = p.probabilities ?? {};
                ['fresh', 'stale', 'rotten'].forEach(k => {
                    const pct = Math.round(((probs[k] ?? 0) * 100) * 10) / 10;
                    const bar = document.getElementById('pbar-' + k);
                    const num = document.getElementById('ppct-' + k);
                    if (bar) bar.style.width = pct + '%';
                    if (num) num.textContent = pct + '%';
                });
                const ub = document.getElementById('ai-uncertain-badge');
                if (ub) { ub.classList.toggle('hidden', !p.uncertain); ub.classList.toggle('inline-flex', !!p.uncertain); }
                const un = document.getElementById('ai-uncertain-note');
                if (un) un.classList.toggle('hidden', !p.uncertain);
                const tm = document.getElementById('ai-time');
                if (tm && p.predicted_at) tm.textContent = '' + new Date(p.predicted_at).toLocaleString();
            }
        }

        // ── Perbandingan gudang ──
        json.devices.forEach(d => {
            const tile = document.querySelector(`[data-device="${d.device_id}"]`);
            if (!tile) return;
            const dot = tile.querySelector('.cmp-dot');
            if (dot) {
                dot.className = 'cmp-dot h-3 w-3 shrink-0 rounded-full ' + (d.is_online ? 'animate-pulse bg-emerald-500' : 'bg-red-400');
                dot.title = d.is_online ? T.online : T.offline;
            }
            const tempEl = tile.querySelector('.cmp-temp');
            if (tempEl) tempEl.innerHTML = `${T.tempPrefix}: ` + (d.latest?.temperature !== null && d.latest?.temperature !== undefined
                ? `<span class="font-bold text-red-600">${fmtTemp(d.latest.temperature)}</span>`
                : `<span class="text-gray-400">${T.tempEmpty}</span>`);
            const w = d.window ?? { current: 0, target: windowTarget };
            if (d.prediction) {
                const b = tile.querySelector('.cmp-badge');
                if (b) b.innerHTML = badgeHTML(d.prediction.label, 'sm');
                const c = tile.querySelector('.cmp-conf');
                if (c) c.textContent = (d.prediction.confidence_percent ?? 0) + '%';
                const old = tile.querySelector('.cmp-collect');
                if (old) old.remove();
            } else {
                const col = tile.querySelector('.cmp-collect');
                if (col) col.textContent = fmtWin(w.current, w.target, false);
            }
        });

        // ── Timeline ──
        if (timelineChart && json.timeline) {
            const vals = json.timeline.map(t => t.value);
            timelineChart.data.labels = json.timeline.map(t => t.label);
            timelineChart.data.datasets[0].data = vals;
            timelineChart.data.datasets[0].pointBackgroundColor = vals.map(timelineColor);
            timelineChart.update('none');
        }
    } catch (e) { console.warn('[E-CASSAVA] overview polling error:', e); }
}

async function pollSensorChart() {
    if (!sensorChart || !chartDeviceId) return;
    try {
        const url = new URL("{{ route('dashboard.api.sensor') }}", location.origin);
        url.searchParams.set('device_id', chartDeviceId);
        url.searchParams.set('range', chartRange);
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;
        const json = await res.json();
        if (!json.success || !json.data) return;
        sensorChart.data.labels = json.data.map(r => r.label);
        sensorChart.data.datasets[0].data = json.data.map(r => r.mq2);
        sensorChart.data.datasets[1].data = json.data.map(r => r.mq3);
        sensorChart.data.datasets[2].data = json.data.map(r => r.mq135);
        if (sensorChart.data.datasets[3]) sensorChart.data.datasets[3].data = json.data.map(r => r.temperature);
        sensorChart.update('none');
        const last = json.data[json.data.length - 1];
        const now = document.getElementById('chart-now');
        if (last && now) now.textContent = `M2:${last.mq2} M3:${last.mq3} M5:${last.mq135}` + (last.temperature !== null && last.temperature !== undefined ? ` • ${(Math.round(last.temperature * 10) / 10)}°C` : '');
    } catch (e) { console.warn('[E-CASSAVA] sensor polling error:', e); }
}

function liveTick() {
    // Hemat baterai/CPU: tab tidak terlihat → lewati putaran ini.
    if (!document.hidden) {
        pollOverview();
        pollSensorChart();
    }
    setTimeout(liveTick, 4000);
}
setTimeout(liveTick, 4000);
</script>
@endpush
