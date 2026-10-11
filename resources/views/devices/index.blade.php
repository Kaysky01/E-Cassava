@extends('layouts.app')
@section('title', __('devices.title'))

@section('content')
<div class="space-y-6" x-data="{ showAdd: false, copied: false }">

    <div class="card card-hover relative overflow-hidden p-6 sm:p-7">
        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cassava-100 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="eyebrow text-cassava-600">Multi-Gudang</p>
                <h2 class="mt-1 font-display text-xl font-extrabold tracking-tight sm:text-2xl">{{ __('devices.title') }}</h2>
                <p class="mt-1 max-w-xl text-[13px] text-gray-500">{{ __('devices.subtitle') }}</p>
            </div>
            <button @click="showAdd = true" class="btn-primary w-full sm:w-auto">{{ __('devices.add') }}</button>
        </div>
    </div>

    @if ($devices->isEmpty())
        <x-empty-state :title="__('devices.empty_title')" :desc="__('devices.empty_desc')">
            <button @click="showAdd = true" class="btn-primary mt-2">{{ __('devices.add') }}</button>
        </x-empty-state>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($devices as $device)
                @php
                    $pred = $device->latestPrediction;
                    $reading = $device->latestReading;
                @endphp
                <div class="card card-hover p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-display text-lg font-extrabold">{{ $device->name }}</h3>
                            <p class="mt-0.5 text-sm text-gray-500">{{ $device->location ?? '—' }}</p>
                            <p class="mt-1 inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-2.5 py-1 font-mono text-[11px] font-semibold text-lime-300">{{ $device->device_id }}</p>
                        </div>
                        <x-badge-status :label="$device->is_online ? 'online' : 'offline'" />
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                        @foreach ([['MQ-2', $reading?->mq2], ['MQ-3', $reading?->mq3], ['MQ-135', $reading?->mq135]] as [$nm, $val])
                            <div class="rounded-2xl bg-gray-50 px-2 py-2.5 ring-1 ring-gray-100">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $nm }}</p>
                                <p class="font-mono text-base font-extrabold tabular-nums">{{ $val ?? '—' }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                        <span>{{ __('devices.last_seen') }}: <strong class="tabular-nums">{{ $device->last_seen_at?->translatedFormat('d M H:i') ?? __('devices.never') }}</strong></span>
                        @if ($pred)
                            <span>{{ __('devices.last_prediction') }}: <x-badge-status :label="$pred->predicted_label->value" size="sm" /></span>
                        @else
                            <span>{{ __('devices.last_prediction') }}: —</span>
                        @endif
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                        @if ($device->is_active)
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 font-bold text-emerald-700">{{ __('devices.status_active') }}</span>
                        @else
                            <span class="rounded-full bg-gray-200 px-2.5 py-1 font-bold text-gray-600">{{ __('devices.status_inactive') }}</span>
                        @endif
                        @if ($device->api_key_hint)
                            <span class="font-mono rounded-full bg-gray-100 px-2.5 py-1 text-gray-500">…{{ $device->api_key_hint }}</span>
                        @endif
                        <span class="rounded-full bg-cassava-50 px-2.5 py-1 font-semibold text-cassava-700">{{ $device->sensor_readings_count }} data</span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2 border-t border-gray-100 pt-4 text-sm">
                        <a href="{{ route('devices.edit', $device) }}" class="btn-ghost !px-4 !py-2.5 !text-sm">{{ __('app.edit') }}</a>
                        <form method="POST" action="{{ route('devices.toggle', $device) }}">
                            @csrf
                            <button class="btn-ghost !px-4 !py-2.5 !text-sm">{{ $device->is_active ? __('devices.deactivate') : __('devices.activate') }}</button>
                        </form>
                        <form method="POST" action="{{ route('devices.regenerate', $device) }}" onsubmit="return confirm(@js(__('devices.regenerate_confirm')))">
                            @csrf
                            <button class="btn-ghost !px-4 !py-2.5 !text-sm">{{ __('devices.regenerate') }}</button>
                        </form>
                        <form method="POST" action="{{ route('devices.destroy', $device) }}" onsubmit="return confirm(@js(__('devices.delete_confirm')))">
                            @csrf
                            @method('DELETE')
                            <button class="inline-flex items-center gap-1.5 rounded-2xl bg-red-50 px-4 py-2 text-sm font-bold text-red-600 ring-1 ring-inset ring-red-200 transition hover:bg-red-100">{{ __('app.delete') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal tambah alat --}}
    <div x-show="showAdd" x-cloak class="fixed inset-0 z-[80] flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true" aria-label="{{ __('devices.add_title') }}">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showAdd = false"></div>
        <div class="card relative w-full max-w-lg p-6 sm:p-7">
            <h3 class="font-display text-xl font-extrabold">{{ __('devices.add_title') }}</h3>
            <form method="POST" action="{{ route('devices.store') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="dev-name" class="label-shell">{{ __('devices.name') }}</label>
                    <input id="dev-name" name="name" required maxlength="100" class="input-shell" placeholder="{{ __('devices.name_ph') }}" />
                </div>
                <div>
                    <label for="dev-loc" class="label-shell">{{ __('devices.location') }}</label>
                    <input id="dev-loc" name="location" maxlength="150" class="input-shell" placeholder="{{ __('devices.location_ph') }}" />
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
