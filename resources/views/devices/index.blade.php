@extends('layouts.app')
@section('title', __('devices.title'))

@section('content')
@php
    $totalDevices = $devices->count();
    $offlineCount = $devices->where('is_online', false)->count();
    $totalSamples = $devices->sum('sensor_readings_count');
@endphp
<div class="space-y-4" x-data="{ showAdd: {{ $errors->any() ? 'true' : 'false' }}, copied: false, showBanner: true }">

    {{-- Banner sukses --}}
    @if (session('success'))
        <div x-show="showBanner" x-transition
            class="flex items-center gap-2.5 rounded-2xl border border-[#CBE3C3] bg-[#EAF6E6] py-2.5 pl-3.5 pr-2.5 text-[13px] shadow-sm">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#2f5d2a] text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7" /></svg>
            </span>
            <p class="min-w-0 flex-1 truncate text-[#3A5A34]"><strong class="font-extrabold text-[#24401F]">{{ session('success') }}</strong>
                <span class="font-medium"> • Sinkronisasi sensor ESP32 termutakhir tersimpan</span>
            </p>
            <button type="button" @click="showBanner = false" aria-label="Tutup"
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[#3A5A34] transition hover:bg-[#D8EDD3]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
            </button>
        </div>
    @endif

    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-[20px] border border-[#ECE8D5] bg-white shadow-[0_2px_16px_-8px_rgba(60,80,40,0.18)]">
        <div class="pointer-events-none absolute inset-y-0 right-0 w-[42%] bg-gradient-to-l from-[#D9F0D3]/90 via-[#EAF6E6]/50 to-transparent"></div>
        <div class="relative flex flex-col gap-4 p-6 sm:p-7 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-[#CBE3C3] bg-[#E4F2DF] px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#2f5d2a]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#2f5d2a]"></span>Multi-Gudang
                </span>
                <h2 class="mt-2 font-display text-[30px] font-extrabold leading-none tracking-tight text-[#1E3A1A]">Alat / Gudang</h2>
                <p class="mt-2 text-[13px] text-[#6B7263]">Kelola semua ESP32 milikmu secara terpusat. Satu akun bisa banyak gudang.</p>
            </div>
            <button @click="showAdd = true"
                class="inline-flex w-full shrink-0 items-center justify-center gap-1.5 rounded-full bg-[#2F5D2A] px-6 py-3 text-sm font-bold text-white shadow-[0_10px_24px_-10px_rgba(47,93,42,0.8)] transition hover:brightness-110 active:scale-[0.98] lg:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M12 5v14M5 12h14" /></svg>
                Tambah Alat
            </button>
        </div>
    </section>

    {{-- Statistik --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="flex items-center gap-3 rounded-[16px] border border-[#ECE8D5] bg-white px-5 py-4 shadow-[0_2px_12px_-8px_rgba(60,80,40,0.15)]">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#EDF3E2] text-[#6B8A4E]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 11V7m0 10v-2" /><path d="M7 17h10M8 13.5h8" /><path d="M6 13.5V12a6 6 0 0 1 12 0v1.5" /><circle cx="12" cy="5.5" r="1.6" /><path d="M4 20h16" /></svg>
            </span>
            <div class="min-w-0 leading-tight">
                <p class="text-[10px] font-bold uppercase leading-tight tracking-[0.1em] text-[#6B7263]">Total Alat</p>
                <p class="mt-0.5 font-display text-[20px] font-extrabold tabular-nums text-[#1c1c1c]">{{ $totalDevices }} Node</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-[16px] border border-[#ECE8D5] bg-white px-5 py-4 shadow-[0_2px_12px_-8px_rgba(60,80,40,0.15)]">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#FDE8E8] text-[#E23A3A]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 16.5a5 5 0 0 1 7 0M5 13a10 10 0 0 1 14 0M2 9.5a15 15 0 0 1 20 0" /><circle cx="12" cy="19.5" r="1.2" fill="currentColor" stroke="none" /><line x1="3" y1="3" x2="21" y2="21" /></svg>
            </span>
            <div class="min-w-0 leading-tight">
                <p class="text-[10px] font-bold uppercase leading-tight tracking-[0.1em] text-[#6B7263]">Status Node</p>
                <p class="mt-0.5 font-display text-[20px] font-extrabold tabular-nums text-[#E23A3A]">{{ $offlineCount }} Offline</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-[16px] border border-[#ECE8D5] bg-white px-5 py-4 shadow-[0_2px_12px_-8px_rgba(60,80,40,0.15)]">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#E3F3E8] text-[#1E9E5A]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6M10 3v6L4.5 18.5A2 2 0 0 0 6.3 21.5h11.4a2 2 0 0 0 1.8-2.9L14 9V3" /><path d="M7.5 14h9" /></svg>
            </span>
            <div class="min-w-0 leading-tight">
                <p class="text-[10px] font-bold uppercase leading-tight tracking-[0.1em] text-[#6B7263]">Sensor Aktif</p>
                <p class="mt-0.5 font-display text-[20px] font-extrabold tabular-nums text-[#0B6B4A]">3 Array</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-[16px] border border-[#ECE8D5] bg-white px-5 py-4 shadow-[0_2px_12px_-8px_rgba(60,80,40,0.15)]">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#EDF3E2] text-[#6B8A4E]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5.5" rx="7" ry="2.8" /><path d="M5 5.5V12c0 1.5 3.1 2.8 7 2.8s7-1.3 7-2.8V5.5" /><path d="M5 12v6.5c0 1.5 3.1 2.8 7 2.8s7-1.3 7-2.8V12" /></svg>
            </span>
            <div class="min-w-0 leading-tight">
                <p class="text-[10px] font-bold uppercase leading-tight tracking-[0.1em] text-[#6B7263]">Siklus Volatilome</p>
                <p class="mt-0.5 font-display text-[20px] font-extrabold tabular-nums text-[#1c1c1c]">{{ $totalSamples }} Sampel</p>
            </div>
        </div>
    </div>

    {{-- Grid utama --}}
    <div class="grid items-stretch gap-4 md:grid-cols-2 xl:grid-cols-3">

        {{-- Kolom alat --}}
        <div class="flex min-w-0 flex-col gap-4">
            @forelse ($devices as $device)
                @php
                    $pred = $device->latestPrediction;
                    $reading = $device->latestReading;
                    $online = (bool) $device->is_online;
                    $predLabel = $pred?->predicted_label->value;
                    $predText = $predLabel === 'fresh' ? 'Segar' : ($predLabel === 'stale' ? 'Mulai Busuk' : ($predLabel === 'rotten' ? 'Busuk' : '—'));
                @endphp
                <article class="relative flex h-full min-w-0 flex-col overflow-hidden rounded-[20px] border border-[#ECE8D5] bg-white shadow-[0_2px_16px_-8px_rgba(60,80,40,0.18)]">
                    <div class="h-[6px] w-full {{ $online ? 'bg-[#2E9E5B]' : 'bg-[#D93A3A]' }}"></div>
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="min-w-0 font-display text-[18px] font-extrabold tracking-tight text-[#1c1c1c]">{{ $device->name }}</h3>
                            @if ($online)
                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-[#E3F3E8] px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-[#1E9E5A]">
                                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-[#1E9E5A]"></span>Online
                                </span>
                            @else
                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-[#FDE8E8] px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-[#E23A3A]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#E23A3A]"></span>Offline
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 flex items-center gap-1 text-[12px] text-[#6B7263]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-[#1E9E5A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z" /><circle cx="12" cy="10" r="2.5" /></svg>
                            <span class="truncate">{{ $device->location ?? '—' }}</span>
                        </p>
                        <p class="mt-2 inline-flex w-fit items-center gap-1.5 rounded-full bg-[#1E3A1A] px-3 py-[5px] font-mono text-[10px] font-bold tracking-wide text-[#B8E39A]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="7" width="16" height="10" rx="2" /><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /></svg>
                            {{ $device->device_id }}
                        </p>

                        <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-[10px] border border-[#EFE7CF] bg-[#FBF7EA] px-1 py-2">
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-[#E8822A]">MQ-2</p>
                                <p class="font-display text-[19px] font-extrabold tabular-nums leading-tight text-[#E8822A]">{{ $reading?->mq2 ?? '—' }}</p>
                                <p class="text-[9px] font-medium text-[#9AA091]">LPG / Smoke</p>
                            </div>
                            <div class="rounded-[10px] border border-[#EFE7CF] bg-[#FBF7EA] px-1 py-2">
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-[#2B7CD3]">MQ-3</p>
                                <p class="font-display text-[19px] font-extrabold tabular-nums leading-tight text-[#0B6B4A]">{{ $reading?->mq3 ?? '—' }}</p>
                                <p class="text-[9px] font-medium text-[#9AA091]">Etanol / VOC</p>
                            </div>
                            <div class="rounded-[10px] border border-[#EFE7CF] bg-[#FBF7EA] px-1 py-2">
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-[#8B5CF6]">MQ-135</p>
                                <p class="font-display text-[19px] font-extrabold tabular-nums leading-tight text-[#7C3AED]">{{ $reading?->mq135 ?? '—' }}</p>
                                <p class="text-[9px] font-medium text-[#9AA091]">Amonia / CO2</p>
                            </div>
                        </div>

                        <div class="mt-2.5 rounded-[10px] border border-[#EFE7CF] bg-[#FBF7EA] px-3 py-2.5 text-[12px]">
                            <p class="flex items-center justify-between gap-2 text-[#4A4A45]">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                                    Terakhir terlihat:
                                </span>
                                <strong class="tabular-nums text-[#1c1c1c]">{{ $device->last_seen_at?->translatedFormat('d M H:i') ?? '—' }}</strong>
                            </p>
                            <p class="mt-1.5 flex items-center justify-between gap-2 text-[#4A4A45]">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1" /><circle cx="12" cy="12" r="3" /></svg>
                                    Prediksi terakhir:
                                </span>
                                @if ($pred)
                                    <span class="inline-flex items-center gap-1 rounded-full border border-[#CBE3C3] bg-[#E4F2DF] px-2.5 py-[3px] text-[11px] font-bold text-[#0B6B4A]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 19C5 9 13 5 20 5c0 8-4 14-13 14" /><path d="M5 19c3-5 7-9 11-11" /></svg>
                                        {{ $predText }}
                                    </span>
                                @else
                                    <span class="text-[#9AA091]">—</span>
                                @endif
                            </p>
                        </div>

                        <div class="mt-2.5 flex flex-wrap items-center gap-1.5 text-[11px]">
                            @if ($device->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#E4F2DF] px-2.5 py-1 text-[11px] font-bold text-[#0B6B4A]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#1E9E5A]"></span>Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#EFEFEA] px-2.5 py-1 text-[11px] font-bold text-[#6B7263]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#9AA091]"></span>Nonaktif
                                </span>
                            @endif
                            @if ($device->api_key_hint)
                                <span class="inline-flex items-center gap-1 rounded-full bg-[#F1EEE0] px-2.5 py-1 font-mono text-[11px] text-[#6B7263]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="15" r="4" /><path d="m11 12 9-9m-4 4 3 3" /></svg>
                                    …{{ $device->api_key_hint }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 rounded-full bg-[#F1EEE0] px-2.5 py-1 text-[11px] font-semibold text-[#4A4A45]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><ellipse cx="12" cy="5.5" rx="7" ry="2.8" /><path d="M5 5.5V12c0 1.5 3.1 2.8 7 2.8s7-1.3 7-2.8V5.5" /><path d="M5 12v6.5c0 1.5 3.1 2.8 7 2.8s7-1.3 7-2.8V12" /></svg>
                                {{ $device->sensor_readings_count }} data
                            </span>
                        </div>

                        <div class="mt-3.5 flex flex-wrap items-center gap-1.5">
                            <a href="{{ route('devices.edit', $device) }}"
                                class="inline-flex flex-1 items-center justify-center gap-1 whitespace-nowrap rounded-full bg-[#F3F0E2] px-2 py-2 text-[11px] font-bold text-[#3F3F3F] transition hover:bg-[#E7E2CF]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.8 2.8 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z" /></svg>
                                Ubah
                            </a>
                            <form method="POST" action="{{ route('devices.toggle', $device) }}" class="flex-1">
                                @csrf
                                <button class="inline-flex w-full items-center justify-center gap-1 whitespace-nowrap rounded-full bg-[#F3F0E2] px-2 py-2 text-[11px] font-bold text-[#3F3F3F] transition hover:bg-[#E7E2CF]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9" /><path d="M10 9v6M14 9v6" /></svg>
                                    {{ $device->is_active ? __('devices.deactivate') : __('devices.activate') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('devices.regenerate', $device) }}" onsubmit="return confirm(@js(__('devices.regenerate_confirm')))" class="flex-1">
                                @csrf
                                <button class="inline-flex w-full items-center justify-center gap-1 whitespace-nowrap rounded-full bg-[#E3F2FD] px-2 py-2 text-[11px] font-bold text-[#1D6FDC] transition hover:bg-[#D2E9FB]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-2.6-6.4M21 4v5h-5" /></svg>
                                    Buat ulang
                                </button>
                            </form>
                            <form method="POST" action="{{ route('devices.destroy', $device) }}" onsubmit="return confirm(@js(__('devices.delete_confirm')))" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex w-full items-center justify-center gap-1 whitespace-nowrap rounded-full bg-[#FDE8E8] px-2 py-2 text-[11px] font-bold text-[#E23A3A] transition hover:bg-[#FBD5D5]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m3 0-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" /></svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[20px] border border-[#ECE8D5] bg-white p-8 text-center shadow-sm">
                    <p class="font-display text-base font-extrabold text-[#1e3a1a]">{{ __('devices.empty_title') }}</p>
                    <p class="mx-auto mt-1 max-w-xs text-[13px] text-gray-500">{{ __('devices.empty_desc') }}</p>
                </div>
            @endforelse
        </div>

        {{-- Kolom tambah --}}
        <button type="button" @click="showAdd = true"
            class="group flex h-full min-h-[480px] w-full min-w-0 flex-col items-center justify-center rounded-[20px] border-[1.5px] border-dashed border-[#B9CF8F] bg-[#FCFBF1]/70 p-8 text-center transition hover:border-[#2f5d2a] hover:bg-[#F7F5E6]">
            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-[#E9F3E4] text-[#2f5d2a] transition group-hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14" /></svg>
            </span>
            <span class="mt-4 font-display text-[20px] font-extrabold text-[#1E3A1A]">Tambah Alat Baru</span>
            <span class="mx-auto mt-1.5 block max-w-[270px] text-[12.5px] leading-relaxed text-[#6B7263]">Hubungkan mikrokontroler ESP32 Electronic Nose ke sistem deteksi pembusukan umbi singkong.</span>
            <span class="mt-5 inline-flex items-center gap-1.5 rounded-full border border-[#E3DDC4] bg-[#F3F0E2] px-4 py-2.5 text-[12px] font-bold text-[#3F3F3F] transition group-hover:bg-white">
                Konfigurasi ESP32
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6" /></svg>
            </span>
        </button>

        {{-- Kolom diagnostik --}}
        <aside class="flex min-w-0 flex-col md:col-span-2 xl:col-span-1">
            <div class="flex h-full flex-col rounded-[20px] border border-[#ECE8D5] bg-white p-5 shadow-[0_2px_16px_-8px_rgba(60,80,40,0.18)]">
                <div class="flex items-center justify-between gap-2">
                    <p class="flex min-w-0 items-center gap-2 font-display text-[15px] font-extrabold text-[#1e3a1a]">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#E9F3E4] text-[#2f5d2a]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6M10 3v6L4.5 18.5A2 2 0 0 0 6.3 21.5h11.4a2 2 0 0 0 1.8-2.9L14 9V3" /><path d="M7.5 14h9" /><circle cx="12" cy="17" r="1" fill="currentColor" stroke="none" /></svg>
                        </span>
                        <span class="truncate">Diagnostik E-Nose</span>
                    </p>
                    <span class="shrink-0 rounded-full border border-[#E3DDC4] bg-[#F6F3E7] px-2.5 py-1 text-[10px] font-bold text-[#6B7263]">Protokol SVM</span>
                </div>
                <p class="mt-3 text-[12.5px] leading-relaxed text-[#6B7263]">Sensor mendeteksi gas volatil khas pasca-panen (asam asetat, etanol, dan hidrogen sianida). Kalibrasi chamber udara bebas berkala memastikan akurasi klasifikasi Support Vector Machine tetap di atas 94%.</p>
                <div class="mt-4 grid grid-cols-3 divide-x divide-[#E7E1CC] border-y border-[#EFE7CF] bg-[#FBF7EA] py-3">
                    <div class="px-3">
                        <p class="text-[10px] font-bold leading-tight text-[#4A4A45]">Laju Alir Chamber</p>
                        <p class="mt-1 text-[13.5px] font-extrabold tabular-nums text-[#1c1c1c]">1.2 L/menit</p>
                    </div>
                    <div class="px-3">
                        <p class="text-[10px] font-bold leading-tight text-[#4A4A45]">Suhu Operasional</p>
                        <p class="mt-1 text-[13.5px] font-extrabold tabular-nums text-[#1c1c1c]">28.4 °C</p>
                    </div>
                    <div class="px-3">
                        <p class="text-[10px] font-bold leading-tight text-[#4A4A45]">Kelembaban</p>
                        <p class="mt-1 text-[13.5px] font-extrabold tabular-nums text-[#1c1c1c]">62% RH</p>
                    </div>
                </div>
                <div class="mt-auto flex items-center justify-between gap-2 border-b border-[#EFE7CF] bg-[#FBF7EA] px-3 py-3.5">
                    <p class="flex min-w-0 items-center gap-1.5 text-[12px] font-semibold text-[#4A4A45]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-[#1E9E5A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.2a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.2a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3h.1a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.2a1.7 1.7 0 0 0 1 1.5h.1a1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9v.1a1.7 1.7 0 0 0 1.5 1h.2a2 2 0 1 1 0 4h-.2a1.7 1.7 0 0 0-1.5 1z" /></svg>
                        <span class="truncate">Firmware ESP32 v2.4.1</span>
                    </p>
                    <a href="#" class="inline-flex shrink-0 items-center gap-0.5 text-[12px] font-bold text-[#0B6B4A] hover:underline">Panduan Kalibrasi
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M8 7h9v9" /></svg>
                    </a>
                </div>
            </div>
        </aside>
    </div>

    {{-- Modal registrasi ESP32 --}}
    <div x-show="showAdd" x-cloak class="fixed inset-0 z-[80] flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true" aria-label="{{ __('devices.register_title') }}">
        <div class="absolute inset-0 bg-[#0B1F0E]/70 backdrop-blur-sm" @click="showAdd = false"></div>
        <div class="relative w-full max-w-lg rounded-[28px] bg-white p-6 shadow-[0_0_90px_-12px_rgba(20,70,30,0.65)] sm:p-7">
            <div class="flex items-start justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#E9F3E4] text-[#2f5d2a]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9" /><path d="M12 8v8M8 12h8" /></svg>
                    </span>
                    <div class="min-w-0">
                        <h3 class="font-display text-[20px] font-extrabold leading-tight tracking-tight text-[#1E3A1A]">{{ __('devices.register_title') }}</h3>
                        <p class="mt-0.5 text-[12.5px] text-[#6B7263]">{{ __('devices.register_sub') }}</p>
                    </div>
                </div>
                <button type="button" @click="showAdd = false" aria-label="{{ __('app.cancel') }}"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[#1c1c1c] transition hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('devices.store') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="dev-name" class="mb-1.5 block text-[11px] font-extrabold uppercase tracking-[0.08em] text-[#1c1c1c]">{{ __('devices.device_id_label') }}</label>
                    <input id="dev-name" name="name" required maxlength="50" value="{{ old('name') }}"
                        class="w-full rounded-[6px] border border-[#E7E1CC] bg-[#F4F0E1] px-4 py-3.5 text-[14px] font-medium text-[#1c1c1c] placeholder-[#9AA091] outline-none transition focus:border-[#2f5d2a] focus:bg-white focus:ring-2 focus:ring-[#2f5d2a]/20"
                        placeholder="{{ __('devices.name_ph') }}" />
                    @error('name')
                        <p class="mt-1.5 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="dev-loc" class="mb-1.5 block text-[11px] font-extrabold uppercase tracking-[0.08em] text-[#1c1c1c]">{{ __('devices.warehouse_label') }}</label>
                    <input id="dev-loc" name="location" maxlength="150" value="{{ old('location') }}"
                        class="w-full rounded-[6px] border border-[#E7E1CC] bg-[#F4F0E1] px-4 py-3.5 text-[14px] font-medium text-[#1c1c1c] placeholder-[#9AA091] outline-none transition focus:border-[#2f5d2a] focus:bg-white focus:ring-2 focus:ring-[#2f5d2a]/20"
                        placeholder="{{ __('devices.location_ph') }}" />
                </div>
                <div>
                    <p class="mb-1.5 text-[11px] font-extrabold uppercase tracking-[0.08em] text-[#1c1c1c]">{{ __('devices.sensor_array_label') }}</p>
                    <div class="flex items-center justify-between gap-2 rounded-[6px] border border-[#E7E1CC] bg-[#F4F0E1] px-4 py-3.5">
                        <span class="text-[14px] font-medium text-[#1c1c1c]">{{ __('devices.sensor_array_value') }}</span>
                        <span class="shrink-0 text-[14px] font-bold text-[#0B6B4A]">{{ __('devices.sensor_array_std') }}</span>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-5 pt-1">
                    <button type="button" @click="showAdd = false"
                        class="text-[14px] font-medium text-[#1c1c1c] transition hover:text-black">{{ __('app.cancel') }}</button>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-full bg-[#2F5D2A] px-7 py-3 text-[14px] font-bold text-white shadow-[0_10px_24px_-10px_rgba(47,93,42,0.8)] transition hover:brightness-110 active:scale-[0.98]">{{ __('devices.save_device') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfigurasi ESP32 (key penuh hanya sekali) --}}
    @if (!empty($plainKey) && !empty($plainDeviceId))
        @php
            $freshDevice = $devices->firstWhere('id', $plainDeviceId);
            $espApiUrl = url('/api/sensor-data');
            $espRows = [
                ['label' => 'DEVICE_ID', 'value' => (string) ($freshDevice?->device_id ?? ''), 'hint' => null],
                ['label' => 'API_KEY', 'value' => (string) $plainKey, 'hint' => __('devices.api_key_once')],
                ['label' => 'API_URL', 'value' => (string) $espApiUrl, 'hint' => null],
                ['label' => 'WIFI_SSID', 'value' => 'NAMA_WIFI_KAMU', 'hint' => 'Ganti dengan nama WiFi lokasi alat'],
                ['label' => 'WIFI_PASSWORD', 'value' => 'PASSWORD_WIFI_KAMU', 'hint' => 'Ganti dengan password WiFi lokasi alat'],
            ];
        @endphp
        <x-modal name="esp-config" :show="true">
            <div class="flex items-center justify-between gap-2">
                <p class="flex min-w-0 items-center gap-2.5 font-display text-[18px] font-extrabold text-[#1e3a1a]">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E9F3E4] text-[#2f5d2a]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="7" width="16" height="10" rx="2" /><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /><path d="M12 11v2m0 2h.01" /></svg>
                    </span>
                    <span class="truncate">Konfigurasi ESP32</span>
                </p>
                <span class="shrink-0 rounded-full bg-[#1E3A1A] px-3 py-1.5 font-mono text-[10px] font-bold tracking-wide text-[#B8E39A]">{{ $freshDevice?->device_id }}</span>
            </div>

            <div class="mt-4 flex items-start gap-2.5 rounded-[12px] border border-[#EFE7CF] bg-[#FBF7EA] px-3.5 py-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-[#B7791F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z" /></svg>
                <p class="text-[12.5px] leading-relaxed text-[#6B5E2E]"><strong class="font-extrabold">Salin API key ini sekarang.</strong> Key penuh hanya ditampilkan sekali ini.</p>
            </div>

            <div class="mt-3 space-y-2">
                @foreach ($espRows as $row)
                    <div class="flex items-center gap-2 rounded-[12px] border border-[#EFE7CF] bg-[#FBF7EA] px-3 py-2.5" x-data="{ ok: false }">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.1em] text-[#6B8A4E]">{{ $row['label'] }}</p>
                            <p class="mt-0.5 break-all font-mono text-[12px] font-bold text-[#1c1c1c]">{{ $row['value'] }}</p>
                            @if (!empty($row['hint']))
                                <p class="mt-0.5 text-[11px] text-[#9AA091]">{{ $row['hint'] }}</p>
                            @endif
                        </div>
                        <button type="button"
                            @click="navigator.clipboard.writeText(@js($row['value'])); ok = true; setTimeout(() => ok = false, 2000)"
                            class="inline-flex shrink-0 items-center gap-1 rounded-full px-3 py-2 text-[11px] font-bold transition"
                            :class="ok ? 'bg-[#E4F2DF] text-[#0B6B4A]' : 'bg-[#F3F0E2] text-[#3F3F3F] hover:bg-[#E7E2CF]'">
                            <svg x-show="!ok" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2" /><path d="M5 15V5a2 2 0 0 1 2-2h10" /></svg>
                            <svg x-show="ok" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7" /></svg>
                            <span x-text="ok ? @js(__('devices.copied')) : @js(__('devices.copy'))"></span>
                        </button>
                    </div>
                @endforeach
            </div>

            <pre id="esp-snippet" class="hidden">const char* WIFI_SSID     = "NAMA_WIFI_KAMU";
const char* WIFI_PASSWORD = "PASSWORD_WIFI_KAMU";
const char* API_URL       = "{{ $espApiUrl }}";
const char* DEVICE_ID     = "{{ $freshDevice?->device_id }}";
const char* API_KEY       = "{{ $plainKey }}";</pre>

            <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                <div class="rounded-[10px] border border-[#EFE7CF] bg-white px-2 py-2.5">
                    <p class="font-display text-[13px] font-extrabold text-[#2f5d2a]">1</p>
                    <p class="mt-0.5 text-[10.5px] font-semibold leading-tight text-[#6B7263]">Tempel ke firmware</p>
                </div>
                <div class="rounded-[10px] border border-[#EFE7CF] bg-white px-2 py-2.5">
                    <p class="font-display text-[13px] font-extrabold text-[#2f5d2a]">2</p>
                    <p class="mt-0.5 text-[10.5px] font-semibold leading-tight text-[#6B7263]">Sesuaikan WiFi</p>
                </div>
                <div class="rounded-[10px] border border-[#EFE7CF] bg-white px-2 py-2.5">
                    <p class="font-display text-[13px] font-extrabold text-[#2f5d2a]">3</p>
                    <p class="mt-0.5 text-[10.5px] font-semibold leading-tight text-[#6B7263]">Upload ke ESP32</p>
                </div>
            </div>

            <div class="mt-4 flex gap-2">
                <button type="button"
                    @click="navigator.clipboard.writeText(document.getElementById('esp-snippet').innerText); copied = true; setTimeout(() => copied = false, 2000)"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-full bg-[#F3F0E2] px-4 py-3 text-[13px] font-bold text-[#3F3F3F] transition hover:bg-[#E7E2CF]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2" /><path d="M5 15V5a2 2 0 0 1 2-2h10" /></svg>
                    <span x-show="!copied">Salin Semua</span>
                    <span x-show="copied" x-cloak>{{ __('devices.copied') }}</span>
                </button>
                <button type="button" @click="$dispatch('close-modal-esp-config')"
                    class="inline-flex flex-1 items-center justify-center rounded-full bg-[#2F5D2A] px-4 py-3 text-[13px] font-bold text-white shadow-[0_10px_24px_-10px_rgba(47,93,42,0.8)] transition hover:brightness-110">{{ __('devices.close') }}</button>
            </div>
        </x-modal>
    @endif
</div>
@endsection
