@extends('layouts.app')
@section('title', __('ml.title'))

@section('content')
<div class="space-y-4" x-data="{ openService: true, cmdCopied: false }">

    {{-- Hero --}}
    <section class="rounded-[20px] border border-[#ECE8D5] bg-white p-6 shadow-[0_2px_16px_-8px_rgba(60,80,40,0.18)] sm:p-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#E4F2DF] text-[#2f5d2a]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6M10 3v6L4.5 18.5A2 2 0 0 0 6.3 21.5h11.4a2 2 0 0 0 1.8-2.9L14 9V3" /><path d="M7.5 14h9" /><circle cx="12" cy="17" r="1" fill="currentColor" stroke="none" /></svg>
                </span>
                <div class="min-w-0">
                    <span class="inline-flex items-center rounded-full border border-[#CBE3C3] bg-[#E4F2DF] px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#2f5d2a]">{{ __('ml.eyebrow') }}</span>
                    <h2 class="mt-2 font-display text-[30px] font-extrabold leading-none tracking-tight text-[#1E3A1A]">{{ __('ml.title') }}</h2>
                    <p class="mt-2 max-w-[260px] text-[13px] leading-relaxed text-[#6B7263]">{{ __('ml.subtitle') }}</p>
                </div>
            </div>
            @if ($isServiceOnline)
                <span class="inline-flex w-fit shrink-0 items-center gap-1.5 rounded-full bg-[#E4F2DF] px-4 py-2 text-[12px] font-bold text-[#0B6B4A]">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-[#1E9E5A]"></span>
                    Python ML Service: {{ __('ml.service_on') }}
                </span>
            @else
                <span class="inline-flex w-fit shrink-0 items-center gap-1.5 rounded-full bg-[#FDE8E8] px-4 py-2 text-[12px] font-bold text-[#E23A3A]">
                    <span class="h-2 w-2 rounded-full bg-[#E23A3A]"></span>
                    Python ML Service: {{ __('ml.service_off') }}
                </span>
            @endif
        </div>
    </section>

    {{-- Banner status service --}}
    <section class="rounded-[20px] border border-[#ECE8D5] bg-[#FBF7EA] p-5 sm:p-6">
        <button type="button" @click="openService = !openService" class="flex w-full items-center justify-between gap-3 text-left">
            <span class="flex min-w-0 items-center gap-2 font-display text-[17px] font-extrabold text-[#1E3A1A]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-[#1E3A1A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.2a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.2a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3h.1a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.2a1.7 1.7 0 0 0 1 1.5h.1a1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9v.1a1.7 1.7 0 0 0 1.5 1h.2a2 2 0 1 1 0 4h-.2a1.7 1.7 0 0 0-1.5 1z" /></svg>
                <span class="truncate">{{ $isServiceOnline ? 'Python ML Service Siap Digunakan' : 'Python ML Service Belum Berjalan' }}</span>
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-[#1c1c1c] transition-transform" :class="{ 'rotate-180': !openService }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6" /></svg>
        </button>
        <div x-show="openService" x-collapse>
            <p class="mt-2.5 text-[12.5px] leading-relaxed text-[#4A4A45]">
                Model SVM aktif dan siap digunakan. Klasifikasi berjalan otomatis setiap ada data sensor baru (window {{ $windowSize }}).
                Endpoint <code class="rounded bg-[#EFE9D3] px-1.5 py-0.5 font-mono text-[11px] font-bold text-[#1E3A1A]">/ml/health</code>
                dapat diakses untuk inspeksi status kesehatan inference pipeline.
            </p>
            <div class="mt-3 flex items-center gap-2 rounded-full bg-[#2B2B28] py-1.5 pl-4 pr-1.5" x-data="{ cmd: 'uvicorn ml.service:app --port 8001' }">
                <code class="min-w-0 flex-1 truncate font-mono text-[12px] text-[#E8E4D8]">$ <span x-text="cmd"></span></code>
                <button type="button" @click="navigator.clipboard.writeText(cmd); cmdCopied = true; setTimeout(() => cmdCopied = false, 2000)"
                    class="shrink-0 rounded-full bg-white/15 px-4 py-1.5 text-[11px] font-bold text-white transition hover:bg-white/25">
                    <span x-show="!cmdCopied">Salin</span><span x-show="cmdCopied" x-cloak>Disalin!</span>
                </button>
            </div>
            <p class="mt-2.5 text-[11.5px] italic leading-relaxed text-[#6B7263]">* Anda tetap dapat menggunakan fitur ini secara manual maupun otomatis setelah model dilatih dan service dijalankan.</p>
        </div>
    </section>

    {{-- Prediksi + cara kerja --}}
    <div class="grid items-start gap-4 lg:grid-cols-5">

        {{-- Prediksi real-time --}}
        <section class="rounded-[20px] border border-[#ECE8D5] bg-white p-5 shadow-[0_2px_16px_-8px_rgba(60,80,40,0.18)] sm:p-6 lg:col-span-3">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E4F2DF] text-[#2f5d2a]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><path d="M2 9a15 15 0 0 1 20 0M5.5 12.5a10 10 0 0 1 13 0M9 16a5 5 0 0 1 6 0" /><circle cx="12" cy="19.5" r="1.2" fill="currentColor" stroke="none" /></svg>
                </span>
                <div class="min-w-0">
                    <h3 class="font-display text-[19px] font-extrabold tracking-tight text-[#1E3A1A]">{{ __('ml.manual_title') }}</h3>
                    <p class="mt-0.5 text-[12px] text-[#6B7263]">{{ __('ml.manual_sub', ['n' => $windowSize]) }}</p>
                </div>
            </div>

            <label for="predict-device" class="mb-1.5 mt-5 block text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#6B7263]">{{ __('ml.choose_device') }}</label>
            <div class="relative">
                <select id="predict-device"
                    class="w-full appearance-none rounded-[10px] border border-[#EFE7CF] bg-[#FBF7EA] px-4 py-3 pr-10 text-[13px] font-medium text-[#1c1c1c] outline-none transition focus:border-[#2f5d2a] focus:bg-white">
                    @foreach ($devices as $device)
                        <option value="{{ $device->device_id }}">{{ $device->device_id }} - {{ $device->name }}{{ $device->location ? ' (' . $device->location . ')' : '' }}</option>
                    @endforeach
                </select>
                <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#1c1c1c]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6" /></svg>
            </div>

            <button type="button" onclick="runPrediction()" id="btn-analyze"
                class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#1E4D1B] px-4 py-3.5 text-[14px] font-bold text-white shadow-[0_10px_24px_-10px_rgba(30,77,27,0.8)] transition hover:brightness-110 active:scale-[0.99] disabled:opacity-60">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m21 21-4.3-4.3" /></svg>
                <span id="btn-analyze-label">{{ __('ml.analyze') }}</span>
            </button>

            <div id="result-error" class="mt-3 hidden rounded-[12px] border border-red-200 bg-red-50 px-4 py-3 text-[13px] font-medium text-red-700"></div>

            <div id="result-box" class="mt-3 hidden rounded-[16px] border border-[#EFE7CF] bg-[#FBF7EA] p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="relative h-[104px] w-[104px] shrink-0">
                            <svg viewBox="0 0 104 104" class="h-full w-full -rotate-90">
                                <circle cx="52" cy="52" r="44" fill="none" stroke="#E7E1CC" stroke-width="11" />
                                <circle id="res-donut-arc" cx="52" cy="52" r="44" fill="none" stroke="#1E9E5A" stroke-width="11" stroke-linecap="round" stroke-dasharray="276.5" stroke-dashoffset="276.5" class="transition-all duration-700" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span id="res-donut-num" class="font-display text-[19px] font-extrabold tabular-nums text-[#1E9E5A]">0%</span>
                                <span class="text-[9px] font-bold uppercase tracking-[0.12em] text-[#6B7263]">Akurasi</span>
                            </div>
                        </div>
                        <div class="min-w-0">
                            <span id="res-badge" class="inline-flex items-center gap-1 rounded-full bg-[#E4F2DF] px-2.5 py-1 text-[11px] font-extrabold text-[#0B6B4A]"></span>
                            <p id="res-device" class="mt-1.5 truncate font-mono text-[11px] font-bold text-[#1c1c1c]"></p>
                            <p class="text-[11px] text-[#6B7263]">Inference: SVM Classifier</p>
                        </div>
                    </div>
                    <div class="shrink-0 sm:text-right">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#0B6B4A]">Status Sensor</p>
                        <p class="mt-1 flex items-center gap-1.5 text-[13px] font-bold text-[#1c1c1c] sm:justify-end">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#1c1c1c]"></span>Volatilome Normal
                        </p>
                    </div>
                </div>
                <div id="result-probs" class="mt-4 space-y-3"></div>
            </div>
        </section>

        {{-- Cara kerja + status model --}}
        <div class="flex min-w-0 flex-col gap-4 lg:col-span-2">
            <section class="rounded-[20px] border border-[#ECE8D5] bg-white p-5 shadow-[0_2px_16px_-8px_rgba(60,80,40,0.18)] sm:p-6">
                <h3 class="flex items-center gap-2 font-display text-[19px] font-extrabold tracking-tight text-[#1E3A1A]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#1E3A1A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5" /><rect x="14" y="3" width="7" height="7" rx="1.5" /><rect x="3" y="14" width="7" height="7" rx="1.5" /><rect x="14" y="14" width="7" height="7" rx="1.5" /></svg>
                    {{ __('ml.how_title') }}
                </h3>
                @php
                    $mlSteps = [
                        ['n' => '1', 'bg' => 'bg-orange-500', 't' => 'Input Sensor', 'd' => '30 pembacaan terakhir dari MQ-2, MQ-3, MQ-135'],
                        ['n' => '2', 'bg' => 'bg-sky-500', 't' => 'Feature Extraction', 'd' => 'Mean, Std Dev, Min, Max per sensor → 12 fitur'],
                        ['n' => '3', 'bg' => 'bg-violet-500', 't' => 'Klasifikasi', 'd' => 'SVM (StandardScaler + SVC): Segar / Mulai Busuk / Busuk'],
                        ['n' => '4', 'bg' => 'bg-emerald-600', 't' => 'Output Probabilitas', 'd' => 'Nilai kepercayaan per kelas (0–100%)'],
                    ];
                @endphp
                <div class="mt-4 space-y-4">
                    @foreach ($mlSteps as $s)
                        <div class="flex gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $s['bg'] }} text-[12px] font-extrabold text-white">{{ $s['n'] }}</span>
                            <div class="min-w-0">
                                <p class="text-[13.5px] font-extrabold text-[#1c1c1c]">{{ $s['t'] }}</p>
                                <p class="mt-0.5 text-[12px] leading-relaxed text-[#6B7263]">{{ $s['d'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[20px] border border-[#CBD8B8] bg-[#F2F5E6] p-5 sm:p-6">
                <p class="flex items-center gap-1.5 text-[13px] font-bold text-[#1E3A1A]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9" /><path d="M12 11v5m0-8h.01" /></svg>
                    Status Model (SVM)
                </p>
                <p class="mt-2 text-[12.5px] text-[#4A4A45]">Langkah pelatihan model SVM:</p>
                <div class="mt-2.5 space-y-2.5 text-[12.5px] text-[#4A4A45]">
                    <p class="flex items-start gap-2.5">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#1E3A1A] text-[10px] font-extrabold text-white">1</span>
                        <span>Mengumpulkan data training dari pembacaan sensor</span>
                    </p>
                    <p class="flex items-start gap-2.5">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#1E3A1A] text-[10px] font-extrabold text-white">2</span>
                        <span>Melatih model SVM di Python (<code class="rounded bg-[#E7E4D2] px-1.5 py-0.5 font-mono text-[11px] font-bold text-[#1E3A1A]">ml/train.py</code>)</span>
                    </p>
                    <p class="flex items-start gap-2.5">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#1E3A1A] text-[10px] font-extrabold text-white">3</span>
                        <span>Menjalankan Python API service (<code class="rounded bg-[#E7E4D2] px-1.5 py-0.5 font-mono text-[11px] font-bold text-[#1E3A1A]">ml/service.py :8001</code>)</span>
                    </p>
                </div>
                <p class="mt-3.5 flex items-start gap-2 border-t border-[#D8DCC2] pt-3.5 text-[12px] leading-relaxed text-[#4A4A45]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-[#1E3A1A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" /><path d="m8.5 12.5 2.5 2.5 4.5-5" /></svg>
                    Model SVM aktif dan siap digunakan. Klasifikasi berjalan otomatis setiap window {{ $windowSize }} data terpenuhi.
                </p>
            </section>
        </div>
    </div>

    {{-- Hasil klasifikasi otomatis terbaru --}}
    <section class="rounded-[20px] border border-[#ECE8D5] bg-white p-5 shadow-[0_2px_16px_-8px_rgba(60,80,40,0.18)] sm:p-6">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E4F2DF] text-[#1E3A1A]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z" /></svg>
            </span>
            <div class="min-w-0">
                <h3 class="font-display text-[19px] font-extrabold tracking-tight text-[#1E3A1A]">{{ __('ml.auto_latest') }}</h3>
                <p class="mt-0.5 text-[12px] text-[#6B7263]">{{ __('ml.auto_latest_sub', ['n' => $windowSize]) }}</p>
            </div>
        </div>
        @if ($latestPrediction)
            @php
                $lpLabel = $latestPrediction->predicted_label->value;
                $lpText = $lpLabel === 'fresh' ? 'SEGAR' : ($lpLabel === 'stale' ? 'MULAI BUSUK' : 'BUSUK');
                $lpConf = round(($latestPrediction->confidence ?? 0) * 100, 1);
                $lpProbs = [
                    'fresh' => ['Segar', '#1E9E5A', round(($latestPrediction->probabilities['fresh'] ?? 0) * 100, 1)],
                    'stale' => ['Mulai Busuk', '#E8A020', round(($latestPrediction->probabilities['stale'] ?? 0) * 100, 1)],
                    'rotten' => ['Busuk', '#D93A3A', round(($latestPrediction->probabilities['rotten'] ?? 0) * 100, 1)],
                ];
            @endphp
            <div class="mt-4 rounded-[16px] bg-[#FBF7EA] p-4 sm:p-5">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <p class="flex min-w-0 flex-wrap items-center gap-x-3 gap-y-1">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#D9EDD9] px-3 py-1 text-[11px] font-extrabold text-[#0B6B4A]">{{ $lpText }}</span>
                        <span class="text-[15px] font-bold text-[#1E3A1A]">Kepercayaan: {{ number_format($lpConf, 1) }}%</span>
                    </p>
                    <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11.5px] text-[#6B7263]">
                        <span class="font-medium tabular-nums">{{ $latestPrediction->predicted_at?->translatedFormat('d M Y H:i:s') }}</span>
                        <span>•</span>
                        <span class="rounded bg-[#EFE9D3] px-1.5 py-0.5 font-mono text-[10.5px] font-bold text-[#1c1c1c]">{{ $latestPrediction->device?->device_id }}</span>
                        <span>•</span>
                        <span>model {{ $latestPrediction->model_name }}</span>
                    </p>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    @foreach ($lpProbs as [$nm, $col, $pct])
                        <div class="rounded-[12px] bg-white px-3.5 py-3 ring-1 ring-[#EFE7CF]">
                            <p class="flex items-center justify-between text-[11.5px]">
                                <span class="inline-flex items-center gap-1.5 font-bold" style="color:{{ $col }}"><span class="h-2 w-2 rounded-full" style="background:{{ $col }}"></span>{{ $nm }}</span>
                                <span class="font-bold tabular-nums" style="color:{{ $col }}">{{ number_format($pct, 1) }}%</span>
                            </p>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-[#EFE9D3]">
                                <div class="h-2 rounded-full" style="width:{{ $pct }}%;background:{{ $col }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="mt-4 rounded-[16px] border border-dashed border-[#D8DCC2] bg-[#FBF7EA] px-5 py-8 text-center">
                <p class="text-sm font-semibold text-gray-500">{{ __('ml.auto_empty') }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ __('ml.auto_empty_hint', ['n' => $windowSize]) }}</p>
            </div>
        @endif
    </section>

    {{-- Riwayat prediksi --}}
    <section class="rounded-[20px] border border-[#ECE8D5] bg-white p-5 shadow-[0_2px_16px_-8px_rgba(60,80,40,0.18)] sm:p-6">
        <h3 class="font-display text-[19px] font-extrabold tracking-tight text-[#1E3A1A]">{{ __('ml.history_db') }}</h3>
        <p class="mt-0.5 text-[12px] text-[#6B7263]">{{ __('ml.history_db_sub') }}</p>
        <div class="-mx-5 mt-3 overflow-x-auto px-5 sm:-mx-6 sm:px-6">
            <table class="w-full min-w-[640px] text-sm">
                <thead>
                    <tr class="bg-[#F4F0E1] text-left text-[10.5px] font-extrabold uppercase tracking-[0.08em] text-[#1c1c1c]">
                        <th class="px-4 py-2.5">Waktu</th>
                        <th class="px-4 py-2.5">Perangkat</th>
                        <th class="px-4 py-2.5">Label</th>
                        <th class="px-4 py-2.5">Confidence</th>
                        <th class="px-4 py-2.5">Model</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1EEE0]">
                    @forelse ($predictionHistory as $prediction)
                        @php
                            $pv = $prediction->predicted_label->value;
                            $pt = $pv === 'fresh' ? 'SEGAR' : ($pv === 'stale' ? 'MULAI BUSUK' : 'BUSUK');
                        @endphp
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-[12px] tabular-nums text-[#1c1c1c]">{{ $prediction->predicted_at?->translatedFormat('d M Y H:i:s') }}</td>
                            <td class="whitespace-nowrap px-4 py-3"><span class="rounded bg-[#F1EEE0] px-2 py-1 font-mono text-[11px] font-bold text-[#1c1c1c]">{{ $prediction->device?->device_id ?? '—' }}</span></td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full bg-[#E4F2DF] px-2.5 py-1 text-[10.5px] font-extrabold text-[#0B6B4A]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#1E9E5A]"></span>{{ $pt }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-[12px] font-bold tabular-nums text-[#0B6B4A]">{{ number_format(($prediction->confidence ?? 0) * 100, 1) }}%</td>
                            <td class="whitespace-nowrap px-4 py-3 text-[12px] text-[#6B7263]">{{ $prediction->model_name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-400">{{ __('ml.history_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($predictionHistory->total() > 0)
            <div class="mt-2 flex flex-col items-center justify-between gap-3 pt-1 sm:flex-row">
                <p class="text-[12px] text-[#6B7263]">Menampilkan <strong class="text-[#1c1c1c]">{{ $predictionHistory->firstItem() }}–{{ $predictionHistory->lastItem() }}</strong> dari <strong class="text-[#1c1c1c]">{{ $predictionHistory->total() }}</strong> data</p>
                @if ($predictionHistory->hasPages())
                    <div class="flex items-center gap-1.5">
                        @if ($predictionHistory->onFirstPage())
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#F1EEE0] text-[13px] font-bold text-[#B9B39E]">‹</span>
                        @else
                            <a href="{{ $predictionHistory->previousPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-[#F1EEE0] text-[13px] font-bold text-[#1c1c1c] transition hover:bg-[#E7E2CF]">‹</a>
                        @endif
                        @foreach (range(1, $predictionHistory->lastPage()) as $p)
                            @if ($p == 1 || $p == $predictionHistory->lastPage() || abs($p - $predictionHistory->currentPage()) <= 1)
                                @if ($p == $predictionHistory->currentPage())
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#1E4D1B] text-[12px] font-bold text-white">{{ $p }}</span>
                                @else
                                    <a href="{{ $predictionHistory->url($p) }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-[#F1EEE0] text-[12px] font-bold text-[#1c1c1c] transition hover:bg-[#E7E2CF]">{{ $p }}</a>
                                @endif
                            @endif
                        @endforeach
                        @if ($predictionHistory->hasMorePages())
                            <a href="{{ $predictionHistory->nextPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-[#F1EEE0] text-[13px] font-bold text-[#1c1c1c] transition hover:bg-[#E7E2CF]">›</a>
                        @else
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#F1EEE0] text-[13px] font-bold text-[#B9B39E]">›</span>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
const ML_LABEL = { fresh: 'SEGAR', stale: 'MULAI BUSUK', rotten: 'BUSUK' };
const ML_COLOR = { fresh: '#1E9E5A', stale: '#E8A020', rotten: '#D93A3A' };
const ML_NAME = { fresh: 'Segar', stale: 'Mulai Busuk', rotten: 'Busuk' };

async function runPrediction() {
    const deviceId = document.getElementById('predict-device').value;
    const btn = document.getElementById('btn-analyze');
    const label = document.getElementById('btn-analyze-label');
    const origText = label.textContent;
    label.textContent = @json(__('ml.analyzing'));
    btn.disabled = true;

    document.getElementById('result-box').classList.add('hidden');
    const errBox = document.getElementById('result-error');
    errBox.classList.add('hidden');

    try {
        const response = await fetch(@json(route('ml.predict')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ device_id: deviceId }),
        });
        const data = await response.json();

        if (data.success && data.condition) {
            const cond = ML_LABEL[data.condition] ? data.condition : 'fresh';
            const conf = Math.round(data.confidence * 1000) / 10;
            const box = document.getElementById('result-box');
            box.classList.remove('hidden');

            const arc = document.getElementById('res-donut-arc');
            const circ = 276.5;
            arc.style.strokeDashoffset = String(circ * (1 - conf / 100));
            arc.setAttribute('stroke', ML_COLOR[cond]);
            const num = document.getElementById('res-donut-num');
            num.textContent = conf + '%';
            num.style.color = ML_COLOR[cond];

            const badge = document.getElementById('res-badge');
            badge.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7"/></svg>${ML_LABEL[cond]}`;

            document.getElementById('res-device').textContent = deviceId;

            const probsEl = document.getElementById('result-probs');
            probsEl.innerHTML = '';
            ['fresh', 'stale', 'rotten'].forEach((key) => {
                const pct = Math.round(((data.probabilities && data.probabilities[key]) || 0) * 1000) / 10;
                const col = ML_COLOR[key];
                probsEl.innerHTML += `
                    <div>
                        <div class="flex items-center justify-between text-[11.5px]">
                            <span class="inline-flex items-center gap-1.5 font-bold" style="color:${col}"><span class="h-2 w-2 rounded-full" style="background:${col}"></span>${ML_NAME[key]}</span>
                            <span class="font-bold tabular-nums" style="color:${col}">${pct}%</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-[#EFE9D3]">
                            <div class="h-2 rounded-full transition-all duration-700" style="width:${pct}%;background:${col}"></div>
                        </div>
                    </div>`;
            });
        } else {
            errBox.textContent = data.message || 'Prediksi gagal.';
            errBox.classList.remove('hidden');
        }
    } catch (err) {
        errBox.textContent = 'Gagal menghubungi server. Periksa koneksi.';
        errBox.classList.remove('hidden');
    } finally {
        label.textContent = origText;
        btn.disabled = false;
    }
}
</script>
@endpush
