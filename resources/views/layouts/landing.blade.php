<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', __('landing.meta_title'))</title>
    <meta name="description" content="@yield('meta_description', __('landing.meta_description'))" />
    <meta property="og:title" content="@yield('title', __('landing.meta_title'))" />
    <meta property="og:description" content="@yield('meta_description', __('landing.meta_description'))" />
    <meta property="og:type" content="website" />
    <meta name="theme-color" content="#16A34A" />
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
</head>
<body class="flex min-h-screen flex-col font-sans">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-cassava-200/50 blur-3xl"></div>
        <div class="absolute top-10 -right-24 h-80 w-80 rounded-full bg-emerald-100/60 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-lime-100/50 blur-3xl"></div>
    </div>

    <header x-data="{ menu: false }" class="sticky top-0 z-50 border-b border-cassava-900/10 bg-cassava-950/95 text-white shadow-lg backdrop-blur-xl">
        <div class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-lime-300 via-cassava-400 to-emerald-400"></div>
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-3 gap-y-2 px-4 py-3 sm:px-6 lg:px-8">
            <a href="/" class="flex min-w-0 items-center gap-2.5 sm:gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-lime-300 via-cassava-400 to-cassava-600 shadow-lg ring-1 ring-white/30 sm:h-11 sm:w-11" aria-hidden="true"><span class="font-display text-sm font-extrabold tracking-tight text-white sm:text-base">EC</span></div>
                <div class="min-w-0">
                    <p class="truncate text-base font-extrabold leading-none tracking-tight sm:text-lg">E-CASSAVA</p>
                    <p class="mt-1 hidden text-[11px] font-medium uppercase tracking-[0.18em] text-cassava-300 min-[420px]:block">Electronic Nose System</p>
                </div>
            </a>
            <nav class="hidden items-center gap-6 text-sm font-semibold text-cassava-100 md:flex" aria-label="Landing">
                <a href="#fitur" class="transition hover:text-white">{{ __('landing.nav_features') }}</a>
                <a href="#cara-kerja" class="transition hover:text-white">{{ __('landing.nav_how') }}</a>
                <a href="#demo" class="transition hover:text-white">{{ __('landing.nav_demo') }}</a>
            </nav>
            <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                <x-locale-switcher />
                <a href="{{ route('login') }}" class="hidden rounded-2xl px-4 py-2.5 text-sm font-semibold text-cassava-100 transition hover:bg-white/10 hover:text-white sm:inline-block">{{ __('landing.login') }}</a>
                <a href="{{ route('register') }}" class="hidden rounded-2xl bg-gradient-to-b from-lime-400 to-cassava-500 px-4 py-2.5 text-sm font-bold text-cassava-950 shadow transition hover:brightness-105 min-[420px]:inline-block">{{ __('landing.register') }}</a>
                <button type="button" @click="menu = !menu" :aria-expanded="menu" aria-label="Menu"
                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/15 transition hover:bg-white/20 md:hidden">
                    <svg x-show="!menu" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                    <svg x-show="menu" x-cloak class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
            </div>
        </div>
        <div x-show="menu" x-cloak x-transition class="border-t border-white/10 px-4 pb-4 pt-2 md:hidden">
            <nav class="grid gap-1 text-base font-semibold" aria-label="Landing mobile">
                <a href="#fitur" @click="menu = false" class="rounded-xl px-3 py-3 transition hover:bg-white/10">{{ __('landing.nav_features') }}</a>
                <a href="#cara-kerja" @click="menu = false" class="rounded-xl px-3 py-3 transition hover:bg-white/10">{{ __('landing.nav_how') }}</a>
                <a href="#demo" @click="menu = false" class="rounded-xl px-3 py-3 transition hover:bg-white/10">{{ __('landing.nav_demo') }}</a>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}" class="rounded-2xl bg-white/10 px-4 py-3 text-center ring-1 ring-white/15">{{ __('landing.login') }}</a>
                    <a href="{{ route('register') }}" class="rounded-2xl bg-gradient-to-b from-lime-400 to-cassava-500 px-4 py-3 text-center font-bold text-cassava-950">{{ __('landing.register') }}</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="w-full flex-grow">
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-cassava-900/10 bg-white/70 backdrop-blur">
        <div class="mx-auto flex max-w-7xl flex-col items-center gap-4 px-4 py-8 text-center sm:flex-row sm:justify-between sm:text-left">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-cassava-500 to-cassava-800" aria-hidden="true"><span class="font-display text-xs font-extrabold text-white">EC</span></div>
                <div>
                    <p class="text-sm font-bold">E-CASSAVA Electronic Nose</p>
                    <p class="text-xs text-gray-500">ESP32 • Laravel • SVM • FastAPI</p>
                </div>
            </div>
            <p class="max-w-md text-xs leading-relaxed text-gray-500">{{ __('landing.footer_desc') }}</p>
            <p class="text-xs text-gray-400">© {{ date('Y') }} E-CASSAVA. {{ __('landing.footer_rights') }}</p>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
