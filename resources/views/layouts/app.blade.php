@php
    $unreadAlerts = auth()->check()
        ? \App\Models\Alert::where('user_id', auth()->id())->whereNull('read_at')->count()
        : 0;
    $nav = [
        ['route' => 'dashboard', 'label' => __('app.nav_dashboard'), 'pattern' => 'dashboard'],
        ['route' => 'devices.index', 'label' => __('app.nav_devices'), 'pattern' => 'devices.*'],
        ['route' => 'sessions.index', 'label' => __('app.nav_sessions'), 'pattern' => 'sessions.*'],
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
<body class="flex min-h-screen flex-col font-sans">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-cassava-200/50 blur-3xl"></div>
        <div class="absolute top-10 -right-24 h-80 w-80 rounded-full bg-emerald-100/60 blur-3xl"></div>
    </div>

    <header class="sticky top-0 z-50 border-b border-cassava-900/10 bg-cassava-950/95 text-white shadow-lg backdrop-blur-xl">
        <div class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-lime-300 via-cassava-400 to-emerald-400"></div>
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-2 gap-y-2 px-4 py-3 sm:gap-3 sm:px-6 lg:px-8">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2.5 sm:gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-lime-300 via-cassava-400 to-cassava-600 shadow-lg ring-1 ring-white/30 sm:h-11 sm:w-11" aria-hidden="true"><span class="font-display text-sm font-extrabold tracking-tight text-white sm:text-base">EC</span></div>
                <div class="min-w-0">
                    <p class="truncate text-base font-extrabold leading-none tracking-tight sm:text-lg">E-CASSAVA</p>
                    <p class="mt-1 hidden text-[11px] font-medium uppercase tracking-[0.18em] text-cassava-300 min-[420px]:block">Electronic Nose System</p>
                </div>
            </a>

            <nav class="order-3 flex w-full items-center gap-1 overflow-x-auto rounded-2xl bg-white/10 p-1 text-sm ring-1 ring-white/15 md:order-none md:w-auto" aria-label="Main">
                @foreach ($nav as $item)
                    @php $active = request()->routeIs($item['pattern']); @endphp
                    <a href="{{ route($item['route']) }}"
                        @class([
                            'flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-xl px-3 py-2 font-semibold transition-all duration-200 sm:px-3.5',
                            'bg-white text-cassava-900 shadow-md' => $active,
                            'text-cassava-100 hover:bg-white/10 hover:text-white' => ! $active,
                        ])
                        @if ($active) aria-current="page" @endif>
                        <span class="hidden min-[420px]:inline">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                <x-locale-switcher />
                <a href="{{ route('alerts.index') }}" class="relative flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/15 transition hover:bg-white/20" aria-label="{{ __('app.nav_alerts') }}" title="{{ __('app.nav_alerts') }}">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" /><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" /></svg>
                    @if ($unreadAlerts > 0)
                        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[11px] font-bold text-white">{{ $unreadAlerts > 9 ? '9+' : $unreadAlerts }}</span>
                    @endif
                </a>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @keydown.escape="open = false" class="flex h-10 items-center gap-2 rounded-2xl bg-white/10 px-3 text-sm font-semibold ring-1 ring-white/15 transition hover:bg-white/20" aria-haspopup="menu" :aria-expanded="open">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-lime-300 text-xs font-extrabold text-cassava-950" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()?->name ?? 'E', 0, 1)) }}</span>
                        <span class="hidden max-w-24 truncate lg:inline">{{ auth()->user()?->name }}</span>
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
            </div>
        </div>
    </header>

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

    <footer class="mt-12 border-t border-cassava-900/10 bg-white/70 backdrop-blur">
        <div class="mx-auto flex max-w-7xl flex-col items-center gap-4 px-4 py-8 text-center sm:flex-row sm:justify-between sm:text-left">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-cassava-500 to-cassava-800" aria-hidden="true"><span class="font-display text-xs font-extrabold text-white">EC</span></div>
                <div>
                    <p class="text-sm font-bold">E-CASSAVA Electronic Nose</p>
                    <p class="text-xs text-gray-500">ESP32 • Laravel • Machine Learning SVM</p>
                </div>
            </div>
            <p class="text-xs text-gray-400">© {{ date('Y') }} E-CASSAVA</p>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
