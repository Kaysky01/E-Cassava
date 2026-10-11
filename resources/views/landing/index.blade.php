@extends('layouts.landing')

@section('title', __('landing.meta_title'))
@section('meta_description', __('landing.meta_description'))

@section('content')
<div class="space-y-14 sm:space-y-20">

    {{-- HERO --}}
    <section class="mx-auto grid max-w-7xl items-center gap-8 px-4 pt-8 sm:gap-10 sm:px-6 sm:pt-12 lg:grid-cols-2 lg:px-8">
        <div class="animate-fade-up">
            <span class="inline-flex items-center gap-2 rounded-full border border-cassava-200 bg-white px-4 py-1.5 text-xs font-bold text-cassava-700 shadow-sm">
                <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                {{ __('landing.hero_badge') }}
            </span>
            <h1 class="mt-5 break-words font-display text-[1.75rem] font-extrabold leading-tight tracking-tight sm:text-5xl">
                {{ __('landing.hero_title') }}
            </h1>
            <p class="mt-4 max-w-xl text-base leading-relaxed text-gray-600 sm:text-lg">
                {{ __('landing.hero_subtitle') }}
            </p>
            <div class="mt-6 grid grid-cols-1 gap-3 min-[420px]:flex min-[420px]:flex-wrap sm:mt-7">
                <a href="{{ route('register') }}" class="btn-primary">{{ __('landing.cta_start') }}</a>
                <a href="#demo" class="btn-ghost">{{ __('landing.cta_demo') }}</a>
            </div>
            <p class="mt-4 text-sm text-gray-500">{{ __('landing.hero_note') }}</p>
        </div>

        {{-- Mockup dashboard (SVG/CSS murni) --}}
        <div class="animate-fade-up" aria-hidden="true">
            <div class="card relative overflow-hidden p-5">
                <div class="h-1.5 rounded-full bg-gradient-to-r from-emerald-400 via-lime-400 to-cassava-500"></div>
                <div class="mt-4 flex items-center justify-between">
                    <p class="font-display text-sm font-extrabold">{{ __('landing.mock_ai') }}</p>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-bold text-emerald-700"><span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>{{ __('landing.mock_live') }}</span>
                </div>
                <div class="mt-4 flex items-center gap-4 rounded-2xl bg-gradient-to-br from-gray-50 to-white p-4">
                    <div class="relative h-24 w-24 shrink-0">
                        <svg viewBox="0 0 120 120" class="h-24 w-24 -rotate-90">
                            <circle cx="60" cy="60" r="54" fill="none" stroke-width="12" class="stroke-gray-200" />
                            <circle cx="60" cy="60" r="54" fill="none" stroke="#16a34a" stroke-width="12" stroke-linecap="round" stroke-dasharray="339.3" stroke-dashoffset="40" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center font-display text-lg font-extrabold">92%</div>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-4 py-2 text-sm font-bold text-emerald-800">{{ __('app.fresh') }}</span>
                        <p class="mt-2 font-mono text-xs text-gray-500">MQ-2 1180 • MQ-3 840 • MQ-135 990</p>
                    </div>
                </div>
                <svg viewBox="0 0 400 120" class="mt-4 w-full rounded-2xl bg-gray-50 p-2" preserveAspectRatio="none">
                    <polyline points="0,90 40,82 80,86 120,70 160,74 200,60 240,64 280,50 320,54 360,40 400,44" fill="none" stroke="#16a34a" stroke-width="3" stroke-linecap="round" />
                    <polyline points="0,100 40,96 80,98 120,88 160,90 200,80 240,82 280,72 320,74 360,66 400,68" fill="none" stroke="#0ea5e9" stroke-width="3" stroke-linecap="round" />
                    <polyline points="0,95 40,92 80,90 120,84 160,80 200,76 240,70 280,66 320,60 360,58 400,52" fill="none" stroke="#8b5cf6" stroke-width="3" stroke-linecap="round" />
                </svg>
                <div class="mt-3 flex gap-2 text-[11px] font-bold">
                    <span class="rounded-full bg-orange-50 px-3 py-1 text-orange-700">MQ-2</span>
                    <span class="rounded-full bg-sky-50 px-3 py-1 text-sky-700">MQ-3</span>
                    <span class="rounded-full bg-violet-50 px-3 py-1 text-violet-700">MQ-135</span>
                </div>
            </div>
        </div>
    </section>

    {{-- MASALAH --}}
    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="break-words font-display text-2xl font-extrabold tracking-tight sm:text-3xl">{{ __('landing.problems_title') }}</h2>
            <p class="mx-auto mt-3 max-w-2xl text-gray-600">{{ __('landing.problems_subtitle') }}</p>
        </div>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach ([[__('landing.problem_1_title'), __('landing.problem_1_desc')], [__('landing.problem_2_title'), __('landing.problem_2_desc')], [__('landing.problem_3_title'), __('landing.problem_3_desc')]] as [$t, $d])
                <div class="card card-hover p-6">
                    <h3 class="font-display text-lg font-bold">{{ $t }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $d }}</p>
                </div>
            @endforeach
        </div>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <x-stat-tile :label="__('landing.tile_1_title')" value="×N" :sub="__('landing.tile_1_desc')" />
            <x-stat-tile :label="__('landing.tile_2_title')" value="" :sub="__('landing.tile_2_desc')" />
            <x-stat-tile :label="__('landing.tile_3_title')" value="+" :sub="__('landing.tile_3_desc')" />
        </div>
    </section>

    {{-- CARA KERJA --}}
    <section id="cara-kerja" class="bg-cassava-950 py-16 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="break-words font-display text-2xl font-extrabold tracking-tight sm:text-3xl">{{ __('landing.how_title') }}</h2>
                <p class="mt-3 text-cassava-200">{{ __('landing.how_subtitle') }}</p>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ([['1', __('landing.step_1_title'), __('landing.step_1_desc')], ['2', __('landing.step_2_title'), __('landing.step_2_desc')], ['3', __('landing.step_3_title'), __('landing.step_3_desc')]] as [$n, $t, $d])
                    <div class="relative rounded-3xl bg-white/5 p-6 ring-1 ring-white/10 backdrop-blur transition duration-300 hover:-translate-y-1 hover:bg-white/10">
                        <span class="absolute -top-4 left-6 flex h-8 w-8 items-center justify-center rounded-full bg-lime-400 font-display text-sm font-extrabold text-cassava-950">{{ $n }}</span>
                        <h3 class="mt-4 font-display text-lg font-bold">{{ $t }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-cassava-100">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mx-auto mt-8 flex max-w-3xl flex-wrap items-center justify-center gap-2 text-sm font-bold">
                <span class="rounded-full bg-emerald-400/20 px-4 py-2 ring-1 ring-emerald-300/30">{{ __('app.fresh') }}</span>
                <span class="text-cassava-300">→</span>
                <span class="rounded-full bg-amber-400/20 px-4 py-2 ring-1 ring-amber-300/30">{{ __('app.stale') }}</span>
                <span class="text-cassava-300">→</span>
                <span class="rounded-full bg-red-400/20 px-4 py-2 ring-1 ring-red-300/30">{{ __('app.rotten') }}</span>
            </div>
        </div>
    </section>

    {{-- FITUR --}}
    <section id="fitur" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="break-words font-display text-2xl font-extrabold tracking-tight sm:text-3xl">{{ __('landing.features_title') }}</h2>
            <p class="mt-3 text-gray-600">{{ __('landing.features_subtitle') }}</p>
        </div>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([[__('landing.f_realtime_title'), __('landing.f_realtime_desc')], [__('landing.f_ai_title'), __('landing.f_ai_desc')], [__('landing.f_multi_title'), __('landing.f_multi_desc')], [__('landing.f_alert_title'), __('landing.f_alert_desc')], [__('landing.f_insight_title'), __('landing.f_insight_desc')], [__('landing.f_export_title'), __('landing.f_export_desc')], [__('landing.f_lang_title'), __('landing.f_lang_desc')]] as [$t, $d])
                <div class="card card-hover p-6">
                    <h3 class="font-display text-base font-bold">{{ $t }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $d }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- DEMO INTERAKTIF --}}
    <section id="demo" class="mx-auto max-w-4xl px-4 sm:px-6">
        <div class="text-center">
            <h2 class="break-words font-display text-2xl font-extrabold tracking-tight sm:text-3xl">{{ __('landing.demo_title') }}</h2>
            <p class="mt-3 text-gray-600">{{ __('landing.demo_subtitle') }}</p>
        </div>
        <div class="card mt-8 overflow-hidden" x-data="{
            tab: 'fresh',
            cases: {
                fresh: { conf: 92.4, probs: { fresh: 92.4, stale: 5.1, rotten: 2.5 }, color: '#16a34a', badge: 'bg-emerald-100 text-emerald-800', label: @js(__('app.fresh')), desc: @js(__('landing.demo_fresh_desc')) },
                stale: { conf: 78.6, probs: { fresh: 12.3, stale: 78.6, rotten: 9.1 }, color: '#f59e0b', badge: 'bg-amber-100 text-amber-800', label: @js(__('app.stale')), desc: @js(__('landing.demo_stale_desc')) },
                rotten: { conf: 88.9, probs: { fresh: 3.2, stale: 7.9, rotten: 88.9 }, color: '#ef4444', badge: 'bg-red-100 text-red-800', label: @js(__('app.rotten')), desc: @js(__('landing.demo_rotten_desc')) },
            }
        }">
            <div class="h-1.5 bg-gradient-to-r from-emerald-400 via-lime-400 to-cassava-500"></div>
            <div class="p-6 sm:p-8">
                <div class="flex flex-wrap gap-2" role="tablist" aria-label="Demo">
                    <button @click="tab = 'fresh'" :class="tab === 'fresh' ? 'bg-cassava-600 text-white' : 'bg-gray-100 text-gray-700'" class="min-w-[100px] flex-1 whitespace-nowrap rounded-2xl px-4 py-3 text-base font-bold transition" role="tab" :aria-selected="tab === 'fresh'">{{ __('app.fresh') }}</button>
                    <button @click="tab = 'stale'" :class="tab === 'stale' ? 'bg-cassava-600 text-white' : 'bg-gray-100 text-gray-700'" class="min-w-[100px] flex-1 whitespace-nowrap rounded-2xl px-4 py-3 text-base font-bold transition" role="tab" :aria-selected="tab === 'stale'">{{ __('app.stale') }}</button>
                    <button @click="tab = 'rotten'" :class="tab === 'rotten' ? 'bg-cassava-600 text-white' : 'bg-gray-100 text-gray-700'" class="min-w-[100px] flex-1 whitespace-nowrap rounded-2xl px-4 py-3 text-base font-bold transition" role="tab" :aria-selected="tab === 'rotten'">{{ __('app.rotten') }}</button>
                </div>
                <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row">
                    <div class="relative h-32 w-32 shrink-0">
                        <svg viewBox="0 0 120 120" class="h-32 w-32 -rotate-90">
                            <circle cx="60" cy="60" r="54" fill="none" stroke-width="12" class="stroke-gray-200" />
                            <circle cx="60" cy="60" r="54" fill="none" :stroke="cases[tab].color" stroke-width="12" stroke-linecap="round"
                                stroke-dasharray="339.3" :stroke-dashoffset="339.3 * (1 - cases[tab].conf / 100)"
                                style="transition: stroke-dashoffset .6s ease, stroke .6s ease;" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-display text-2xl font-extrabold tabular-nums" x-text="cases[tab].conf + '%'"></span>
                            <span class="text-[11px] uppercase tracking-wider text-gray-400">{{ __('landing.demo_confidence') }}</span>
                        </div>
                    </div>
                    <div class="w-full">
                        <span :class="cases[tab].badge" class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-lg font-bold">
                            <span x-text="cases[tab].label"></span>
                        </span>
                        <p class="mt-3 text-base leading-relaxed text-gray-600" x-text="cases[tab].desc"></p>
                    </div>
                </div>
                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <template x-for="(val, key) in cases[tab].probs" :key="key">
                        <div class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-sm">
                            <div class="mb-2 flex justify-between text-xs">
                                <span class="font-bold" x-text="cases[key].label"></span>
                                <span class="font-mono font-bold" x-text="val + '%'"></span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-2.5 rounded-full transition-all duration-700" :style="'width:' + val + '%; background:' + cases[key].color"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>

    {{-- TEKNOLOGI + CTA --}}
    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="break-words font-display text-2xl font-extrabold tracking-tight sm:text-3xl">{{ __('landing.tech_title') }}</h2>
            <p class="mt-3 text-gray-600">{{ __('landing.tech_subtitle') }}</p>
        </div>
        <div class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([['ESP32', 'MQ-2 • MQ-3 • MQ-135'], ['Laravel', 'REST API • MySQL'], ['SVM', 'Support Vector Machine'], ['FastAPI', 'Python ML service :8001']] as [$t, $d])
                <div class="card card-hover p-6 text-center">
                    <h3 class="font-display text-lg font-extrabold">{{ $t }}</h3>
                    <p class="mt-1 font-mono text-xs text-gray-500">{{ $d }}</p>
                </div>
            @endforeach
        </div>
        <div class="card relative mt-10 overflow-hidden p-8 text-center sm:p-12">
            <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-cassava-200/60 blur-3xl"></div>
            <h2 class="break-words font-display text-2xl font-extrabold tracking-tight sm:text-3xl">{{ __('landing.cta_bottom_title') }}</h2>
            <p class="mx-auto mt-3 max-w-xl text-gray-600">{{ __('landing.cta_bottom_desc') }}</p>
            <a href="{{ route('register') }}" class="btn-primary mt-6 w-full !px-8 !py-4 !text-lg min-[420px]:w-auto">{{ __('landing.cta_bottom_button') }}</a>
        </div>
    </section>
</div>
@endsection
