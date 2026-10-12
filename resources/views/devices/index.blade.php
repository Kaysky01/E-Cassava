@extends('layouts.app')
@section('title', __('devices.title'))

@section('content')
@php
    $totalDevices = $devices->count();
    $offlineCount = $devices->where('is_online', false)->count();
    $onlineCount = $totalDevices - $offlineCount;
    $totalSamples = $devices->sum('sensor_readings_count');
    $firstDevice = $devices->first();
    $firstReading = $firstDevice?->latestReading;
    $firstPred = $firstDevice?->latestPrediction;
@endphp
<div class="space-y-4 sm:space-y-5" x-data="{ showAdd: {{ $errors->any() ? 'true' : 'false' }}, copied: false, showBanner: true }">

    {{-- Banner sukses ala gambar --}}
    @if (session('success'))
        <div x-show="showBanner" x-transition
            class="flex items-center gap-2.5 rounded-full border border-emerald-200 bg-emerald-50/90 py-2.5 pl-3.5 pr-2.5 text-[13px] text-emerald-900 shadow-sm">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#2f5d2a] text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7" /></svg>
            </span>
            <p class="min-w-0 flex-1 truncate"><strong>{{ session('success') }}</strong>
                <span class="hidden text-emerald-800/70 sm:inline"> • Sinkronisasi sensor ESP32 termutakhir tersimpan</span>
            </p>
            <button type="button" @click="showBanner = false" aria-label="Tutup"
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-emerald-800 transition hover:bg-emerald-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
            </button>
        </div>
    @endif

    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-[24px] bg-white shadow-[var(--shadow-card)] ring-1 ring-black/5">
        <div class="pointer-events-none absolute -right-24 -top-28 h-72 w-72 rounded-full bg-emerald-100/80 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 right-40 h-52 w-52 rounded-full bg-cassava-100/70 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100/80 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.14em] text-[#2f5d2a] ring-1 ring-emerald-200/60">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#2f5d2a]"></span>Multi-Gudang
                </span>
                <h2 class="mt-2.5 font-display text-[28px] font-extrabold tracking-tight text-[#1e3a1a] sm:text-[32px]">{{ __('devices.title') }}</h2>
                <p class="mt-1 max-w-xl text-[13px] leading-relaxed text-gray-500">{{ __('devices.subtitle') }} Satu akun bisa banyak gudang.</p>
            </div>
            <button @click="showAdd = true"
                class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-full bg-[#2f5d2a] px-6 py-3 text-sm font-bold text-white shadow-[0_12px_28px_-10px_rgba(47,93,42,0.7)] transition hover:brightness-110 active:scale-[0.98]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M12 5v14M5 12h14" /></svg>
                {{ __('devices.add') }}
            </button>
        </div>
    </section>

    {{-- Statistik --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5 sm:p-5">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#eef3e3] text-[#5c7a45]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V8m0 8h.01" /><path d="M8.5 8h7a3 3 0 0 1 3 3v1a3 3 0 0 1-3 3H14l-2 3-2-3H8.5a3 3 0 0 1-3-3v-1a3 3 0 0 1 3-3z" opacity="0"/><path d="M5 10v3.5A3.5 3.5 0 0 0 8.5 17H9l-1 3 4-3h3.5a3.5 3.5 0 0 0 3.5-3.5V10" /><circle cx="12" cy="7" r="2.4" /><path d="M9.5 7h5" /></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-gray-500">Total Alat</p>
                <p class="font-display text-xl font-extrabold tabular-nums text-gray-900">{{ $totalDevices }} Node</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5 sm:p-5">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 16.5a5 5 0 0 1 7 0M5 13a10 10 0 0 1 14 0M2 9.5a15 15 0 0 1 20 0" /><circle cx="12" cy="19.5" r="1.2" fill="currentColor" stroke="none" /><line x1="3" y1="3" x2="21" y2="21" /></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-gray-500">Status Node</p>
                <p class="font-display text-xl font-extrabold tabular-nums text-red-500">{{ $offlineCount }} Offline</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5 sm:p-5">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6M10 3v6L4.5 18.5A2 2 0 0 0 6.3 21.5h11.4a2 2 0 0 0 1.8-2.9L14 9V3" /><path d="M7.5 14h9" /></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-gray-500">Sensor Aktif</p>
                <p class="font-display text-xl font-extrabold tabular-nums text-emerald-700">3 Array</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5 sm:p-5">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#eef3e3] text-[#5c7a45]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5.5" rx="7" ry="2.8" /><path d="M5 5.5V12c0 1.5 3.1 2.8 7 2.8s7-1.3 7-2.8V5.5" /><path d="M5 12v6.5c0 1.5 3.1 2.8 7 2.8s7-1.3 7-2.8V12" /></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-gray-500">Siklus Volatilome</p>
                <p class="font-display text-xl font-extrabold tabular-nums text-gray-900">{{ $totalSamples }} Sampel</p>
            </div>
        </div>
    </div>

    {{-- Grid utama: alat | tambah | diagnostik --}}
    <div class="grid items-start gap-4 lg:grid-cols-3">

        {{-- Kolom alat --}}
        <div class="space-y-4">
            @forelse ($devices as $device)
                @php
                    $pred = $device->latestPrediction;
                    $reading = $device->latestReading;
                    $online = (bool) $device->is_online;
                @endphp
                <article class="relative overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-black/5">
                    <div class="h-1.5 {{ $online ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-display text-[17px] font-extrabold uppercase tracking-tight text-gray-900">{{ $device->name }}</h3>
                            @if ($online)
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-emerald-600 ring-1 ring-emerald-200">
                                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>{{ __('app.online') }}
                                </span>
                            @else
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-red-500 ring-1 ring-red-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>{{ __('app.offline') }}
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 flex items-center gap-1 text-[12px] text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z" /><circle cx="12" cy="10" r="2.5" /></svg>
                            {{ $device->location ?? '—' }}
                        </p>
                        <p class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-[#1e3a1a] px-3 py-1.5 font-mono text-[10px] font-bold tracking-wide text-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="7" width="16" height="10" rx="2" /><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /></svg>
                            {{ $device->device_id }}
                        </p>

                        <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-xl bg-[#faf7ec] px-2 py-2.5 ring-1 ring-[#efe9d3]">
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-orange-500">MQ-2</p>
                                <p class="font-mono text-lg font-extrabold tabular-nums text-orange-500">{{ $reading?->mq2 ?? '—' }}</p>
                                <p class="text-[9px] font-medium text-gray-400">LPG / Smoke</p>
                            </div>
                            <div class="rounded-xl bg-[#faf7ec] px-2 py-2.5 ring-1 ring-[#efe9d3]">
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-sky-600">MQ-3</p>
                                <p class="font-mono text-lg font-extrabold tabular-nums text-emerald-700">{{ $reading?->mq3 ?? '—' }}</p>
                                <p class="text-[9px] font-medium text-gray-400">Etanol / VOC</p>
                            </div>
                            <div class="rounded-xl bg-[#faf7ec] px-2 py-2.5 ring-1 ring-[#efe9d3]">
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-violet-500">MQ-135</p>
                                <p class="font-mono text-lg font-extrabold tabular-nums text-violet-600">{{ $reading?->mq135 ?? '—' }}</p>
                                <p class="text-[9px] font-medium text-gray-400">Amonia / CO2</p>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1.5 rounded-xl bg-[#faf7ec] px-3.5 py-3 text-[12px] ring-1 ring-[#efe9d3]">
                            <p class="flex items-center justify-between gap-2 text-gray-600">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                                    {{ __('devices.last_seen') }}:
                                </span>
                                <strong class="tabular-nums text-gray-900">{{ $device->last_seen_at?->translatedFormat('d M H:i') ?? __('devices.never') }}</strong>
                            </p>
                            <p class="flex items-center justify-between gap-2 text-gray-600">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1" /><circle cx="12" cy="12" r="3" /></svg>
                                    {{ __('devices.last_prediction') }}:
                                </span>
                                @if ($pred)
                                    <x-badge-status :label="$pred->predicted_label->value" size="sm" />
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </p>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-1.5 text-[11px]">
                            @if ($device->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 font-bold text-emerald-700 ring-1 ring-emerald-100">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ __('devices.status_active') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 font-bold text-gray-500">
                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>{{ __('devices.status_inactive') }}
                                </span>
                            @endif
                            @if ($device->api_key_hint)
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 font-mono text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 10a4 4 0 0 0-5.6 5.6L3 21m6-6 2 2m4-8a4 4 0 0 1 5.6-5.6L21 8m-6 2-2-2" opacity="0"/><path d="M9 15a4 4 0 1 1 6 0M4 20l3-3m2-8a5 5 0 0 1 7-1l3 3a5 5 0 0 1-7 7l-1-1" /><circle cx="9" cy="15" r="1.4" fill="currentColor" stroke="none" /></svg>
                                    …{{ $device->api_key_hint }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 rounded-full bg-[#f1eee0] px-2.5 py-1 font-semibold text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><ellipse cx="12" cy="5.5" rx="7" ry="2.8" /><path d="M5 5.5V12c0 1.5 3.1 2.8 7 2.8s7-1.3 7-2.8V5.5" /><path d="M5 12v6.5c0 1.5 3.1 2.8 7 2.8s7-1.3 7-2.8V12" /></svg>
                                {{ $device->sensor_readings_count }} data
                            </span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-1.5 border-t border-gray-100 pt-3.5">
                            <a href="{{ route('devices.edit', $device) }}"
                                class="inline-flex items-center gap-1 rounded-full bg-[#f4f1e4] px-3.5 py-2 text-[12px] font-bold text-gray-700 transition hover:bg-[#e9e4d0]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.8 2.8 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z" /></svg>
                                {{ __('app.edit') }}
                            </a>
                            <form method="POST" action="{{ route('devices.toggle', $device) }}">
                                @csrf
                                <button class="inline-flex items-center gap-1 rounded-full bg-[#f4f1e4] px-3.5 py-2 text-[12px] font-bold text-gray-700 transition hover:bg-[#e9e4d0]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                                    {{ $device->is_active ? __('devices.deactivate') : __('devices.activate') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('devices.regenerate', $device) }}" onsubmit="return confirm(@js(__('devices.regenerate_confirm')))">
                                @csrf
                                <button class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-3.5 py-2 text-[12px] font-bold text-sky-700 ring-1 ring-sky-100 transition hover:bg-sky-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-2.6-6.4M21 4v5h-5" /></svg>
                                    {{ __('devices.regenerate') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('devices.destroy', $device) }}" onsubmit="return confirm(@js(__('devices.delete_confirm')))">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3.5 py-2 text-[12px] font-bold text-red-500 ring-1 ring-red-100 transition hover:bg-red-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m3 0-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" /></svg>
                                    {{ __('app.delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[24px] bg-white p-8 text-center shadow-sm ring-1 ring-black/5">
                    <p class="font-display text-base font-extrabold text-[#1e3a1a]">{{ __('devices.empty_title') }}</p>
                    <p class="mx-auto mt-1 max-w-xs text-[13px] text-gray-500">{{ __('devices.empty_desc') }}</p>
                </div>
            @endforelse
        </div>

        {{-- Kolom tambah --}}
        <button type="button" @click="showAdd = true"
            class="group flex min-h-[420px] w-full flex-col items-center justify-center rounded-[24px] border-[1.5px] border-dashed border-[#b9cf8f] bg-[#fdfcf4]/70 p-8 text-center transition hover:border-[#2f5d2a] hover:bg-[#f7f5e8]">
            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-[#2f5d2a] ring-1 ring-emerald-100 transition group-hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14" /></svg>
            </span>
            <span class="mt-4 font-display text-xl font-extrabold text-[#1e3a1a]">Tambah Alat Baru</span>
            <span class="mx-auto mt-1.5 block max-w-[260px] text-[13px] leading-relaxed text-gray-500">Hubungkan mikrokontroler ESP32 Electronic Nose ke sistem deteksi pembusukan umbi singkong.</span>
            <span class="mt-5 inline-flex items-center gap-1.5 rounded-full bg-[#f1eee0] px-4 py-2.5 text-[12px] font-bold text-gray-700 ring-1 ring-[#e3ddc4] transition group-hover:bg-white">
                Konfigurasi ESP32
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6" /></svg>
            </span>
        </button>

        {{-- Kolom diagnostik --}}
        <aside class="flex flex-col gap-4">
            <div class="rounded-[24px] bg-white p-5 shadow-sm ring-1 ring-black/5 sm:p-6">
                <div class="flex items-center justify-between gap-2">
                    <p class="flex items-center gap-2 font-display text-[15px] font-extrabold text-[#1e3a1a]">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6M10 3v6L4.5 18.5A2 2 0 0 0 6.3 21.5h11.4a2 2 0 0 0 1.8-2.9L14 9V3" /><path d="M7.5 14h9" /><circle cx="12" cy="17" r="1" fill="currentColor" stroke="none" /></svg>
                        </span>
                        Diagnostik E-Nose
                    </p>
                    <span class="rounded-full bg-[#f4f1e4] px-2.5 py-1 text-[10px] font-bold text-gray-500 ring-1 ring-[#e7e1cc]">Protokol SVM</span>
                </div>
                <p class="mt-3 text-[12.5px] leading-relaxed text-gray-500">Sensor mendeteksi gas volatil khas pasca-panen (asam asetat, etanol, dan hidrogen sianida). Kalibrasi chamber udara bebas berkala memastikan akurasi klasifikasi Support Vector Machine tetap di atas 94%.</p>
                <div class="mt-4 grid grid-cols-3 divide-x divide-[#e7e1cc] rounded-xl bg-[#faf7ec] py-3 ring-1 ring-[#efe9d3]">
                    <div class="px-3">
                        <p class="text-[10px] font-bold leading-tight text-gray-500">Laju Alir Chamber</p>
                        <p class="mt-1 text-[14px] font-extrabold tabular-nums text-gray-900">1.2 L/menit</p>
                    </div>
                    <div class="px-3">
                        <p class="text-[10px] font-bold leading-tight text-gray-500">Suhu Operasional</p>
                        <p class="mt-1 text-[14px] font-extrabold tabular-nums text-gray-900">28.4 °C</p>
                    </div>
                    <div class="px-3">
                        <p class="text-[10px] font-bold leading-tight text-gray-500">Kelembaban</p>
                        <p class="mt-1 text-[14px] font-extrabold tabular-nums text-gray-900">62% RH</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between gap-2 rounded-xl bg-[#faf7ec] px-3.5 py-3 ring-1 ring-[#efe9d3]">
                    <p class="flex items-center gap-1.5 text-[12px] font-semibold text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.2a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.2a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3h.1a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.2a1.7 1.7 0 0 0 1 1.5h.1a1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9v.1a1.7 1.7 0 0 0 1.5 1h.2a2 2 0 1 1 0 4h-.2a1.7 1.7 0 0 0-1.5 1z" /></svg>
                        Firmware ESP32 v2.4.1
                    </p>
                    <a href="#" class="inline-flex items-center gap-1 text-[12px] font-bold text-emerald-700 hover:underline">Panduan Kalibrasi
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M8 7h9v9" /></svg>
                    </a>
                </div>
            </div>

            @if ($firstDevice)
                <div class="rounded-[24px] bg-[#1e3a1a] p-5 text-white shadow-sm sm:p-6">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-white/60">Sampel Terakhir</p>
                    <p class="mt-1 font-mono text-sm font-bold text-emerald-200">{{ $firstDevice->device_id }}</p>
                    <div class="mt-3 flex items-end justify-between gap-2">
                        <div class="flex gap-4 font-mono text-xs tabular-nums">
                            <span>MQ-2 <strong class="block text-base text-orange-300">{{ $firstReading?->mq2 ?? '—' }}</strong></span>
                            <span>MQ-3 <strong class="block text-base text-sky-300">{{ $firstReading?->mq3 ?? '—' }}</strong></span>
                            <span>MQ-135 <strong class="block text-base text-violet-300">{{ $firstReading?->mq135 ?? '—' }}</strong></span>
                        </div>
                        @if ($firstPred)
                            <x-badge-status :label="$firstPred->predicted_label->value" size="sm" />
                        @endif
                    </div>
                </div>
            @endif
        </aside>
    </div>

    {{-- Modal tambah alat --}}
    <div x-show="showAdd" x-cloak class="fixed inset-0 z-[80] flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true" aria-label="{{ __('devices.add_title') }}">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showAdd = false"></div>
        <div class="card relative w-full max-w-lg p-6 sm:p-7">
            <h3 class="font-display text-xl font-extrabold">{{ __('devices.add_title') }}</h3>
            <form method="POST" action="{{ route('devices.store') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="dev-name" class="label-shell">{{ __('devices.name') }}</label>
                    <input id="dev-name" name="name" required maxlength="50" value="{{ old('name') }}" class="input-shell" placeholder="{{ __('devices.name_ph') }}" />
                    <p class="mt-1 text-xs text-gray-500">{{ __('devices.name_hint') }}</p>
                    @error('name')
                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="dev-loc" class="label-shell">{{ __('devices.location') }}</label>
                    <input id="dev-loc" name="location" maxlength="150" value="{{ old('location') }}" class="input-shell" placeholder="{{ __('devices.location_ph') }}" />
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="submit" class="btn-primary flex-1">{{ __('devices.create') }}</button>
                    <button type="button" @click="showAdd = false" class="btn-ghost">{{ __('app.cancel') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal snippet ESP32 (key penuh hanya sekali) --}}
    @if (!empty($plainKey) && !empty($plainDeviceId))
        @php $freshDevice = $devices->firstWhere('id', $plainDeviceId); @endphp
        <x-modal name="esp-config" :show="true">
            <h3 class="font-display text-xl font-extrabold">{{ __('devices.created_ok') }}</h3>
            <p class="mt-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ __('devices.api_key_once') }}</p>
            <p class="mb-1.5 mt-5 label-shell">{{ __('devices.esp_snippet') }}</p>
            <p class="mb-2 text-xs text-gray-500">{{ __('devices.esp_step') }}</p>
            <div class="relative">
                <pre id="esp-snippet" class="overflow-x-auto rounded-2xl bg-gray-900 p-4 font-mono text-xs leading-relaxed text-lime-300">const char* WIFI_SSID     = "NAMA_WIFI_KAMU";
const char* WIFI_PASSWORD = "PASSWORD_WIFI_KAMU";
const char* API_URL       = "{{ url('/api/sensor-data') }}";
const char* DEVICE_ID     = "{{ $freshDevice?->device_id }}";
const char* API_KEY       = "{{ $plainKey }}";</pre>
                <button type="button"
                    @click="navigator.clipboard.writeText(document.getElementById('esp-snippet').innerText); copied = true; setTimeout(() => copied = false, 2000)"
                    class="absolute right-3 top-3 rounded-xl bg-white/10 px-3 py-1.5 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <span x-show="!copied">{{ __('devices.copy') }}</span>
                    <span x-show="copied" x-cloak>{{ __('devices.copied') }}</span>
                </button>
            </div>
            <div class="mt-3 rounded-2xl bg-gray-50 p-4 font-mono text-xs break-all">
                <span class="font-sans font-bold">API_KEY:</span> {{ $plainKey }}
            </div>
            <button type="button" @click="$dispatch('close-modal-esp-config')" class="btn-primary mt-5 w-full">{{ __('devices.close') }}</button>
        </x-modal>
    @endif
</div>
@endsection
