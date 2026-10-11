@extends('layouts.app')
@section('title', __('sessions.title'))

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="card card-hover relative overflow-hidden p-6 sm:p-7">
        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cassava-100 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="hidden h-13 w-13 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cassava-500 to-cassava-800 p-3.5 text-white shadow-lg sm:flex">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6v4l4 11a2 2 0 0 1-1.8 2.7H6.8A2 2 0 0 1 5 18L9 7V3Z"/><path d="M7.5 14h9"/></svg>
                </div>
                <div>
                    <p class="eyebrow text-cassava-600">{{ __('sessions.collection') }}</p>
                    <h2 class="mt-1 text-xl font-extrabold tracking-tight text-gray-900 sm:text-2xl">{{ __('sessions.title') }}</h2>
                    <p class="mt-1 text-[13px] text-gray-500">{{ __('sessions.subtitle') }}</p>
                </div>
            </div>
            <a href="{{ route('sessions.export-csv') }}" class="btn-primary w-full sm:w-auto">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5"/><path d="M4 19h16"/></svg>
                {{ __('sessions.export_full') }}
            </a>
        </div>
    </div>

    {{-- ACTIVE SESSION BANNER --}}
    @if ($activeSession)
    <div class="relative overflow-hidden rounded-3xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-lime-50 p-5 sm:p-6 flex flex-col gap-4 shadow-[var(--shadow-card)] sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-4">
            <span class="relative mt-1 flex h-3 w-3 shrink-0">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-500"></span>
            </span>
            <div>
                <p class="eyebrow text-emerald-700">{{ __('sessions.active_running') }}</p>
                <p class="mt-1 font-bold text-gray-900">{{ $activeSession->name }}</p>
                <p class="text-[13px] text-gray-600 mt-1">
                    Device: <strong class="font-mono">{{ $activeSession->device->device_id }}</strong>
                    <span class="mx-1 text-gray-400">|</span>
                    Label: <strong class="text-emerald-700">{{ $activeSession->cassava_condition?->label() }}</strong>
                    <span class="mx-1 text-gray-400">|</span>
                    Dimulai: {{ $activeSession->started_at->translatedFormat('d M Y H:i:s') }}
                </p>
            </div>
        </div>
        <form method="POST" action="{{ route('sessions.end', $activeSession) }}">
            @csrf
            @method('PATCH')
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-red-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-red-500/30 transition hover:bg-red-600 active:scale-[0.98]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                {{ __('sessions.end') }}
            </button>
        </form>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- FORM MULAI SESI --}}
        <div class="lg:col-span-1">
            <div class="card card-hover p-6">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cassava-950 text-lime-300">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900">{{ __('sessions.start_new') }}</h3>
                </div>

                <form method="POST" action="{{ route('sessions.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="eyebrow mb-1.5 block text-gray-500">{{ __('sessions.device') }}</label>
                        <select name="device_id" required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm outline-none transition focus:border-cassava-400 focus:bg-white focus:ring-4 focus:ring-cassava-100">
                            @foreach ($devices as $device)
                                <option value="{{ $device->device_id }}">{{ $device->device_id }} – {{ $device->name }}</option>
                            @endforeach
                        </select>
                        @error('device_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="eyebrow mb-1.5 block text-gray-500">{{ __('sessions.name') }}</label>
                        <input type="text" name="name" required
                               placeholder="{{ __('sessions.name_ph') }}"
                               value="{{ old('name') }}"
                               class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm outline-none transition placeholder:text-gray-400 focus:border-cassava-400 focus:bg-white focus:ring-4 focus:ring-cassava-100" />
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="eyebrow mb-1.5 block text-gray-500">{{ __('sessions.condition') }}</label>
                        <select name="cassava_condition" required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm outline-none transition focus:border-cassava-400 focus:bg-white focus:ring-4 focus:ring-cassava-100">
                            @foreach ($conditions as $value => $label)
                                <option value="{{ $value }}" {{ old('cassava_condition') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('cassava_condition') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="eyebrow mb-1.5 block text-gray-500">{{ __('sessions.notes') }}</label>
                        <textarea name="notes" rows="3" placeholder="{{ __('sessions.notes_ph') }}"
                                  class="w-full resize-none rounded-2xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm outline-none transition placeholder:text-gray-400 focus:border-cassava-400 focus:bg-white focus:ring-4 focus:ring-cassava-100">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full !py-3">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                        {{ __('sessions.start_record') }}
                    </button>
                </form>

                <div class="mt-4 flex gap-2.5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs leading-relaxed text-amber-800">
                    <svg class="h-4 w-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.5"/></svg>
                    <p><strong>{{ __('sessions.how') }}</strong> {{ __('sessions.how_desc') }}</p>
                </div>
            </div>
        </div>

        {{-- DAFTAR SESI --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 class="font-bold text-gray-900">{{ __('sessions.list') }}</h3>
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-bold text-gray-600">{{ __('sessions.count', ['n' => $sessions->total()]) }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-shell min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-100">
                                <th>Nama Sesi</th>
                                <th>Label</th>
                                <th>Waktu</th>
                                <th class="!text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($sessions as $session)
                            @php
                                $cond = $session->cassava_condition;
                                $badge = match($cond?->value) {
                                    'fresh' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
                                    'stale' => 'bg-amber-100 text-amber-800 ring-amber-200',
                                    'rotten' => 'bg-red-100 text-red-800 ring-red-200',
                                    default => 'bg-gray-100 text-gray-700 ring-gray-200',
                                };
                            @endphp
                            <tr class="hover:bg-cassava-50/50 transition-colors">
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-gray-900">{{ $session->name }}</p>
                                    <p class="mt-0.5 font-mono text-[11px] text-gray-400">{{ $session->device->device_id }}</p>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($cond)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $badge }}">
                                        {{ $cond->label() }}
                                    </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-xs text-gray-500 tabular-nums">
                                    <p>{{ __('sessions.started') }}: {{ $session->started_at->translatedFormat('d M H:i') }}</p>
                                    <p class="mt-0.5">{{ $session->ended_at ? __('sessions.finished') . ': ' . $session->ended_at->translatedFormat('d M H:i') : __('sessions.running') }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('sessions.show', $session) }}"
                                       class="inline-flex items-center gap-1 rounded-xl bg-gray-900 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-cassava-700">{{ __('sessions.detail_btn') }}
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center">
                                    <p class="text-sm font-semibold text-gray-500">{{ __('sessions.empty_short') }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ __('sessions.empty_hint') }}</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($sessions->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/60">
                    {{ $sessions->links() }}
                </div>
                @endif
            </div>

            <!-- Info Card -->
            <div class="rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-50 to-blue-50 p-6 text-sm text-sky-900 shadow-[var(--shadow-card)]">
                <p class="mb-3 flex items-center gap-2 font-bold">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-sky-500 text-white">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 11l3 3 8-8"/><path d="M21 12v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h11"/></svg>
                    </span>
                    {{ __('sessions.guide_title') }}
                </p>
                <ol class="list-decimal list-inside space-y-1.5 text-[13px] leading-relaxed text-sky-800">
                    <li>Siapkan singkong dengan kondisi yang sudah diketahui (segar / mulai busuk / busuk)</li>
                    <li>Letakkan singkong di dekat sensor E-CASSAVA</li>
                    <li>Klik "Mulai Rekam Data" dengan label kondisi yang sesuai</li>
                    <li>Biarkan sensor merekam selama minimal 2–5 menit</li>
                    <li>Klik "Akhiri Sesi" setelah selesai</li>
                    <li>Ulangi untuk kondisi berbeda, minimal 30 sesi per kelas</li>
                    <li>Export CSV dan gunakan untuk training model di Python</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
