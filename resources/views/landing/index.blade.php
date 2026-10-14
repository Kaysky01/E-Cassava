@extends('layouts.landing')

@section('title', __('landing.meta_title'))
@section('meta_description', __('landing.meta_description'))

@section('content')
<div class="bg-[#f1f4e8] pb-16">

    {{-- ══════════ HERO ══════════ --}}
    <section class="relative -mt-[68px] overflow-hidden bg-[#101d0d] pt-[68px]">
        {{-- Background foto ladang + gunung, full-bleed --}}
        <img src="{{ asset('images/hero-bg.jpg') }}"
            onerror="this.onerror=null;this.src='{{ asset('images/login-bg.jpg') }}';"
            alt="" aria-hidden="true"
            class="absolute inset-0 h-full w-full object-cover object-[center_35%]" />
        {{-- Overlay gelap kiri → terang kanan + fade bawah untuk pill mengambang --}}
        <div class="absolute inset-0 bg-gradient-to-r from-[#0a140a]/85 via-[#0a140a]/55 to-[#0a140a]/15"></div>
        <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-[#0a140a]/60 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-[#0a140a]/70 to-transparent"></div>

        <div class="relative mx-auto grid w-full max-w-6xl items-center gap-10 px-4 pb-28 pt-10 sm:px-6 lg:grid-cols-[1.08fr_0.92fr] lg:pb-32 lg:pt-14">
            <div class="max-w-xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/95 px-4 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.18em] text-[#1e3a1a] shadow">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#3f7a34]"></span>{{ __('landing.hero_eyebrow') }}
                </span>
                <h1 class="mt-5 text-balance break-words font-display text-[30px] font-extrabold leading-[1.1] tracking-tight text-white min-[420px]:text-[34px] sm:text-[48px]">
                    {!! str_replace('sulit ditebak?', '<em class="font-serif font-medium italic underline decoration-white/70 decoration-2 underline-offset-8">sulit ditebak?</em>', e(__('landing.hero_title'))) !!}
                </h1>
                <p class="mt-4 max-w-md text-[14.5px] leading-relaxed text-white/75">{{ __('landing.hero_subtitle') }}</p>
                <div class="mt-7 flex flex-col items-stretch gap-3 min-[420px]:flex-row min-[420px]:items-center">
                    <a href="{{ route('register') }}" class="rounded-[12px] bg-[#2f5d2a] px-6 py-3 text-center text-sm font-bold text-white shadow-lg transition hover:bg-[#264a22]">{{ __('landing.cta_start') }}</a>
                    <a href="#demo" class="rounded-[12px] bg-white px-6 py-3 text-center text-sm font-bold text-[#1e3a1a] shadow transition hover:bg-emerald-50">{{ __('landing.cta_demo') }}</a>
                </div>
                <p class="mt-5 inline-flex max-w-full items-start gap-2 rounded-2xl border border-white/20 bg-black/30 px-4 py-2 text-xs leading-relaxed text-white/80 backdrop-blur min-[420px]:items-center min-[420px]:rounded-full min-[420px]:py-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-3.5 w-3.5 shrink-0 min-[420px]:mt-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M12 8v5m0 3h.01" /></svg>
                    <span>{{ __('landing.hero_note') }}</span>
                </p>
            </div>

            {{-- Kartu mockup sensor live — persis referensi --}}
            <div class="w-full max-w-[430px] justify-self-center rounded-[28px] bg-white p-4 shadow-2xl min-[420px]:p-6 lg:justify-self-end sm:p-7" aria-hidden="true">
                <div class="flex flex-col gap-2.5 min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between">
                    <p class="flex items-center gap-2 text-[13px] font-extrabold uppercase tracking-wide text-[#2f6b26] min-[420px]:gap-2.5 min-[420px]:text-[15px]">
                        <span class="h-3 w-3 shrink-0 rounded-full bg-[#2f6b26] min-[420px]:h-3.5 min-[420px]:w-3.5"></span>{{ __('landing.mock_title') }}
                    </p>
                    <span class="self-start whitespace-nowrap rounded-full bg-[#f0f7e6] px-3.5 py-1.5 text-[12px] font-medium text-[#5a7a4e] min-[420px]:self-auto min-[420px]:text-[13px]">{{ __('landing.mock_warehouse') }}</span>
                </div>
                <div class="mt-4 rounded-[20px] bg-[#fdfbf0] px-4 pb-4 pt-3 min-[420px]:mt-5 min-[420px]:px-5 min-[420px]:pb-5 min-[420px]:pt-4">
                    <div class="flex items-start justify-between gap-2 text-[10px] font-bold uppercase tracking-wide text-[#6b6a5e] min-[420px]:items-center min-[420px]:text-[12px]">
                        <span>{{ __('landing.mock_chart_y') }}</span><span class="shrink-0">{{ __('landing.mock_chart_x') }}</span>
                    </div>
                    <svg viewBox="0 0 400 150" class="mt-2 w-full" preserveAspectRatio="none" style="height:132px">
                        <g stroke="#e7e2d2" stroke-width="1" stroke-dasharray="5 5">
                            <line x1="0" y1="20" x2="400" y2="20" /><line x1="0" y1="65" x2="400" y2="65" /><line x1="0" y1="110" x2="400" y2="110" />
                        </g>
                        <path d="M0,60 C80,55 160,55 240,55 C310,55 350,50 400,48" fill="none" stroke="#a9c49b" stroke-width="3.5" stroke-linecap="round" />
                        <path d="M0,75 C60,70 90,68 130,62 C170,56 200,52 240,40 C280,28 330,22 392,24" fill="none" stroke="#1e5c1e" stroke-width="4" stroke-linecap="round" />
                        <path d="M0,100 C50,95 90,88 140,90 C190,92 230,95 280,82 C320,72 360,72 400,82" fill="none" stroke="#8b5a3c" stroke-width="3.5" stroke-linecap="round" />
                        <circle cx="392" cy="24" r="7" fill="#1e5c1e" />
                    </svg>
                    <div class="mt-3 grid grid-cols-1 items-start gap-x-6 gap-y-2 text-[12px] font-semibold text-[#33312a] min-[420px]:grid-cols-[1fr_auto] min-[420px]:text-[13px]">
                        <span class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-full bg-[#1e5c1e]"></span><span class="min-w-0">MQ-3 ({{ __('landing.mock_mq3') }})</span></span>
                        <span class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-full bg-[#a9c49b]"></span><span class="min-w-0">MQ-135 ({{ __('landing.mock_mq135') }})</span></span>
                        <span class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-full bg-[#8b5a3c]"></span><span class="min-w-0">MQ-2 ({{ __('landing.mock_mq2') }})</span></span>
                    </div>
                </div>
                <div class="mt-4 flex flex-col gap-3 rounded-[20px] border border-[#cde3b8] bg-[#e9f3dd] px-4 py-4 min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between min-[420px]:px-5">
                    <div class="flex min-w-0 items-center gap-3.5">
                        <span class="block h-14 w-14 shrink-0 overflow-hidden rounded-full shadow-sm">
                            <img src="{{ asset('images/logo.png') }}" alt="E-Cassava" class="block h-full w-full rounded-full object-cover" />
                        </span>
                        <div class="min-w-0">
                            <p class="font-display text-[22px] font-extrabold leading-none text-[#2e6b25] min-[420px]:text-[24px]">92.4% {{ __('app.fresh') }}</p>
                            <p class="mt-1.5 text-[12px] font-medium leading-snug text-[#3d3d35]">{{ __('landing.mock_result_sub') }}</p>
                        </div>
                    </div>
                    <span class="self-start shrink-0 rounded-full bg-[#2f5d2a] px-4 py-1.5 text-[12px] font-extrabold uppercase tracking-wider text-white min-[420px]:self-auto">{{ __('landing.mock_optimal') }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════ BILAH INFO MENGAMBANG (menumpuk batas hero) ══════════ --}}
    <div class="relative z-10 mx-auto -mt-12 w-full max-w-4xl px-4 sm:px-6">
        <div class="flex flex-col items-stretch gap-3 rounded-[24px] bg-white px-5 py-4 shadow-[0_18px_50px_-16px_rgba(30,58,26,0.35)] ring-1 ring-black/5 sm:flex-row sm:items-center sm:gap-0 sm:rounded-full sm:py-2.5 sm:pl-6 sm:pr-2.5">
            <div class="flex flex-1 items-center gap-3 sm:px-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#e9f1dc] text-[#2f5d2a]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none" /><path d="M8.5 8.5a5 5 0 0 0 0 7M15.5 8.5a5 5 0 0 1 0 7" /><path d="M6 6a8.5 8.5 0 0 0 0 12M18 6a8.5 8.5 0 0 1 0 12" /></svg>
                </span>
                <span><span class="block text-[12.5px] font-extrabold leading-tight text-[#1e3a1a]">{{ __('landing.fb_1_t') }}</span><span class="block text-[11px] text-gray-400">{{ __('landing.fb_1_d') }}</span></span>
            </div>
            <div class="flex flex-1 items-center gap-3 sm:border-l sm:border-gray-100 sm:px-6">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#e9f1dc] text-[#2f5d2a]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </span>
                <span><span class="block text-[12.5px] font-extrabold leading-tight text-[#1e3a1a]">{{ __('landing.fb_2_t') }}</span><span class="block text-[11px] text-gray-400">{{ __('landing.fb_2_d') }}</span></span>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2.5 sm:flex-nowrap sm:justify-end sm:pl-4">
                <span class="whitespace-nowrap rounded-full bg-[#eef4e4] px-3.5 py-1.5 text-[12px] font-bold text-[#2f5d2a]">{{ __('landing.fb_3_t') }}</span>
                <a href="{{ route('register') }}" class="whitespace-nowrap rounded-full bg-[#2f5d2a] px-4 py-2.5 text-[13px] font-bold text-white transition hover:bg-[#234d1d] sm:px-5">{{ __('landing.cta_start') }}</a>
            </div>
        </div>
    </div>

    {{-- ══════════ TANTANGAN ══════════ --}}
    <section class="mx-auto w-full max-w-6xl px-4 pt-14 sm:px-6">
            <div class="text-center">
            <span class="inline-block rounded-full border border-[#d8e2c8] bg-white px-4 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.2em] text-[#5a7a4e]">{{ __('landing.eyebrow_problems') }}</span>
            <h2 class="mx-auto mt-4 max-w-xl text-balance break-words font-display text-[22px] font-extrabold tracking-tight text-[#1e3a1a] min-[420px]:text-2xl sm:text-[32px] sm:leading-tight">{{ __('landing.problems_title') }}</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-gray-500">{{ __('landing.problems_subtitle') }}</p>
        </div>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach ([
                ['icon' => '<path d="M7 2h10M7 22h10M8 2v4.5L12 11l4-4.5V2M8 22v-4.5L12 13l4 4.5V22" />', 't' => __('landing.problem_1_title'), 'd' => __('landing.problem_1_desc'), 'f' => __('landing.problem_1_tag')],
                ['icon' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" /><circle cx="12" cy="12" r="3" />', 't' => __('landing.problem_2_title'), 'd' => __('landing.problem_2_desc'), 'f' => __('landing.problem_2_tag')],
                ['icon' => '<path d="M3 17l6-6 4 4 8-8" /><path d="M21 13V7h-6" />', 't' => __('landing.problem_3_title'), 'd' => __('landing.problem_3_desc'), 'f' => __('landing.problem_3_tag')],
            ] as $c)
                <div class="rounded-3xl border border-black/5 bg-white p-6 shadow-[0_10px_30px_-18px_rgba(30,58,26,0.4)]">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#e9f1dc] text-[#2f5d2a]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $c['icon'] !!}</svg>
                    </span>
                    <h3 class="mt-4 font-display text-[17px] font-extrabold leading-snug text-[#1e3a1a]">{{ $c['t'] }}</h3>
                    <p class="mt-2 text-[13px] leading-relaxed text-gray-500">{{ $c['d'] }}</p>
                    <p class="mt-5 border-t border-gray-100 pt-4 text-[11px] text-gray-400">{{ $c['f'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ══════════ CARA KERJA ══════════ --}}
    <section id="cara-kerja" class="mt-14 scroll-mt-24 py-14">
        <div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
            <div class="text-center">
                <span class="inline-block rounded-full border border-[#d8e2c8] bg-white px-4 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.2em] text-[#5a7a4e]">{{ __('landing.eyebrow_how') }}</span>
                <h2 class="mt-4 font-display text-2xl font-extrabold tracking-tight text-[#1e3a1a] sm:text-[32px]">{{ __('landing.how_title') }}</h2>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-gray-500">{{ __('landing.how_subtitle') }}</p>
            </div>
            <div class="mt-8 grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['n' => '1', 'e' => __('landing.step_1_eyebrow'), 't' => __('landing.step_1_title'), 'd' => __('landing.step_1_desc')],
                    ['n' => '2', 'e' => __('landing.step_2_eyebrow'), 't' => __('landing.step_2_title'), 'd' => __('landing.step_2_desc')],
                    ['n' => '3', 'e' => __('landing.step_3_eyebrow'), 't' => __('landing.step_3_title'), 'd' => __('landing.step_3_desc')],
                ] as $s)
                    <div class="rounded-3xl border border-black/5 bg-white p-7 text-center shadow-[0_10px_30px_-18px_rgba(30,58,26,0.4)]">
                        <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-[#2f5d2a] font-display text-lg font-extrabold text-white">{{ $s['n'] }}</span>
                        <p class="mt-4 text-[10px] font-extrabold uppercase tracking-[0.18em] text-gray-400">◉ {{ $s['e'] }}</p>
                        <h3 class="mt-2 font-display text-[17px] font-extrabold text-[#1e3a1a]">{{ $s['t'] }}</h3>
                        <p class="mx-auto mt-2 max-w-[280px] text-[13px] leading-relaxed text-gray-500">{{ $s['d'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════ FITUR ══════════ --}}
    <section id="fitur" class="mx-auto w-full max-w-6xl scroll-mt-24 px-4 sm:px-6">
        <div class="text-center">
            <span class="inline-block rounded-full border border-[#d8e2c8] bg-white px-4 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.2em] text-[#5a7a4e]">{{ __('landing.eyebrow_features') }}</span>
            <h2 class="mt-4 font-display text-2xl font-extrabold tracking-tight text-[#1e3a1a] sm:text-[32px]">{{ __('landing.features_title') }}</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-gray-500">{{ __('landing.features_subtitle') }}</p>
        </div>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['img' => 'images/icon-ai.png', 'icon' => '', 't' => __('landing.f_ai_title'), 'd' => __('landing.f_ai_desc')],
                ['icon' => '<path d="M12 5a5.5 5.5 0 0 0-5.5 5.5V14l-2 2.5h15L17.5 14v-3.5A5.5 5.5 0 0 0 12 5Z" /><path d="M10 19.5a2 2 0 0 0 4 0" /><path d="M4 10a7 7 0 0 0-.8 3M20 10a7 7 0 0 1 .8 3" />', 't' => __('landing.f_alert_title'), 'd' => __('landing.f_alert_desc')],
                ['icon' => '<path d="M9 18h6M10 21h4" /><path d="M12 3a6 6 0 0 0-3.5 10.9c.8.6 1.5 1.4 1.5 2.1h4c0-.7.7-1.5 1.5-2.1A6 6 0 0 0 12 3Z" />', 't' => __('landing.f_insight_title'), 'd' => __('landing.f_insight_desc')],
                ['icon' => '<path d="M3 9.5 12 4l9 5.5" /><path d="M5 8.5V20h14V8.5" /><path d="M9 20v-6h6v6" />', 't' => __('landing.f_multi_title'), 'd' => __('landing.f_multi_desc')],
                ['img' => 'images/prediksiml.png', 'icon' => '', 't' => __('landing.f_pred_title'), 'd' => __('landing.f_pred_desc')],
                ['icon' => '<path d="M3 16l4-5 3 3 5-6 4 3" />', 't' => __('landing.f_chart_title'), 'd' => __('landing.f_chart_desc')],
            ] as $f)
                <div class="flex gap-4 rounded-3xl border border-black/5 bg-white p-6 shadow-[0_10px_30px_-18px_rgba(30,58,26,0.4)]">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#e9f1dc] text-[#2f5d2a]">
                        @if (!empty($f['img']))
                            <img src="{{ asset($f['img']) }}" alt="" class="h-6 w-6 object-contain" />
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $f['icon'] !!}</svg>
                        @endif
                    </span>
                    <span>
                        <span class="block font-display text-[15px] font-extrabold text-[#1e3a1a]">{{ $f['t'] }}</span>
                        <span class="mt-1 block text-[13px] leading-relaxed text-gray-500">{{ $f['d'] }}</span>
                    </span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ══════════ DEMO ══════════ --}}
    <section id="demo" class="mx-auto w-full max-w-6xl scroll-mt-24 px-4 pt-14 sm:px-6">
        <div class="text-center">
            <span class="inline-block rounded-full border border-[#d8e2c8] bg-white px-4 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.2em] text-[#5a7a4e]">{{ __('landing.eyebrow_demo') }}</span>
            <h2 class="mt-4 font-display text-2xl font-extrabold tracking-tight text-[#1e3a1a] sm:text-[32px]">{{ __('landing.demo_title') }}</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-gray-500">{{ __('landing.demo_subtitle') }}</p>
        </div>

        <div class="mt-8 overflow-hidden rounded-3xl bg-white shadow-[0_24px_60px_-24px_rgba(30,58,26,0.4)] ring-1 ring-black/5"
            x-data="{
                tab: 'fresh',
                cases: {
                    fresh: { conf: 92.4, probs: { fresh: 92.4, stale: 5.1, rotten: 2.5 }, color: '#2f7a2c', label: @js(__('app.fresh')), desc: @js(__('landing.demo_fresh_desc')) },
                    stale: { conf: 78.6, probs: { fresh: 12.3, stale: 78.6, rotten: 9.1 }, color: '#d97706', label: @js(__('app.stale')), desc: @js(__('landing.demo_stale_desc')) },
                    rotten: { conf: 88.9, probs: { fresh: 3.2, stale: 7.9, rotten: 88.9 }, color: '#dc2626', label: @js(__('app.rotten')), desc: @js(__('landing.demo_rotten_desc')) },
                }
            }">
            <div class="h-1.5 bg-gradient-to-r from-[#2f5d2a] via-[#6aa048] to-[#2f5d2a]"></div>
            <div class="p-4 min-[420px]:p-6 sm:p-8">
                <div class="mx-auto grid max-w-md grid-cols-3 gap-1 rounded-full bg-gray-100 p-1 text-[11px] font-bold min-[420px]:text-xs sm:text-[13px]" role="tablist">
                    <button @click="tab = 'fresh'" :class="tab === 'fresh' ? 'bg-[#2f5d2a] text-white shadow' : 'text-gray-500'" class="min-w-0 truncate rounded-full px-1 py-2.5 transition min-[420px]:px-2 sm:px-4" role="tab">{{ __('app.fresh') }}</button>
                    <button @click="tab = 'stale'" :class="tab === 'stale' ? 'bg-[#2f5d2a] text-white shadow' : 'text-gray-500'" class="min-w-0 truncate rounded-full px-1 py-2.5 transition min-[420px]:px-2 sm:px-4" role="tab">{{ __('app.stale') }}</button>
                    <button @click="tab = 'rotten'" :class="tab === 'rotten' ? 'bg-[#2f5d2a] text-white shadow' : 'text-gray-500'" class="min-w-0 truncate rounded-full px-1 py-2.5 transition min-[420px]:px-2 sm:px-4" role="tab">{{ __('app.rotten') }}</button>
                </div>

                <div class="mt-8 grid items-center gap-8 md:grid-cols-[auto_1fr]">
                    <div class="mx-auto text-center">
                        <div class="relative h-40 w-40">
                            <svg viewBox="0 0 120 120" class="h-40 w-40 -rotate-90">
                                <circle cx="60" cy="60" r="52" fill="none" stroke-width="11" class="stroke-gray-100" />
                                <circle cx="60" cy="60" r="52" fill="none" :stroke="cases[tab].color" stroke-width="11" stroke-linecap="round"
                                    stroke-dasharray="326.7" :stroke-dashoffset="326.7 * (1 - cases[tab].conf / 100)"
                                    style="transition: stroke-dashoffset .6s ease, stroke .6s ease;" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="font-display text-[26px] font-extrabold tabular-nums text-[#1e3a1a]" x-text="cases[tab].conf.toFixed(1) + '%'"></span>
                                <span class="text-[10px] font-extrabold uppercase tracking-[0.18em]" :style="'color:' + cases[tab].color" x-text="cases[tab].label"></span>
                            </div>
                        </div>
                        <p class="mx-auto mt-3 max-w-[200px] rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-semibold leading-snug text-emerald-700 ring-1 ring-emerald-100" x-text="cases[tab].desc"></p>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-col gap-1 text-xs min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between min-[420px]:gap-2">
                            <p class="font-extrabold text-[#1e3a1a]">{{ __('landing.demo_dist') }}</p>
                            <p class="text-gray-400">RBF Kernel ({{ __('landing.demo_confidence') }} Score)</p>
                        </div>
                        <div class="mt-4 space-y-4">
                            <template x-for="(val, key) in cases[tab].probs" :key="key">
                                <div>
                                    <div class="mb-1.5 flex items-center justify-between text-xs">
                                        <span class="flex items-center gap-1.5 font-bold text-gray-600">
                                            <span class="h-2 w-2 rounded-full" :style="'background:' + cases[key].color"></span>
                                            <span x-text="'{{ __('landing.demo_prob') }} ' + cases[key].label"></span>
                                        </span>
                                        <span class="font-mono font-bold tabular-nums text-gray-700" x-text="val.toFixed(1) + '%'"></span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-2 rounded-full transition-all duration-700" :style="'width:' + val + '%; background:' + cases[key].color"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="mt-5 flex gap-2.5 rounded-2xl bg-amber-50 p-4 text-[12px] leading-relaxed text-amber-800 ring-1 ring-amber-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z" /></svg>
                            <span>{{ __('landing.demo_warn') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════ TEKNOLOGI ══════════ --}}
    <section class="mx-auto w-full max-w-6xl px-4 pt-14 sm:px-6">
        <div class="text-center">
            <span class="inline-block rounded-full border border-[#d8e2c8] bg-white px-4 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.2em] text-[#5a7a4e]">{{ __('landing.eyebrow_tech') }}</span>
            <h2 class="mt-4 font-display text-2xl font-extrabold tracking-tight text-[#1e3a1a] sm:text-[32px]">{{ __('landing.tech_title') }}</h2>
        </div>
        <div class="mt-8 grid grid-cols-1 items-stretch gap-4 min-[480px]:grid-cols-2 lg:grid-cols-4 lg:gap-2">
            @foreach ([
                ['t' => 'ESP32', 'd' => 'MQ-2 • MQ-3 • MQ-135', 'n' => '01', 'icon' => '<rect x="6" y="6" width="12" height="12" rx="2" /><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3" />'],
                ['t' => 'Laravel', 'd' => 'REST API • MySQL', 'n' => '02', 'icon' => '<rect x="3" y="4" width="18" height="6" rx="1.5" /><rect x="3" y="14" width="18" height="6" rx="1.5" /><path d="M7 7h.01M7 17h.01" />'],
                ['t' => 'FastAPI', 'd' => 'Python ML service :8001', 'n' => '03', 'icon' => '<path d="M13 10V3L4 14h7v7l9-11h-7z" />'],
                ['t' => 'SVM', 'd' => 'Support Vector Machine', 'n' => '04', 'img' => 'images/svm.png', 'icon' => ''],
            ] as $i => $t)
                <div class="flex items-stretch gap-2">
                    <div class="relative flex-1 rounded-3xl border border-black/5 bg-white p-6 text-center shadow-[0_10px_30px_-18px_rgba(30,58,26,0.4)]">
                        <span class="absolute right-4 top-4 font-mono text-[10px] font-bold text-gray-300">{{ $t['n'] }}</span>
                        <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-[#e9f1dc] text-[#2f5d2a]">
                            @if (!empty($t['img']))
                                <img src="{{ asset($t['img']) }}" alt="" class="h-6 w-6 object-contain" />
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $t['icon'] !!}</svg>
                            @endif
                        </span>
                        <p class="mt-3 font-display text-[15px] font-extrabold text-[#1e3a1a]">{{ $t['t'] }}</p>
                        <p class="mt-1 font-mono text-[11px] text-gray-400">{{ $t['d'] }}</p>
                    </div>
                    @if (!$loop->last)
                        <div class="hidden items-center text-[#b9c9a8] lg:flex" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6" stroke-dasharray="3 3" /></svg>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- ══════════ CTA ══════════ --}}
    <section class="mx-auto w-full max-w-6xl px-4 pt-12 sm:px-6">
        <div class="flex flex-col items-start justify-between gap-6 rounded-3xl bg-gradient-to-br from-[#38682e] to-[#1c3618] p-8 sm:p-10 lg:flex-row lg:items-center">
            <div class="max-w-xl">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.22em] text-white/50">{{ __('landing.cta_eyebrow') }}</p>
                <h2 class="mt-3 font-display text-2xl font-extrabold leading-snug tracking-tight text-white sm:text-[28px]">{{ __('landing.cta_bottom_title') }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-white/60">{{ __('landing.cta_bottom_desc') }}</p>
            </div>
            <a href="{{ route('register') }}" class="w-full shrink-0 rounded-full bg-white px-7 py-3.5 text-center text-sm font-bold text-[#234d1d] shadow-lg transition hover:bg-emerald-50 min-[420px]:w-auto min-[420px]:whitespace-nowrap lg:w-auto">{{ __('landing.cta_bottom_button') }}</a>
        </div>
    </section>

</div>
@endsection
