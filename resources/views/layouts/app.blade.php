@php
    $unreadAlerts = auth()->check()
        ? \App\Models\Alert::where('user_id', auth()->id())->whereNull('read_at')->count()
        : 0;
    $nav = [
        ['route' => 'dashboard', 'label' => __('app.nav_dashboard'), 'pattern' => 'dashboard'],
        ['route' => 'devices.index', 'label' => __('app.nav_devices'), 'pattern' => 'devices.*'],
        ['route' => 'ml.index', 'label' => __('app.nav_ml'), 'pattern' => 'ml.*'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="E-CASSAVA — Electronic Nose monitoring kualitas singkong berbasis ESP32, Laravel & Machine Learning." />
    <title>@yield('title', 'E-CASSAVA') – Electronic Nose Monitoring</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='24' fill='%23548035'/><text x='50' y='69' font-size='50' font-family='Arial,sans-serif' font-weight='bold' text-anchor='middle' fill='white'>EC</text></svg>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <script>
        // E-CASSAVA selalu terang: bersihkan sisa preferensi gelap lama.
        try {
            localStorage.removeItem('theme');
            document.documentElement.classList.remove('dark');
            document.documentElement.style.colorScheme = 'light';
        } catch (e) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
</head>
<body class="flex min-h-screen flex-col bg-[#f6f4ec] font-sans text-gray-800 antialiased">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-cassava-200/40 blur-3xl"></div>
        <div class="absolute top-10 -right-24 h-80 w-80 rounded-full bg-emerald-100/50 blur-3xl"></div>
    </div>

    {{-- Navbar pil mengambang --}}
    <div class="sticky top-3 z-50 mx-auto w-[calc(100%-1.5rem)] max-w-7xl" x-data="{ menu: false }">
        <header class="flex items-center justify-between gap-2 rounded-full bg-white/95 py-2 pl-3 pr-2 shadow-[0_10px_36px_-12px_rgba(30,58,26,0.35)] ring-1 ring-black/5 backdrop-blur sm:pl-4">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2.5">
                <img src="{{ asset('images/logo.png') }}" alt="E-CASSAVA"
                    class="h-9 w-9 shrink-0 rounded-full object-cover ring-1 ring-black/5"
                    onerror="this.style.display='none'" />
                <span class="min-w-0 leading-none">
                    <span class="block truncate font-display text-[15px] font-extrabold tracking-tight text-[#234d1d]">E-CASSAVA</span>
                    <span class="mt-1 hidden text-[8.5px] font-semibold uppercase tracking-[0.22em] text-[#9db18d] min-[420px]:block">Electronic Nose System</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 text-[13px] font-bold lg:flex" aria-label="Main">
                @foreach ($nav as $item)
                    @php $active = request()->routeIs($item['pattern']); @endphp
                    <a href="{{ route($item['route']) }}"
                        @class([
                            'rounded-full px-4 py-2 transition',
                            'bg-[#2f5d2a] text-white shadow' => $active,
                            'text-gray-600 hover:bg-gray-100 hover:text-[#234d1d]' => ! $active,
                        ])
                        @if ($active) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                <div class="hidden min-[420px]:block">
                    <x-locale-switcher :onDark="false" />
                </div>
                <a href="{{ route('alerts.index') }}" class="relative flex h-9 w-9 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-[#234d1d]" aria-label="{{ __('app.nav_alerts') }}" title="{{ __('app.nav_alerts') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4-5.7V5a2 2 0 1 0-4 0v.3A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5" /><path d="M10 21a2 2 0 0 0 4 0" /></svg>
                    @if ($unreadAlerts > 0)
                        <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold text-white ring-2 ring-white">{{ $unreadAlerts > 9 ? '9+' : $unreadAlerts }}</span>
                    @endif
                </a>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @keydown.escape="open = false" class="flex h-9 items-center gap-2 rounded-full bg-gray-100 py-1 pl-1 pr-2.5 text-[13px] font-bold text-gray-700 transition hover:bg-gray-200 sm:pr-3" aria-haspopup="menu" :aria-expanded="open">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#2f5d2a] text-[11px] font-extrabold text-white" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()?->name ?? 'E', 0, 1)) }}</span>
                        <span class="hidden max-w-24 truncate sm:inline">{{ auth()->user()?->name }}</span>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 top-12 w-48 overflow-hidden rounded-2xl bg-white text-gray-800 shadow-xl ring-1 ring-black/5" role="menu">
                        <div class="border-b border-gray-100 px-4 py-3">
                            <p class="truncate text-sm font-bold">{{ auth()->user()?->name }}</p>
                            <p class="truncate text-xs text-gray-500">{{ auth()->user()?->email }}</p>
                            <p class="mt-1 inline-block rounded-md bg-gray-100 px-1.5 py-0.5 font-mono text-[11px] font-semibold text-gray-600">ID: {{ auth()->id() }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 px-4 py-3 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50" role="menuitem">{{ __('app.logout') }}</button>
                        </form>
                    </div>
                </div>
                <button type="button" @click="menu = !menu" :aria-expanded="menu" aria-label="Menu"
                    class="flex h-9 w-9 items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100 lg:hidden">
                    <svg x-show="!menu" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                    <svg x-show="menu" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
            </div>
        </header>
        <div x-show="menu" x-cloak x-transition class="mt-2 rounded-3xl bg-white p-3 shadow-xl ring-1 ring-black/5 lg:hidden">
            <nav class="grid gap-1 text-sm font-bold" aria-label="Main mobile">
                @foreach ($nav as $item)
                    @php $active = request()->routeIs($item['pattern']); @endphp
                    <a href="{{ route($item['route']) }}" @click="menu = false"
                        @class([
                            'rounded-2xl px-4 py-3 transition',
                            'bg-[#2f5d2a] text-white' => $active,
                            'text-gray-600 hover:bg-gray-100' => ! $active,
                        ])>{{ $item['label'] }}</a>
                @endforeach
                <div class="min-[420px]:hidden">
                    <x-locale-switcher :onDark="false" />
                </div>
            </nav>
        </div>
    </div>

    <div class="mx-auto w-full max-w-7xl px-4 pt-5 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-sm font-medium text-emerald-900 shadow-sm" role="alert">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-2xl border border-red-200 bg-red-50/90 px-4 py-3 text-sm font-medium text-red-900 shadow-sm" role="alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-2xl border border-red-200 bg-red-50/90 px-4 py-3 text-sm text-red-900" role="alert">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <main class="mx-auto w-full max-w-7xl flex-grow animate-fade-up px-4 py-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <footer class="mt-12 bg-white">
        <div class="mx-auto grid w-full max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-3 lg:px-8">
            <div>
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo.png') }}" alt="E-CASSAVA" class="h-9 w-9 rounded-full object-cover ring-1 ring-black/5" onerror="this.style.display='none'" />
                    <p class="font-display text-[15px] font-extrabold tracking-tight text-[#234d1d]">E-CASSAVA</p>
                </div>
                <p class="mt-3 max-w-xs text-[13px] leading-relaxed text-gray-500">{{ __('landing.footer_tagline') }}</p>
                <p class="mt-4 text-[10px] font-bold uppercase tracking-[0.16em] text-gray-400">{{ __('landing.footer_integration') }}</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach (['ESP32', 'Laravel', 'SVM', 'FastAPI'] as $t)
                        <span class="rounded-md bg-gray-100 px-2 py-1 font-mono text-[10px] font-bold text-gray-500">{{ $t }}</span>
                    @endforeach
                </div>
            </div>
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#234d1d]">{{ __('landing.footer_nav') }}</p>
                <ul class="mt-3 space-y-2.5 text-[13px] font-medium text-gray-500">
                    <li><a href="{{ route('dashboard') }}" class="transition hover:text-[#234d1d]">{{ __('landing.footer_l1') }}</a></li>
                    <li><a href="{{ route('devices.index') }}" class="transition hover:text-[#234d1d]">{{ __('landing.footer_l2') }}</a></li>
                    <li><a href="{{ route('ml.index') }}" class="transition hover:text-[#234d1d]">{{ __('landing.footer_l3') }}</a></li>
                    <li><a href="{{ route('alerts.index') }}" class="transition hover:text-[#234d1d]">{{ __('landing.footer_l4') }}</a></li>
                </ul>
            </div>
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#234d1d]">{{ __('landing.footer_consortium') }}</p>
                <p class="mt-3 text-[13px] leading-relaxed text-gray-500">{{ __('landing.footer_consortium_desc') }}</p>
                <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-100">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ __('landing.footer_sensor_badge') }}
                </p>
            </div>
        </div>
        <div class="border-t border-gray-100">
            <div class="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-2 px-4 py-5 text-[12px] text-gray-400 sm:flex-row sm:px-6 lg:px-8">
                <p>© {{ date('Y') }} E-Cassava System. {{ __('landing.footer_rights') }}</p>
                <div class="flex gap-5">
                    <a href="#" class="transition hover:text-[#234d1d]">{{ __('landing.footer_privacy') }}</a>
                    <a href="#" class="transition hover:text-[#234d1d]">{{ __('landing.footer_terms') }}</a>
                </div>
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
