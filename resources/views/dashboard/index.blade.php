@extends('layouts.app')
@section('title', __('dashboard.title'))

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="space-y-5 sm:space-y-6">

    {{-- ════════ HERO ════════ --}}
    <section class="relative overflow-hidden rounded-[24px] shadow-[0_18px_50px_-20px_rgba(20,40,15,0.6)]">
        <img src="{{ asset('images/hero-bg.jpg') }}"
            onerror="this.onerror=null;this.src='{{ asset('images/login-bg.jpg') }}';"
            alt="" aria-hidden="true"
            class="absolute inset-0 h-full w-full object-cover object-[center_35%]" />
        <div class="absolute inset-0 bg-gradient-to-r from-[#0e220d]/95 via-[#1d4420]/85 to-[#2f6b26]/55"></div>
        <div class="relative flex flex-col gap-5 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/30 px-3.5 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.18em] text-white/90">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>AI Monitoring
                </span>
                <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ __('dashboard.title') }}</h2>
                <p class="mt-1.5 max-w-xl text-[13px] leading-relaxed text-white/70">{{ __('dashboard.subtitle') }}</p>
            </div>
            <div class="flex flex-col gap-2.5 sm:flex-row sm:flex-wrap sm:items-center">
                <label for="device-select" class="whitespace-nowrap text-[13px] font-semibold text-white/80">{{ __('dashboard.device_selector') }}:</label>
                <div class="relative min-w-0">
                    <select id="device-select" onchange="location = this.value"
                        class="w-full appearance-none rounded-full bg-white/15 py-2.5 pl-4 pr-10 text-sm font-semibold text-white ring-1 ring-white/30 backdrop-blur transition hover:bg-white/20 focus:outline-none sm:w-auto [&>option]:text-gray-900">
                        <option value="{{ route('dashboard', ['range' => $range]) }}" @selected($selectedId === 'all')>{{ __('app.all_warehouses') }}</option>
                        @foreach ($devices as $d)
                            <option value="{{ route('dashboard', ['device' => $d->device_id, 'range' => $range]) }}" @selected($selectedId === $d->device_id)>
                                {{ $d->name }} ({{ $d->device_id }})
                            </option>
                        @endforeach
                    </select>
                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6" /></svg>
                </div>
                <a href="{{ route('devices.index') }}" class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-full bg-white px-5 py-2.5 text-sm font-bold text-[#1e3a1a] shadow transition hover:bg-emerald-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14" /></svg>
                    {{ __('devices.add') }}
                </a>
            </div>
        </div>
    </section>

    @if ($devices->isEmpty())
        <x-empty-state :title="__('dashboard.empty_devices')" :desc="__('dashboard.empty_devices_desc')">
            <a href="{{ route('devices.index') }}" class="btn-primary mt-2">{{ __('dashboard.add_first') }}</a>
        </x-empty-state>
    @else
    @php
        $offlineCount = $devices->count() - $onlineCount;
        $attentionCount = $devices->count() - $safeCount;
        $offlineDevice = $devices->firstWhere('is_online', false);
        $focusTemp = $focusDevice?->latestReading?->temperature;
    @endphp

    {{-- ════════ STATISTIK ════════ --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <div id="sum-online" class="min-w-0 rounded-2xl border-t-4 border-emerald-500 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <p class="break-words text-[11px] font-extrabold uppercase tracking-wider text-gray-500">{{ __('dashboard.summary_online') }}</p>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M2 9a15 15 0 0 1 20 0M5.5 12.5a10 10 0 0 1 13 0M9 16a5 5 0 0 1 6 0" /><circle cx="12" cy="19.5" r="1.2" fill="currentColor" stroke="none" /></svg>
                </span>
            </div>
            <div class="mt-1 flex items-end justify-between gap-2">
                <p class="font-display text-3xl font-extrabold tabular-nums text-emerald-600">{{ $onlineCount }}</p>
                <svg viewBox="0 0 60 24" class="h-6 w-14 shrink-0" fill="none" aria-hidden="true"><polyline points="0,18 10,16 20,17 30,10 40,12 50,5 60,7" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </div>
            <p class="mt-1 text-xs text-gray-500">{{ $devices->count() }} {{ __('dashboard.total_registered') }}</p>
        </div>

        <div id="sum-offline" class="min-w-0 rounded-2xl border-t-4 border-red-500 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <p class="break-words text-[11px] font-extrabold uppercase tracking-wider text-gray-500">{{ __('dashboard.summary_offline') }}</p>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 16.5a5 5 0 0 1 7 0M5 13a10 10 0 0 1 14 0M2 9.5a15 15 0 0 1 20 0" /><circle cx="12" cy="19.5" r="1.2" fill="currentColor" stroke="none" /><line x1="3" y1="3" x2="21" y2="21" /></svg>
                </span>
            </div>
            <div class="mt-1 flex items-center justify-between gap-2">
                <p class="font-display text-3xl font-extrabold tabular-nums text-red-600">{{ $offlineCount }}</p>
                <span class="shrink-0 rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-bold text-red-500">{{ __('app.offline') }}</span>
            </div>
            <p class="mt-1 truncate font-mono text-xs text-gray-500">{{ $offlineDevice?->device_id ?? '—' }}</p>
        </div>

        <div id="sum-safe" class="min-w-0 rounded-2xl border-t-4 border-amber-400 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <p class="break-words text-[11px] font-extrabold uppercase tracking-wider text-gray-500">{{ __('dashboard.summary_safe') }}</p>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11 12 4l9 7" /><path d="M5 10v10h14V10" /></svg>
                </span>
            </div>
            <p class="mt-1 font-display text-3xl font-extrabold tabular-nums text-amber-500">{{ $safeCount }} <span class="text-gray-300">/</span> {{ $attentionCount }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ __('dashboard.warehouse_active') }}</p>
        </div>

        <div id="sum-temp" class="min-w-0 rounded-2xl border-t-4 border-orange-400 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <p class="break-words text-[11px] font-extrabold uppercase tracking-wider text-gray-500">{{ __('dashboard.machine_temp') }}</p>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-orange-50 text-orange-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4a2 2 0 0 1 2 2v7.3a4 4 0 1 1-4 0V6a2 2 0 0 1 2-2z" /><circle cx="12" cy="17.5" r="1.6" fill="currentColor" stroke="none" /></svg>
                </span>
            </div>
            <div class="mt-1 flex items-end justify-between gap-2">
                <p class="font-display text-3xl font-extrabold tabular-nums text-gray-800">{{ $focusTemp !== null ? number_format((float) $focusTemp, 1) . '°C' : __('dashboard.machine_temp_empty') }}</p>
                <svg viewBox="0 0 60 24" class="h-6 w-14 shrink-0" fill="none" aria-hidden="true"><polyline points="0,12 15,12 25,11 35,13 45,12 60,12" stroke="#d1d5db" stroke-width="2" stroke-linecap="round" stroke-dasharray="3 3" /></svg>
            </div>
            <p class="mt-1 truncate font-mono text-xs text-gray-500">{{ $focusDevice?->device_id ?? '—' }} • {{ __('dashboard.machine_temp_sub') }}</p>
        </div>
    </div>

    {{-- ════════ KARTU AI UTAMA ════════ --}}
    <section class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-black/5" id="ai-card">
        <div class="h-1.5 bg-gradient-to-r from-emerald-500 via-amber-400 to-red-500"></div>
        <div class="p-4 sm:p-6 lg:p-7">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#e9f1dc] text-[#2f5d2a]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3" /><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2" /></svg>
                    </span>
                    <div class="min-w-0">
                        <h3 class="truncate font-display text-[17px] font-extrabold text-[#1e3a1a]">{{ __('dashboard.ai_title') }}{{ $focusDevice ? ' — ' . $focusDevice->device_id : '' }}</h3>
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

            <div id="ai-body" class="mt-4 rounded-2xl bg-[#fbf9ef] p-4 ring-1 ring-[#ece7d2] sm:p-6">
                @if ($focusPrediction)
                    @php
                        $confPct = round(($focusPrediction->confidence ?? 0) * 100, 1);
                        $level = $confPct >= 70 ? [__('dashboard.conf_high'), 'text-emerald-600'] : ($confPct >= 40 ? [__('dashboard.conf_mid'), 'text-amber-600'] : [__('dashboard.conf_low'), 'text-red-600']);
                    @endphp
                    <div class="flex flex-col gap-3 min-[560px]:flex-row min-[560px]:items-center min-[560px]:justify-between">
                        <p id="ai-uncertain-badge" class="{{ $focusPrediction->is_uncertain ? 'inline-flex' : 'hidden' }} items-center gap-1.5 self-start rounded-full bg-amber-100/80 px-3 py-1.5 text-xs font-semibold text-amber-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z" /></svg>
                            {{ __('dashboard.uncertain_badge') }}
                        </p>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-gray-500">
                            <p id="ai-time" class="tabular-nums">{{ $focusPrediction->predicted_at?->translatedFormat('d/m/Y, H:i:s') }}</p>
                            <p class="font-mono"><span id="ai-dev" class="rounded-md bg-[#2f5d2a] px-2 py-1 text-[11px] font-bold text-white">{{ $focusPrediction->device?->device_id }}</span></p>
                        </div>
                    </div>
                    <div class="mt-5 flex flex-col items-center gap-5 text-center sm:flex-row sm:gap-7 sm:text-left">
                        <span id="ai-badge"><x-badge-status :label="$focusPrediction->predicted_label->value" size="lg" /></span>
                        <div id="ai-gauge"><x-gauge :value="$confPct" /></div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-gray-400">{{ __('dashboard.conf_level') }}</p>
                            <p id="ai-level" class="mt-1 font-display text-xl font-extrabold {{ $level[1] }}">{{ $level[0] }}</p>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-1 gap-3 min-[560px]:grid-cols-3">
                        @foreach (['fresh' => ['#16a34a', 'text-emerald-700'], 'stale' => ['#f59e0b', 'text-amber-600'], 'rotten' => ['#f43f5e', 'text-rose-600']] as $key => [$barColor, $txtColor])
                            @php $pct = round(($focusPrediction->probabilities[$key] ?? 0) * 100, 1); @endphp
                            <div class="rounded-2xl bg-white p-3.5 shadow-sm ring-1 ring-black/5">
                                <div class="mb-2 flex items-center justify-between gap-2 text-xs">
                                    <x-badge-status :label="$key" size="sm" />
                                    <span id="ppct-{{ $key }}" class="font-mono font-bold tabular-nums {{ $txtColor }}">{{ number_format($pct, 1) }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                    <div id="pbar-{{ $key }}" class="h-2 rounded-full transition-all duration-700" style="width:{{ $pct }}%;background:{{ $barColor }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p id="ai-uncertain-note" class="{{ $focusPrediction->is_uncertain ? '' : 'hidden ' }}mt-4 flex items-start gap-2 rounded-2xl bg-amber-50 p-4 text-xs leading-relaxed text-amber-800 ring-1 ring-amber-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z" /><circle cx="12" cy="10" r="2.5" /></svg>
                        {{ __('dashboard.uncertain_note') }}
                    </p>
                @else
                    <p class="text-sm text-gray-500">{{ __('dashboard.no_prediction', ['n' => $windowSize]) }}</p>
                @endif
            </div>
            <p id="ai-offline-note" class="{{ $isMlOnline ? 'hidden' : 'flex' }} mt-4 items-start gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs leading-relaxed text-red-700">
                {{ __('dashboard.ml_offline_note') }}
            </p>
        </div>
    </section>

    {{-- ════════ PERBANDINGAN GUDANG ════════ --}}
    <section>
        <h3 class="font-display text-lg font-extrabold text-[#1e3a1a]">{{ __('dashboard.compare_title') }}</h3>
        <p class="mb-3 mt-0.5 text-xs text-gray-500">{{ __('dashboard.compare_subtitle') }}</p>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($devices as $device)
                @php $lp = $device->latestPrediction; @endphp
                <a href="{{ route('dashboard', ['device' => $device->device_id, 'range' => $range]) }}" data-device="{{ $device->device_id }}"
                    class="block min-w-0 rounded-2xl border-l-4 border-emerald-500 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $selectedId === $device->device_id ? 'ring-2 ring-[#2f5d2a]' : 'ring-1 ring-black/5' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="flex items-center gap-1.5 font-display text-[15px] font-extrabold text-[#1e3a1a]">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500"></span>
                                <span class="truncate">{{ $device->device_id }}</span>
                            </p>
                            <p class="mt-1 truncate text-xs text-gray-500">{{ $device->location ?? '—' }} • <span class="font-mono">{{ $device->device_id }}</span></p>
                        </div>
                        <span class="cmp-dot h-3 w-3 shrink-0 rounded-full {{ $device->is_online ? 'animate-pulse bg-emerald-500' : 'bg-red-500' }}" title="{{ $device->is_online ? __('app.online') : __('app.offline') }}"></span>
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-2 border-t border-gray-100 pt-4">
                        @if ($lp)
                            <span class="cmp-badge"><x-badge-status :label="$lp->predicted_label->value" size="sm" /></span>
                            <span class="cmp-conf font-mono text-sm font-bold tabular-nums text-emerald-700">{{ number_format(($lp->confidence ?? 0) * 100, 1) }}%</span>
                        @else
                            <span class="cmp-collect text-xs text-gray-400">{{ __('dashboard.collecting', ['c' => $device->sensor_readings_count, 't' => $windowSize]) }}</span>
                        @endif
                    </div>
                    <p class="cmp-temp mt-2 text-xs text-gray-500">
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

    {{-- ════════ GRAFIK + TIMELINE ════════ --}}
    @php $lastChart = $chartData->last(); @endphp
    <div class="grid gap-4 xl:grid-cols-3">
        <section class="min-w-0 rounded-3xl bg-white p-4 shadow-sm ring-1 ring-black/5 sm:p-6 xl:col-span-2">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <h3 class="font-display text-[16px] font-extrabold text-[#1e3a1a]">{{ __('dashboard.chart_title') }}{{ $chartDevice ? ' — ' . $chartDevice->device_id : '' }}</h3>
                    <p class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1.5 text-xs text-gray-500">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 font-bold text-emerald-700 ring-1 ring-emerald-100">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>LIVE
                        </span>
                        <span id="chart-now" class="hidden font-mono font-semibold tabular-nums">{{ $chartNowText ?? '' }}</span>
                        <span>{{ __('dashboard.chart_subtitle', ['n' => $chartData->count()]) }}</span>
                    </p>
                </div>
                <div class="flex shrink-0 gap-1 self-start rounded-full bg-gray-100 p-1 text-xs font-bold" role="group" aria-label="range">
                    @foreach (['1h' => __('dashboard.range_1h'), '24h' => __('dashboard.range_24h'), '7d' => __('dashboard.range_7d')] as $r => $lbl)
                        <a href="{{ route('dashboard', ['device' => $selectedId === 'all' ? null : $selectedId, 'range' => $r]) }}"
                            @class(['whitespace-nowrap rounded-full px-3.5 py-1.5 transition', 'bg-[#2f5d2a] text-white shadow' => $range === $r, 'text-gray-500 hover:text-gray-800' => $range !== $r])>{{ $lbl }}</a>
                    @endforeach
                </div>
            </div>
            <div class="relative mt-4 h-60 rounded-2xl bg-gradient-to-b from-gray-50/60 to-white p-2 ring-1 ring-gray-100 sm:h-[280px]">
                <canvas id="sensor-chart"></canvas>
            </div>
            <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1.5 text-orange-700 ring-1 ring-orange-100"><span class="h-2 w-2 rounded-full bg-orange-500"></span>MQ-2: <span id="leg-mq2" class="font-bold tabular-nums">{{ $lastChart['mq2'] ?? '—' }}</span></span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-3 py-1.5 text-sky-700 ring-1 ring-sky-100"><span class="h-2 w-2 rounded-full bg-sky-500"></span>MQ-3: <span id="leg-mq3" class="font-bold tabular-nums">{{ $lastChart['mq3'] ?? '—' }}</span></span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-3 py-1.5 text-violet-700 ring-1 ring-violet-100"><span class="h-2 w-2 rounded-full bg-violet-500"></span>MQ-135: <span id="leg-mq135" class="font-bold tabular-nums">{{ $lastChart['mq135'] ?? '—' }}</span></span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-red-700 ring-1 ring-red-100"><span class="h-2 w-2 rounded-full bg-red-500"></span>{{ __('dashboard.machine_temp') }}: <span id="leg-temp" class="font-bold tabular-nums">{{ isset($lastChart['temperature']) && $lastChart['temperature'] !== null ? number_format((float) $lastChart['temperature'], 1) . '°C' : '—' }}</span></span>
            </div>
        </section>

        <section class="min-w-0 rounded-3xl bg-white p-4 shadow-sm ring-1 ring-black/5 sm:p-6">
            <h3 class="font-display text-[16px] font-extrabold text-[#1e3a1a]">{{ __('dashboard.timeline_title') }}</h3>
            <p class="mb-4 mt-1 text-xs text-gray-500">{{ __('dashboard.timeline_subtitle') }}</p>
            <div class="flex flex-wrap gap-1.5 text-[11px] font-bold">
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700 ring-1 ring-emerald-100"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ __('app.fresh') }}</span>
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-amber-700 ring-1 ring-amber-100"><span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>{{ __('app.stale') }}</span>
                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-red-700 ring-1 ring-red-100"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>{{ __('app.rotten') }}</span>
            </div>
            @php $recentPreds = collect($predictionHistory->items())->take(4); @endphp
            <ul class="mt-4 space-y-3.5">
                @forelse ($recentPreds as $prediction)
                    <li class="flex items-center gap-2.5 text-sm">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $prediction->predicted_label->value === 'fresh' ? 'bg-emerald-600' : ($prediction->predicted_label->value === 'stale' ? 'bg-amber-500' : 'bg-red-500') }}"></span>
                        <span class="shrink-0 font-mono text-xs tabular-nums text-gray-500">{{ $prediction->predicted_at?->format('H:i:s') }}</span>
                        <span class="min-w-0 flex-1"><x-badge-status :label="$prediction->predicted_label->value" size="sm" /></span>
                        <span class="shrink-0 font-mono text-xs font-bold tabular-nums text-emerald-700">{{ number_format(($prediction->confidence ?? 0) * 100, 1) }}%</span>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-gray-400">{{ __('app.no_data') }}</li>
                @endforelse
            </ul>
            <div class="mt-5 flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-3 text-xs ring-1 ring-gray-100">
                <span class="font-semibold text-gray-500">{{ __('dashboard.total_inference') }}</span>
                <span class="font-bold text-[#1e3a1a]">{{ $predictionHistory->total() }} {{ __('dashboard.readings_unit') }}</span>
            </div>
        </section>
    </div>

    {{-- ════════ INSIGHT ════════ --}}
    <section>
        <h3 class="font-display text-lg font-extrabold text-[#1e3a1a]">{{ __('dashboard.insights_title') }}</h3>
        <p class="mb-3 mt-0.5 text-xs text-gray-500">{{ __('dashboard.insights_subtitle') }}</p>
        @if (empty($allInsights))
            <div class="rounded-2xl bg-white p-5 text-sm text-gray-500 shadow-sm ring-1 ring-black/5">{{ __('dashboard.insights_empty') }}</div>
        @else
        <div class="grid gap-3">
            @foreach ($allInsights as $insight)
                @php
                    $accent = $insight['level'] === 'high' ? 'border-red-400' : ($insight['level'] === 'medium' ? 'border-amber-400' : 'border-emerald-400');
                    [$iconBg, $iconColor] = $insight['level'] === 'high' ? ['bg-red-50', 'text-red-600'] : ($insight['level'] === 'medium' ? ['bg-amber-50', 'text-amber-600'] : ['bg-emerald-50', 'text-emerald-600']);
                    $urg = __('insights.urgency_' . $insight['level']);
                @endphp
                <div class="flex gap-3.5 rounded-2xl border-l-4 {{ $accent }} bg-white p-5 shadow-sm ring-1 ring-black/5">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $iconBg }} {{ $iconColor }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z" /></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="font-bold text-[#1e3a1a]">{{ $insight['title'] }}
                            <span class="ml-1.5 rounded-full bg-amber-100 px-2 py-0.5 align-middle text-[10px] font-bold uppercase tracking-wide text-amber-700">{{ $urg }}</span>
                        </p>
                        <p class="mt-1 text-sm leading-relaxed text-gray-600">{{ $insight['desc'] }}</p>
                        @if (!empty($insight['device']))
                            <p class="mt-1 font-mono text-xs text-gray-400">{{ $insight['device'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </section>

    {{-- ════════ RIWAYAT ════════ --}}
    <div class="grid gap-4 xl:grid-cols-2">
        <section class="min-w-0 rounded-3xl bg-white p-4 shadow-sm ring-1 ring-black/5 sm:p-6">
            <div class="mb-4 flex items-start justify-between gap-2">
                <div>
                    <h3 class="font-display text-[16px] font-extrabold text-[#1e3a1a]">{{ __('dashboard.history_sensor') }}</h3>
                    <p class="mt-0.5 text-xs text-gray-500">{{ __('dashboard.saved_data', ['n' => $historyReadings->total()]) }}</p>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#e9f1dc] text-[#2f5d2a]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13" /><circle cx="4" cy="6" r="1" fill="currentColor" stroke="none" /><circle cx="4" cy="12" r="1" fill="currentColor" stroke="none" /><circle cx="4" cy="18" r="1" fill="currentColor" stroke="none" /></svg>
                </span>
            </div>
            <div class="-mx-4 overflow-x-auto px-4 sm:-mx-6 sm:px-6">
                <table class="w-full min-w-[560px] text-sm">
                    <thead><tr class="border-b border-gray-100 text-left text-[11px] font-extrabold uppercase tracking-wider text-gray-400">
                        <th class="whitespace-nowrap py-2.5 pr-4">Waktu</th><th class="whitespace-nowrap py-2.5 pr-4">Perangkat</th><th class="whitespace-nowrap py-2.5 pr-4 text-right text-orange-500">MQ-2</th><th class="whitespace-nowrap py-2.5 pr-4 text-right text-sky-600">MQ-3</th><th class="whitespace-nowrap py-2.5 pr-4 text-right text-violet-600">MQ-135</th><th class="whitespace-nowrap py-2.5 text-right">{{ __('dashboard.temp_column') }}</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($historyReadings as $reading)
                            <tr class="transition-colors hover:bg-[#f5f9f0]">
                                <td class="whitespace-nowrap py-3 pr-4 font-mono text-xs tabular-nums text-gray-600">{{ $reading->recorded_at?->translatedFormat('H:i:s') }}</td>
                                <td class="whitespace-nowrap py-3 pr-4 font-mono text-xs font-semibold text-gray-700">{{ $reading->device?->device_id }}</td>
                                <td class="whitespace-nowrap py-3 pr-4 text-right font-mono text-xs font-bold tabular-nums text-orange-600">{{ $reading->mq2 }}</td>
                                <td class="whitespace-nowrap py-3 pr-4 text-right font-mono text-xs font-bold tabular-nums text-sky-600">{{ $reading->mq3 }}</td>
                                <td class="whitespace-nowrap py-3 pr-4 text-right font-mono text-xs font-bold tabular-nums text-violet-600">{{ $reading->mq135 }}</td>
                                <td class="whitespace-nowrap py-3 text-right font-mono text-xs font-bold tabular-nums text-gray-400">{{ $reading->temperature !== null ? number_format((float) $reading->temperature, 1) . '°C' : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-10 text-center text-sm text-gray-500">{{ __('app.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="mt-3 border-t border-gray-100 pt-3 text-center text-xs text-gray-400">{{ __('dashboard.showing_latest', ['n' => $historyReadings->count()]) }}</p>
            @if ($historyReadings->hasPages())
                <div class="mt-2">{{ $historyReadings->links() }}</div>
            @endif
        </section>

        <section class="min-w-0 rounded-3xl bg-white p-4 shadow-sm ring-1 ring-black/5 sm:p-6">
            <div class="mb-4 flex items-start justify-between gap-2">
                <div>
                    <h3 class="font-display text-[16px] font-extrabold text-[#1e3a1a]">{{ __('dashboard.history_prediction') }}</h3>
                    <p class="mt-0.5 text-xs text-gray-500">{{ __('dashboard.ml_inference', ['n' => $predictionHistory->total()]) }}</p>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#e9f1dc] text-[#2f5d2a]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1" /><circle cx="12" cy="12" r="3" /></svg>
                </span>
            </div>
            <div class="-mx-4 overflow-x-auto px-4 sm:-mx-6 sm:px-6">
                <table class="w-full min-w-[480px] text-sm">
                    <thead><tr class="border-b border-gray-100 text-left text-[11px] font-extrabold uppercase tracking-wider text-gray-400">
                        <th class="whitespace-nowrap py-2.5 pr-4">Waktu</th><th class="whitespace-nowrap py-2.5 pr-4">Perangkat</th><th class="whitespace-nowrap py-2.5 pr-4">Label</th><th class="whitespace-nowrap py-2.5 text-right">Confidence</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($predictionHistory as $prediction)
                            <tr class="transition-colors hover:bg-[#f5f9f0]">
                                <td class="whitespace-nowrap py-3 pr-4 font-mono text-xs tabular-nums text-gray-600">{{ $prediction->predicted_at?->translatedFormat('H:i:s') }}</td>
                                <td class="whitespace-nowrap py-3 pr-4 font-mono text-xs font-semibold text-gray-700">{{ $prediction->device?->device_id }}</td>
                                <td class="whitespace-nowrap py-3 pr-4"><x-badge-status :label="$prediction->predicted_label->value" size="sm" /></td>
                                <td class="whitespace-nowrap py-3 text-right font-mono text-xs font-bold tabular-nums text-emerald-700">{{ number_format(($prediction->confidence ?? 0) * 100, 1) }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-10 text-center text-sm text-gray-500">{{ __('app.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="mt-3 border-t border-gray-100 pt-3 text-center text-xs text-gray-400">{{ __('dashboard.showing_pred_latest', ['n' => $predictionHistory->count()]) }}</p>
            @if ($predictionHistory->hasPages())
                <div class="mt-2">{{ $predictionHistory->links('vendor.pagination.cassava') }}</div>
            @endif
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
    levelHigh: @json(__('dashboard.conf_high')),
    levelMid: @json(__('dashboard.conf_mid')),
    levelLow: @json(__('dashboard.conf_low')),
};
const BADGE_CLS = {
    fresh: 'bg-emerald-100 text-emerald-800 ring-emerald-200',
    stale: 'bg-amber-100 text-amber-800 ring-amber-200',
    rotten: 'bg-red-100 text-red-800 ring-red-200',
};
const PROB_BAR = { fresh: '#16a34a', stale: '#f59e0b', rotten: '#f43f5e' };
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
function setLevel(pct) {
    const el = document.getElementById('ai-level');
    if (!el) return;
    const p = Math.max(0, Math.min(100, pct));
    const txt = p >= 70 ? T.levelHigh : (p >= 40 ? T.levelMid : T.levelLow);
    const cls = p >= 70 ? 'text-emerald-600' : (p >= 40 ? 'text-amber-600' : 'text-red-600');
    el.textContent = txt;
    el.classList.remove('text-emerald-600', 'text-amber-600', 'text-red-600');
    el.classList.add(cls);
}

(function initCharts() {
    const grid = 'rgba(0,0,0,0.05)';
    const tick = '#6b7280';

    const sc = document.getElementById('sensor-chart');
    if (sc) {
        sensorChart = new Chart(sc, {
            type: 'line',
            data: { labels: chartLabels, datasets: [
                { label: 'MQ-2', data: chartMq2, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.10)', borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 4, tension: 0.35, fill: true, yAxisID: 'y' },
                { label: 'MQ-3', data: chartMq3, borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,0.10)', borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 4, tension: 0.35, fill: true, yAxisID: 'y' },
                { label: 'MQ-135', data: chartMq135, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.10)', borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 4, tension: 0.35, fill: true, yAxisID: 'y' },
                { label: T.tempLabel, data: chartTemp, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.08)', borderWidth: 2, pointRadius: 0, pointHoverRadius: 4, tension: 0.35, fill: false, borderDash: [6, 4], yAxisID: 'y1', spanGaps: true },
            ]},
            options: { responsive: true, maintainAspectRatio: false, animation: { duration: 300 },
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { maxTicksLimit: 8, font: { size: 10 }, color: tick }, grid: { display: false } },
                    y: { type: 'linear', display: true, position: 'left', min: 0, ticks: { font: { size: 10 }, color: tick }, grid: { color: grid } },
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
                const confPct = p.confidence_percent ?? 0;
                setGauge(confPct);
                setLevel(confPct);
                const probs = p.probabilities ?? {};
                ['fresh', 'stale', 'rotten'].forEach(k => {
                    const pct = Math.round(((probs[k] ?? 0) * 100) * 10) / 10;
                    const bar = document.getElementById('pbar-' + k);
                    const num = document.getElementById('ppct-' + k);
                    if (bar) { bar.style.width = pct + '%'; bar.style.background = PROB_BAR[k]; }
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
                dot.className = 'cmp-dot h-3 w-3 shrink-0 rounded-full ' + (d.is_online ? 'animate-pulse bg-emerald-500' : 'bg-red-500');
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
        if (last) {
            const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
            set('leg-mq2', last.mq2);
            set('leg-mq3', last.mq3);
            set('leg-mq135', last.mq135);
            set('leg-temp', (last.temperature !== null && last.temperature !== undefined) ? (Math.round(last.temperature * 10) / 10) + '°C' : '—');
            const now = document.getElementById('chart-now');
            if (now) now.textContent = `M2:${last.mq2} M3:${last.mq3} M5:${last.mq135}` + (last.temperature !== null && last.temperature !== undefined ? ` • ${(Math.round(last.temperature * 10) / 10)}°C` : '');
        }
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
